<?php

namespace Billitech\Sly\Extensions;

use Billitech\Sly\Definitions;
use Billitech\Sly\ParserInterface as Parser;
use Billitech\Sly\CompilerInterface as Compiler;
use Billitech\Sly\TokenStream as Stream;
use Billitech\Sly\Extension;

class SilverTag Extends Extension {

    public function SetTags() {
        return $this->tags = ['silver', 'endsilver'];
    }

    public function SetParsers() {
        return $this->parsers = ['silver' => [$this, 'parse']];
    }

    public function parse(Stream $stream, Compiler $compiler, Parser $parser) {
        if ($stream->current()->value() == 'endif') {
            $parser->unexpectedToken($stream);
        }

        $compiler->incrIndent();
        $stream->nextIf(Token::T_TAG_CLOSE);
        $content = '';
        while ((($parent = $stream->next()->parent()) != Token::T_CUSTOM_TAG && $stream->current()->value() != 'endsilver') && $parent != Token::T_END) {
            $content .= $parser->parseToken($stream);
        }
        $stream->incr(2);
        return $this->compile($content, $compiler);
    }

    public function compile($content, Compiler $compiler) {
        return $compiler->compileIf($compiler->compileVariable(["'silver'"], '5') . ' != ' . $compiler->compileConstant('null'), $content);
    }

}
