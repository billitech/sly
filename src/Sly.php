<?php

namespace Billitech\Sly;

use Exception;
use InvalidArgumentException;

class Sly Implements SlyInterface {
    const VERSION = '0.0.1';
    protected $charset;
    protected $loader;
    protected $definitions;
    protected $compiler;
    protected $parser;
    protected $tokenizer;
    protected $cache;
    protected $autoEscape;
    protected $strictVariables;
    protected $baseTemplateClass = 'Billitech\Sly\Template';
    protected $functions = [];
    protected $compileTimeFunctions = [];
    protected $globals = [];
    protected $loadedTemplates;
    protected static $instance;
    private $templatesBaseClass;

    /**
     * Class constructor.
     *
     * @param \Billitech\Sly\LoaderInterface $loader
     * @param bool|false|string $cache
     */
    public function __construct(LoaderInterface $loader, $cache = false) {
        $this->loader = $loader;
        $this->cache = $cache;
        $this->addCoreFunctions();
        $this->addCoreCompileTimeFunctions();
        static::$instance = $this;
    }

    /**
     * Add all the core functions to the functions Array.
     *
     * @return void
     */
    protected function addCoreFunctions() {
        $this->addFunction(['date' => '\Billitech\Sly\Functions::date',
            'truncate' => '\Billitech\Sly\Functions::truncate',
            'length' => '\Billitech\Sly\Functions::length',
            'in' => '\Billitech\Sly\Functions::in',
            'isIterable' => '\Billitech\Sly\Functions::isIterable',
            'join' => '\Billitech\Sly\Functions::join',
            'range' => '\Billitech\Sly\Functions::range',
            'substring' => '\Billitech\Sly\Functions::substring',
            'lower' => '\Billitech\Sly\Functions::lower',
            'upper' => '\Billitech\Sly\Functions::upper',
            'firstUpper' => '\Billitech\Sly\Functions::firstUpper',
            'title' => '\Billitech\Sly\Functions::title',
            'default' => '\Billitech\Sly\Functions::doDefault']);
    }

    /**
     * Add all the core compile time functions to the functions Array.
     *
     * @return void
     */
    protected function addCoreCompileTimeFunctions() {
        $this->addCompileTimeFunction(['cycle' => '\Billitech\Sly\Functions::compileCycle',
            'parent' => '\Billitech\Sly\Functions::compileParent',
            'block' => '\Billitech\Sly\Functions::compileBlock',
            'include' => '\Billitech\Sly\Functions::compileInclude',
            'isset' => '\Billitech\Sly\Functions::compileIsset',
            'empty' => '\Billitech\Sly\Functions::compileEmpty']);
    }

    /**
     * Get all global data.
     *
     * @return array
     */
    public function getGlobals() {
        return $this->globals;
    }

    /**
     * Add global data.
     *
     * @param string|array $name
     * @param mixed  $value
     * @return $this
     */
    public function addGlobal($name, $value = null) {
        if (!is_array($name)) {
            $name = [$name => $value];
        }

        foreach ($name as $key => $value) {
            $this->globals[$key] = $value;
        }

        return $this;
    }

    /**
     * Add function.
     *
     * @param string|array   $name
     * @param callable $callable
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addFunction($name, $callable = null) {
        if (!is_array($name)) {
            $name = [$name => $callable];
        }

        foreach ($name as $function => $callable) {
            if (!is_callable($callable)) {
                throw new InvalidArgumentException("Invalid Callable for function '{$function}'.");
            }

            $this->functions[$function] = $callable;
        }

        return $this;
    }

    /**
     * Get Function by the given name.
     *
     * @param string $name
     * @return callable
     * @throws \InvalidArgumentException
     */
    public function getFunction($name) {
        if ($this->isFunction($name)) {
            return $this->functions[$name];
        }

        throw new \InvalidArgumentException("The function for '{$name}' is undefined.");
    }

