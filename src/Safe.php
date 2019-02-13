<?php
/**
 * Created by PhpStorm.
 * User: COMPUTER
 * Date: 9/8/2017
 * Time: 6:47 PM
 */

namespace Billitech\Sly;


class Safe
{
    protected $content;

    function __construct($content)
    {
        $this->content = $content;
    }

    function __toString()
    {
        return $this->content;
    }

    public static function newInstance($content) {
        return new static($content);
    }
}