<?php

namespace Billitech\Sly;

class Functions {

    /**
     * compile include function
     *
     * @param array $parameters
     * @param \Billitech\Sly\TokenStream $stream
     * @return string
     */
    public static function compileInclude($parameters, TokenStream $stream) {
        if (count($parameters) == 0) {
            $stream->error("The include function must contain 1 or more parameters");
        }

        $template = $parameters[0];
        $data = '[]';
        $withCurrentData = true;
        if (isset($parameters[1])) {
            $data = $parameters[1];
        }
        if (isset($parameters[2])) {
            $withCurrentData = false;
        }

        $data = ($withCurrentData === true) ? 'array_merge($data, (array)' . $data . ')' : '(array)' . $data;
        return Safe::class . '::newInstance($this->loadTemplate(' . $template . ', ' . $stream->current()->line() . ')->render(' . $data . '))';
    }

    /**
     * compile block function
     *
     * @param array $parameters
     * @param \Billitech\Sly\TokenStream $stream
     * @return string
     */
    public static function compileBlock($parameters, TokenStream $stream) {
        if (count($parameters) == 0) {
            $stream->error("The block function must contain 1 parameter");
        }

        return Safe::class . '::newInstance($this->renderBlock(' . $parameters[0] . ', $data, $blocks))';
    }

    /**
     * Parse and Compile cycle function
     *
     * @param array $parameters
     * @param \Billitech\Sly\TokenStream $stream
     * @return string
     */
    public static function compileCycle($parameters, TokenStream $stream) {
        if (count($parameters) == 0) {
            $stream->error("Cycle first parameter must be of type array and must be 2 or more");
        }

        static $id = 1;
        array_unshift($parameters, $id++);
        $parameters[2] = '$this';
        $parameters[3] = $stream->current()->line();
        return Functions::class . '::cycle(' . implode(', ', $parameters) . ')';
    }
    
    /**
     * Parse and Compile isset function
     *
     * @param array $parameters
     * @param \Billitech\Sly\TokenStream $stream
     * @return string
     */
    public static function compileIsset($parameters, TokenStream $stream) {
        if(count($parameters) < 1) {
            $stream->error("The 'isset' function must be called with at least 1 parameter");
        }
        
        foreach($parameters as $pos => $parameter) {
            if(!$parameter instanceOf Variable) {
                $stream->error("Invalid argument passed to the 'isset' function, isset function only accept valid variable name as arguments");
            }
            $parameters[$pos] = $parameter->formatAttribute();
        }
        
        return 'isset('. implode(', ', $parameters) .')';
    }
    
    /**
     * Parse and Compile empty function
     *
     * @param array $parameters
     * @param \Billitech\Sly\TokenStream $stream
     * @return string
     */
    public static function compileEmpty($parameters, TokenStream $stream) {
        if(count($parameters) < 1) {
            $stream->error("The 'empty' function must be called with a parameter");
        }
        
        if(!$parameters[0] instanceOf Variable) {
            $stream->error("Invalid argument passed to the 'empty' function, empty function only accept valid variable name as argument");
        }
        
        return 'empty(' . $parameters[0]->formatAttribute() . ')';
    }
    
    /**
     * Parse and Compile parent function
     *
     * @param array $parameters
     * @param \Billitech\Sly\TokenStream $stream
     * @return string
     */
    public static function compileParent($parameters, TokenStream $stream) {
        if (count($parameters) > 0) {
            $block = $parameters[0];
        } else {
            $block = 'isset($blockName) ? $blockName : null';
        }

        return Safe::class . '::newInstance($this->renderParentBlock(' . $block . ', $data, ' . $stream->current()->line() . '))';
    }
    
    /**
     * cycle the given items
     *
     * @param int $id
     * @param array $args
     * @param \Billitech\Sly\TemplateInterface $template
     * @param int $line
     * @return string
     */
    public static function cycle($id, $args, TemplateInterface $template, $line) {
        static $cache = [];

        if (!isset($cache[$id])) {
            if (!is_array($args) || count($args) < 2) {
                $template->error("Cycle First Parameter Must Be Of Type Array And Must Be 2 Or More", $line);
            }
            $cache[$id] = 0;
        }
        if (!isset($args[$cache[$id]])) {
            $cache[$id] = 0;
        }
        return $args[$cache[$id] ++];
    }

    /**
     * Get date 
     *
     * @param string|int $time
     * @param string $format
     * @return string
     */
    public static function date($time, $format = "y-m-d") {
        if (!isset($time)) {
            $time = new \DateTime();
            $time->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
            return $time->format($format);
        } elseif ($time instanceof \DateInterval || $time instanceof \DateTimeInterface) {
            return $time->format($format);
        } elseif (is_numeric($time)) {
            $time = (new \DateTime())->setTimestamp($time);
            $time->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
            return $time->format($format);
        } else {
            $time = new \DateTime($time);
            $time->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
            return $time->format($format);
        }
    }

