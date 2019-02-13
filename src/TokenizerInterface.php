<?php

namespace Billitech\Sly;

interface TokenizerInterface {
    public function tokenize($source, $file);
}