    /**
     * Call function for the given name.
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function callFunction($name, Array $args = []) {
        return call_user_func_array($this->getFunction($name), $args);
    }

    /**
     * Check if a given function exists.
     *
     * @param string $name
     * @return bool
     */
    public function isFunction($name) {
        if (isset($this->functions[$name])) {
            return true;
        }

        return false;
    }

    /**
     * Get all registered functions.
     *
     * @return array
     */
    public function getFunctions() {
        return $this->functions;
    }

    /**
     * Add compile time function.
     *
     * @param string|array   $name
     * @param callable $callable
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addCompileTimeFunction($name, $callable = null) {
        if (!is_array($name)) {
            $name = [$name => $callable];
        }

        foreach ($name as $function => $callable) {
            if (!is_callable($callable)) {
                throw new \InvalidArgumentException("Invalid Callable for compile time function '{$function}'.");
            }

            $this->compileTimeFunctions[$function] = $callable;
        }

        return $this;
    }

    /**
     * Get compile time function by the given name.
     *
     * @param string $name
     * @return callable
     * @throws \InvalidArgumentException
     */
    public function getCompileTimeFunction($name) {
        if ($this->isCompileTimeFunction($name)) {
            return $this->compileTimeFunctions[$name];
        }

        throw new \InvalidArgumentException("The compile time function for '{$name}' is undefined.");
    }

    /**
     * Check if a given compile time function exists.
     *
     * @param string $name
     * @return bool
     */
    public function isCompileTimeFunction($name) {
        if (isset($this->compileTimeFunctions[$name])) {
            return true;
        }

        return false;
    }

    /**
     * Get all registered compile time functions.
     *
     * @return array
     */
    public function getCompileTimeFunctions() {
        return $this->compileTimeFunctions;
    }

    /**
     * Add Extensions.
     *
     * @param array $extensions
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addExtensions(Array $extensions) {
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }

        return $this;
    }

    /**
     * Add Extension.
     *
     * @param \Billitech\Sly\ExtensionInterface $extension
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addExtension(ExtensionInterface $extension) {
        $this->addCustomTag($extension);
        if (count(($globals = $extension->getGlobals())) > 0) {
            $this->addGlobal($globals);
        }

        if (count(($functions = $extension->getFunctions())) > 0) {
            $this->addFunction($functions);
        }

        if (count(($cfunctions = $extension->getCompileTimeFunctions())) > 0) {
            $this->addCompileTimeFunction($cfunctions);
        }

        return $this;
    }

    /**
     * Add custom tag.
     *
     * @param \Billitech\Sly\ExtensionInterface $extension
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function addCustomTag(ExtensionInterface $extension) {
        $tags = array_merge((array)$extension->getTags(), (array)$extension->getParsers());

        foreach ($tags as $tagName => $callable) {
            if(is_int($tagName)) {
                $tagName = $callable;
                $callable = null;
            }
            if ($callable != null && !is_callable($callable)) {
                throw new \InvalidArgumentException("Tag parser must be a valid callable.");
            }
            $this->getDefinitions()->addTag($tagName, $callable);
        }

        return $this;
    }

    /**
     * Get the base template class for compiled templates.
     *
     * @return string
     */
    public function getBaseTemplateClass() {
        return $this->baseTemplateClass;
    }

    /**
     * Set the base template class for compiled templates.
     *
     * @param string $class
     * @return $this
     */
    public function setBaseTemplateClass($class) {
        $this->baseTemplateClass = $class;
        return $this;
    }

    /**
     * Set the default template charset.
     *
     * @param string $charset
     * @return $this;
     */
    public function setCharset($charset) {
        $this->charset = strtoupper($charset);
        return $this;
    }

    /**
     * Get the default template charset.
     *
     * @return string
     */
    public function getCharset() {
        return $this->charset;
    }

    /**
     * Enables auto escape.
     *
     * @return $this
     */
    public function enableAutoEscape() {
        $this->autoEscape = true;
        return $this;
    }

    /**
     * Disables the auto escape.
     *
     * @return $this
     */
    public function disableAutoEscape() {
        $this->autoEscape = false;
        return $this;
    }