    /**
     * Escape the given text
     *
     * @param string $text
     * @param string $type
     * @param string $charset
     * @return string
     */
    public static function escape($text, $type = 'html', $charset = null) {
        if($text instanceof Safe) {
            return $text->__toString();
        }

        if (is_array($text)) {
            $type = 'js';
        }
        
        switch (strtolower($type)) {
            case "url":
                return urlencode((string) $text);
            case "html";
                if ($charset != null || null !== ($charset = static::sly()->getCharset())) {
                    return htmlspecialchars((string) $text, ENT_COMPAT, $charset);
                }

                return htmlspecialchars((string) $text, ENT_COMPAT, $charset);
            case "js":
                return json_encode($text, 64 | 256);
            default:
                return (string) $text;
        }
    }

    /**
     * Unescape escaped string
     *
     * @param string $text
     * @param string $type
     * @return string
     */
    public static function unescape($text, $type = 'html') {
        switch (strtolower($type)) {
            case "url":
                return urldecode($text);
            case "html";
                return htmlspecialchars_decode($text);
            default:
                return $text;
        }
    }

    /**
     * Crop string to specific length (support unicode)
     *
     * @param string $string
     * @param int $length
     * @param string $etc
     * @return string
     */
    public static function truncate($string, $length = 80, $etc = '...') {
        if (static::length($string) <= $length) {
            return $string;
        }
        $spacePos = strpos($string, ' ');

        if ($spacePos === false || $spacePos >= $length) {
            return static::substring($string, 0, $length) . $etc;
        }

        $lastSpacePos = strrpos(static::substring($string, 0, $length + 1), ' ');

        return static::substring($string, 0, $lastSpacePos) . $etc;
    }

    /**
     * Return length of a string, array, countable object
     *
     * @param mixed $item
     * @return int
     */
    public static function length($item) {
        if (is_scalar($item)) {
            if (null !== ($charset = static::sly()->getCharset())) {
                return mb_strlen($item, $charset);
            }
            return strlen($item);
        } elseif (is_array($item)) {
            return count($item);
        } elseif ($item instanceof \Countable) {
            return $item->count();
        } else {
            return 0;
        }
    }

    /**
     * Test if a given substring occured in the given string
     *
     * @param mixed $needle
     * @param mixed $haystack
     * @return bool
     */
    public static function in($needle, $haystack) {
        if (is_array($haystack)) {
            return in_array($needle, $haystack) || array_key_exists($needle, $haystack);
        } elseif (is_string($haystack)) {
            return strpos($haystack, $needle) !== false;
        }
        return false;
    }

    /**
     * Test if a given item is iterable
     *
     * @param mixed $item
     * @return bool
     */
    public static function isIterable($item) {
        return is_array($item) || ($item instanceof \Iterator);
    }

    /**
     * join and convert array to string
     *
     * @param $item
     * @param string $glue
     * @return string
     */
    public static function join($item, $glue = ",") {
        if (is_array($item)) {
            return implode($glue, $item);
        } else {
            return $item;
        }
    }

    /**
     * return range of items
     *
     * @param string|int $from
     * @param string|int $to
     * @param int $step
     * @return array
     */
    public static function range($from, $to, $step = 1) {
        $v = range($from, $to, $step);
        return $v ? $v : [];
    }

    /**
     * Returns a part of string.
     *
     * @param  string $string
     * @param  int $start
     * @param  int $stop
     * @return string
     */
    public static function substring($string, $start, $stop = null) {
        if ($stop === null) {
            $stop = self::length($string);
        }

        if (null !== ($charset = static::sly()->getCharset())) {
            return mb_substr($string, $start, $stop, $charset);
        }

        return substr($string, $start, $stop);
    }

    /**
     * Convert string to lower case.
     *
     * @param string $string
     * @return string
     */
    public static function lower($string) {
        if (null !== ($charset = static::sly()->getCharset())) {
            return mb_strtolower($string, $charset);
        }
        return strtolower($string);
    }

    /**
     * Convert string to upper case.
     *
     * @param string
     * @return string
     */
    public static function upper($string) {
        if (null !== ($charset = static::sly()->getCharset())) {
            return mb_strtoupper($string, $charset);
        }

        return strtoupper($string);
    }

    /**
     * Convert first character of string to upper case.
     *
     * @param string
     * @return string
     */
    public static function firstUpper($string) {
        return static::upper(static::substring($string, 0, 1)) . static::substring($string, 1);
    }

    /**
     * Convert string to title case.
     *
     * @param string $string
     * @return string
     */
    public static function title($string) {
        if (null !== ($charset = static::sly()->getCharset())) {
            return mb_convert_case($string, MB_CASE_TITLE, $charset);
        }

        return ucwords(strtolower($string));
    }

    /**
     * Return the given default value if the given item is null.
     *
     * @param mixed $item
     * @param mixed $default
     * @return mixed
     */
    public static function doDefault($item, $default) {
        return ($item === null) ? $default : $item;
    }

    /**
     * Get the sly instance.
     *
     * @return \Billitech\Sly\SlyInterface
     */
    public static function sly() {
        static $sly;
        if (isset($sly)) {
            return $sly;
        }
        return $sly = Sly::getInstance();
    }

}