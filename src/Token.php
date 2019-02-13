<?php

namespace Billitech\Sly;

class Token {
    
    const T_TAG_OPEN = 1;
    const T_NOT = 2;
    const T_AND = 3;
    const T_OR = 4;
    const T_QUESTION = 5;
    const T_SCOLON = 6;
    const T_EQ = 7;
    const T_NE = 8;
    const T_GT = 9;
    const T_GE = 10;
    const T_LT = 11;
    const T_LE = 12;
    const T_IN = 13;
    const T_PLUS = 14;
    const T_PLUS1 = 15;
    const T_MINUS = 16;
    const T_MINUS1 = 17;
    const T_TIMES = 18;
    const T_DIV = 19;
    const T_MOD = 20;
    const T_BITWISE = 21;
    const T_FILTER_PIPE = 22;
    const T_TEXT = 23;
    const T_COMMENT = 24;
    const T_PRINT_OPEN = 25;
    const T_PRINT_CLOSE = 26;
    const T_EXTENDS = 27;
    const T_TAG_CLOSE = 28;
    const T_INCLUDE = 29;
    const T_AUTOESCAPE = 30;
    const T_EAUTOESCAPE = 31;
    const T_CUSTOM_TAG = 32;
    const T_SET = 33;
    const T_ASSIGN = 34;
    const T_LOAD = 35;
    const T_FOR = 36;
    const T_EFOR = 37;
    const T_WHILE = 38;
    const T_EWHILE = 39;
    const T_COMMA = 40;
    const T_IF = 41;
    const T_EIF = 42;
    const T_ELSE = 43;
    const T_ELSEIF = 44;
    const T_BLOCK = 45;
    const T_EBLOCK = 46;
    const T_FILTER = 47;
    const T_TRUE = 48;
    const T_FALSE = 49;
    const T_STRING = 50;
    const T_OPARENT = 51;
    const T_CPARENT = 52;
    const T_OBJ = 53;
    const T_DOT = 54;
    const T_CLASS = 55;
    const T_OBRACKETS = 56;
    const T_CBRACKETS = 57;
    const T_ALPHA = 58;
    const T_DOTDOT = 59;
    const T_NUMERIC = 60;
    const T_PRINT = 61;
    const T_TAG = 62;
    const T_VARIABLE = 63;
    const T_FUNCTION = 64;
    const T_START = 65;
    const T_END = 66;
    const T_ESET = 67;
    const T_CONCATE = 68;
    const T_COLON = 69;
    const T_ARRAY = 70;
    const T_ARRAY_OPERATOR = 71;
    const T_CLASS_OPERATOR = 72;
    const T_SWITCH = 73;
    const T_ESWITCH = 74;
    const T_CASE = 75;
    const T_DEFAULT = 76;
    const T_BREAK = 77;
    const T_CONTINUE = 78;
    const T_ELSEFOR = 79;
    const T_RAW = 80;
    const T_NSSEPERATOR = 81;
    const T_MACRO = 82;
    const T_EMACRO = 83;
    const T_CONSTANT = 84;
    const T_IMPORT = 85;
    const T_TEND = 86;
    const T_DO = 87;
    const T_E = 88;
    const T_TERNARY_OPERATOR = 89;
    const T_ADD_ASSIGN = 90;
    const T_SUB_ASSIGN = 91;
    const T_MUNTI_ASSIGN = 92;
    const T_DIV_ASSIGN = 93;
    const T_CONCAT_ASSIGN = 94;
    const T_XOR = 95;
    const T_TEST_OPERATOR = 96;
    const T_TFILTER = 97;
    const T_ETFILTER = 98;
    const T_SPACELESS = 99;
    const T_ESPACELESS = 100;
    const T_NAMESPACE = 101;
    const T_IDENTICAL = 102;
    const T_NE_IDENTICAL = 103;
    const T_PAW = 104;
    const T_PARENT = 105;

    /**
     * The token value.
     *
     * @var string
     */
    protected $value;

    /**
     * The token type.
     *
     * @var int
     */
    protected $type;

    /**
     * The token parent type.
     *
     * @var int
     */
    protected $parent;

    /**
     * The token parent type value.
     *
     * @var int
     */
    protected $parentVal = null;

    /**
     * The token line number.
     *
     * @var int
     */
    protected $lineno;

    /**
     * Constructor.
     *
     * @param string $value
     * @param int $type
     * @param int $parent
     * @param int $lineno
     */
    public function __construct($value, $type, $parent, $lineno) {
        $this->value = $value;
        $this->type = $type;
        $this->parent = $parent;
        $this->lineno = $lineno;
        if (is_array($parent)) {
            $this->parent = $parent[0];
            $this->parentVal = $parent[1];
        }
    }

    /**
     * Returns a string representation of the token.
     *
     * @return string
     */
    public function __toString() {
        return $this->value;
    }

    /**
     * Tests token by the given parameters.
     *
     * @param int $type
     * @param string $value
     * @param int $parent
     * @param string|null $parentVal
     * @return bool
     */
    public function is($type, $value = null, $parent = null, $parentVal = null) {
        return (($type === null || $type == $this->type) && ($value === null || $value == $this->value) && ($parent === null || $parent == $this->parent) && ($parentVal === null || $parentVal == $this->parentVal));
    }

    /**
     * Gets the line.
     *
     * @return int
     */
    public function line() {
        return $this->lineno;
    }

    /**
     * Gets the token type.
     *
     * @return int
     */
    public function type() {
        return $this->type;
    }

    /**
     * Gets the token type.
     *
     * @return int
     */
    public function name() {
        return Translator::getTypeName($this->type);
    }

    /**
     * Gets the token value.
     *
     * @return string
     */
    public function value() {
        return $this->value;
    }

    /**
     * Gets the token parent type.
     *
     * @return int
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * Gets the token parent value.
     *
     * @return string|null
     */
    public function parentVal() {
        return $this->parentVal;
    }

}