    /**
     * Checks if auto escape is anabled.
     *
     * @return bool
     */
    public function isAutoEscape() {
        return $this->autoEscape;
    }

    /**
     * Enables strict variables.
     *
     * @return $this
     */
    public function enableStrictVariables() {
        $this->strictVariables = true;
        return $this;
    }

    /**
     * Disables strict variables.
     *
     * @return $this
     */
    public function disableStrictVariables() {
        $this->strictVariables = false;
        return $this;
    }

    /**
     * Checks if strict variables is enabled.
     *
     * @return bool
     */
    public function isStrictVariables() {
        return $this->strictVariables;
    }

    /**
     * Get the cache directory.
     *
     * @return string|false
     */
    public function getCache() {
        return $this->cache;
    }

    /**
     * Set the cache directory or disable cache by passing false as the cache path.
     *
     * @param string|false $cache
     * @return $this         
     */
    public function setCache($cache) {
        $this->cache = $cache ? $cache : false;
        return $this;
    }

    /**
     * Get the cache filename for the given template.
     *
     * @param string $name
     *
     * @return string|false
     */
    public function getCacheFilename($name) {
        if (false === $this->cache) {
            return false;
        }

        return $this->getCache() . DIRECTORY_SEPARATOR . $this->getTemplateClass($name) . '.php';
    }

    /**
     * Get the template class associated for the given template
      .
     *
     * @param string $name
     *
     * @return string
     */
    public function getTemplateClass($name) {
        return 'Sly_' . hash('sha256', $this->getLoader()->getCacheKey($name));
    }

    /**
     * Get the templates base class
    .
     *
     * @param string $name
     *
     * @return string
     */
    public function getTemplatesBaseClass() {
        if (is_null($this->templatesBaseClass)) {
            return Template::class;
        }

        return $this->templatesBaseClass;
    }

    /**
     * Set the templates base class
    .
     *
     * @param string $name
     *
     * @return string
     */
    public function setTemplatesBaseClass($baseClass) {
        $this->templatesBaseClass = $baseClass;
    }

    /**
     * Render the given template.
     *
     * @param string $name
     * @param array  $data
     * @return string
     * @throws \Billitech\Sly\Exceptions\RuntimeError
     * @throws \Billitech\Sly\Exceptions\SyntaxError
     */
    public function render($name, Array $data = []) {
        return $this->loadTemplate($name)->render($this->mergeGlobals($data));
    }

