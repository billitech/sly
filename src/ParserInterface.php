<?php

namespace Billitech\Sly;

interface ParserInterface {
    
    public function parse(TokenStream $stream, CompilerInterface $compiler);
    
    public function parseToken(TokenStream $stream);

    public function parseExpr(TokenStream $stream, $ignorOperators = false);
    
}