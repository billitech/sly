<?php

namespace Billitech\Sly;

use Billitech\Sly\Exceptions\LoadError;

class FileLoader implements LoaderInterface {

    /**
     * The default paths for the loader.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * The array of views that have been located.
     *
     * @var array
     */
    protected $views = [];

    /**
     * The namespace to file path hints.
     *
     * @var array
     */
    protected $hints = [];

    /**
     * Create a new file loader instance.
     *
     * @param array|string $paths
     * @return void
     */
    public function __construct($paths) {
        $this->paths = (array) $paths;
    }

    /**
     * Load a given template.
     *
     * @param string $name
     * @return string
     */
    public function load($name) {
        return file_get_contents($this->find($name));
    }

    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param  string $name
     * @return string
     */
    public function getCacheKey($name) {
        return $this->find($name);
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @param  string $name
     * @param  int    $time
     * @return bool
     */
    public function isFresh($name, $time) {
        return ($time > filemtime($this->find($name)));
    }

    /**
     * Get the fully qualified location of the view.
     *
     * @param  string  $name
     * @return string
     */
    public function find($name) {
        if (isset($this->views[$name]))
            return $this->views[$name];

        if (strpos($name = trim($name), '::')) {
            return $this->views[$name] = $this->findNamedView($name);
        }

        if (($name[0] == DIRECTORY_SEPARATOR || substr($name, 0, 2) == 'C:') && file_exists($name)) {
            return $this->views[$name] = $name;
        }

        return $this->views[$name] = $this->findInPaths($name, $this->paths);
    }

    /**
     * Get the path to a template with a named path.
     *
     * @param  string  $name
     * @return string
     * throws \Billitech\Sly\Exceptions\LoadError
     */
    protected function findNamedView($name) {
        list($namespace, $view) = explode('::', $name);
        if (!isset($this->hints[$namespace])) {
            throw new LoadError("No hint path defined for '{$namespace}'.");
        }

        return $this->findInPaths($view, $this->hints[$namespace]);
    }

    /**
     * Find the given view in the list of paths.
     *
     * @param  string  $name
     * @param  array   $paths
     * @return string
     * @throws \Billitech\Sly\Exceptions\LoadError
     */
    protected function findInPaths($name, $paths) {
        foreach ((array) $paths as $path) {
            if (file_exists($viewPath = $path . DIRECTORY_SEPARATOR . $name)) {
                return $viewPath;
            }
        }

        throw new LoadError("Template '{$name}' not found.");
    }

    /**
     * Add a given path to the paths array.
     *
     * @param  string  $path
     * @return void
     */
    public function addPath($path) {
        $this->paths[] = $path;
    }

    /**
     * Add a namespace hint.
     *
     * @param  string  $namespace
     * @param  string|array  $hints
     * @return void
     */
    public function addNamespace($namespace, $hints) {
        $hints = (array) $hints;

        if (isset($this->hints[$namespace])) {
            $hints = array_merge($this->hints[$namespace], $hints);
        }

        $this->hints[$namespace] = $hints;
    }

    /**
     * Get the paths array.
     *
     * @return array
     */
    public function getPaths() {
        return $this->paths;
    }

    /**
     * Get the namespace to file path hints.
     *
     * @return array
     */
    public function getHints() {
        return $this->hints;
    }

}