    /**
     * Merge global data to the given data array.
     *
     * @param array $data
     *
     * @return array
     */
    public function mergeGlobals(Array $data) {
        foreach ($this->getGlobals() as $key => $value) {
            if (!array_key_exists($key, $data)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Load the given template.
     *
     * @param string $name
     *
     * @return \Billitech\Sly\TemplateInterface
     */
    public function loadTemplate($name) {
        $class = $this->getTemplateClass($name);


        if (class_exists($class, false)) {
            return new $class($this);
        }

        if ((($cacheFile = $this->getCacheFilename($name)) !== false) && ($this->loadCache($cacheFile, $name) !== false)) {
            if (class_exists($class, false)) {
                return new $class($this);
            }
        }

        $code = $this->parse($this->tokenize($this->getLoader()->load($name), $name));

        if ($cacheFile !== false) {
            $this->saveCache($cacheFile, $code);
        }

        eval('?>' . $code);

        return new $class($this);
    }

    /**
     * Load the given given cache file.
     *
     * @param string $cacheFile
     * @param string $name
     * @return bool
     */
    public function loadCache($cacheFile, $name) {
        if (!is_file($cacheFile)) {
            return false;
        }

        if (!$this->getLoader()->isFresh($name, filemtime($cacheFile))) {
            unlink($cacheFile);
            return false;
        }

        require_once $cacheFile;
        return true;
    }

    /**
     * Cache Compiled template.
     *
     * @param string $cacheFile
     * @param string $content
     * @return void
     */
    public function saveCache($cacheFile, $content) {
        $dir = dirname($cacheFile);

        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException("Unable to create the cache directory '{$dir}'.");
            }
        } elseif (!is_writable($dir)) {
            throw new \RuntimeException("Unable to write in the cache directory '{$dir}'.");
        }

        if (!file_put_contents($cacheFile, $content)) {
            throw new \RuntimeException("Failed to write cache file '{$cacheFile}'.");
        }
    }

    /**
     * Clear the template cache files on the filesystem.
     * 
     * return void
     */
    public function clearCacheFiles() {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->cache), RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
            if ($file->isFile()) {
                @unlink($file->getPathname());
            }
        }
    }

    /**
     * Tokenize the given given source code.
     *
     * @param string $source
     * @param string $name
     * @return \Billitech\Sly\TokenStream
     * @throws \Billitech\Sly\Exceptions\SyntaxError
     */
    public function tokenize($source, $name = null) {
        return $this->getTokenizer()->tokenize($source, $name);
    }

    /**
     * Parse the given tokens.
     *
     * @param \Billitech\Sly\TokenStream
     * @return string
     */
    public function parse(TokenStream $stream) {
        return $this->getParser()->parse($stream, $this->getCompiler());
    }
    
    /**
     * Get the definitions instance.
     *
     * @return \Billitech\Sly\Definitions
     */ 
    public function getDefinitions() {
        if(!isset($this->definitions)) {
            $this->definitions = new Definitions();
        }
        
        return $this->definitions;
    }
    
    /**
     * Set the compiler instance.
     *
     * @param \Billitech\Sly\Compiler $compiler
     * @return $this
     */
    public function setCompiler(Compiler $compiler) {
        $this->compiler = $compiler;
        return $this;
    }
    
    /**
     * Get the definitions instance.
     *
     * @return \Billitech\Sly\Compiler
     */ 
    public function getCompiler() {
        if(!isset($this->compiler)) {
            $this->compiler = new Compiler($this);
        }
        
        return $this->compiler;
    }
    
    /**
     * Set the parser instance.
     *
     * @param \Billitech\Sly\Parser $parser
     * @return $this
     */
    public function setParser(Parser $parser) {
        $this->parser = $parser;
        return $this;
    }
    
    /**
     * Get the parser instance.
     *
     * @return \Billitech\Sly\Parser
     */ 
    public function getParser() {
        if(!isset($this->parser)) {
            $this->parser = new Parser($this->getDefinitions());
        }
        
        return $this->parser;
    }
    
    /**
     * Set the tokinizer instance.
     *
     * @param \Billitech\Sly\Tokenizer $tokenizer
     * @return $this
     */
    public function setTokenizer(Tokenizer $tokenizer) {
        $this->tokenizer = $tokenizer;
        return $this;
    }
    
    /**
     * Set the sly instance to use DynamicTokenizer for tokenizing.
     *
     * @param array $options
     * @return $this
     */
    public function useDynamicTokenizer(Array $options = []) {
        $this->tokenizer = new DynamicTokenizer($this->getDefinitions(), $options);
        return $this;
    }
    
    /**
     * Get the tokenizer instance.
     *
     * @return \Billitech\Sly\Tokenizer
     */ 
    public function getTokenizer() {
        if(!isset($this->tokenizer)) {
            $this->tokenizer = new Tokenizer($this->getDefinitions());
        }
        
        return $this->tokenizer;
    }

    /**
     * Set the Loader instance.
     *
     * @param \Billitech\Sly\LoaderInterface $loader
     * @return $this
     */
    public function setLoader(LoaderInterface $loader) {
        $this->loader = $loader;
        return $this;
    }

    /**
     * Get the Loader instance.
     *
     * @return \Billitech\Sly\LoaderInterface $loader
     */
    public function getLoader() {
        return $this->loader;
    }

    /**
     * Get the sly instance.
     * @return static
     * @throws Exception
     */
    public static function getInstance() {
        if (!isset(static::$instance)) {
            throw new Exception("Class Has Not Been Instanciated. Cannot Get Instance Of The Class");
        }

        return static::$instance;
    }

}