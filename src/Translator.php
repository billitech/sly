<?php

namespace Billitech\Sly;

class Translator {

    /**
     * The token types revers names
     *
     * @var array
     */
    protected static $tokenTypesNames = [
        Token::T_TAG_OPEN => 'T_TAG_OPEN',
        Token::T_NOT => 'T_NOT',
        Token::T_AND => 'T_AND',
        Token::T_OR => 'T_OR',
        Token::T_QUESTION => 'T_QUESTION',
        Token::T_SCOLON => 'T_SCOLON',
        Token::T_EQ => 'T_EQ',
        Token::T_IDENTICAL => 'T_IDENTICAL',
        Token::T_NE => 'T_NE',
        Token::T_GT => 'T_GT',
        Token::T_GE => 'T_GE',
        Token::T_LT => 'T_LT',
        Token::T_LE => 'T_LE',
        Token::T_IN => 'T_IN',
        Token::T_PLUS => 'T_PLUS',
        Token::T_PLUS1 => 'T_PLUS1',
        Token::T_MINUS => 'T_MINUS',
        Token::T_MINUS1 => 'T_MINUS1',
        Token::T_TIMES => 'T_TIMES',
        Token::T_DIV => 'T_DIV',
        Token::T_MOD => 'T_MOD',
        Token::T_BITWISE => 'T_BITWISE',
        Token::T_FILTER_PIPE => 'T_FILTER_PIPE',
        Token::T_TEXT => 'T_TEXT',
        Token::T_COMMENT => 'T_COMMENT',
        Token::T_PRINT_OPEN => 'T_PRINT_OPEN',
        Token::T_PRINT_CLOSE => 'T_PRINT_CLOSE',
        Token::T_EXTENDS => 'T_EXTENDS',
        Token::T_TAG_CLOSE => 'T_TAG_CLOSE',
        Token::T_INCLUDE => 'T_INCLUDE',
        Token::T_AUTOESCAPE => 'T_AUTOESCAPE',
        Token::T_EAUTOESCAPE => 'T_EAUTOESCAPE',
        Token::T_CUSTOM_TAG => 'T_CUSTOM_TAG',
        Token::T_SET => 'T_SET',
        Token::T_ASSIGN => 'T_ASSIGN',
        Token::T_LOAD => 'T_LOAD',
        Token::T_FOR => 'T_FOR',
        Token::T_EFOR => 'T_EFOR',
        Token::T_WHILE => 'T_WHILE',
        Token::T_EWHILE => 'T_EWHILE',
        Token::T_COMMA => 'T_COMMA',
        Token::T_IF => 'T_IF',
        Token::T_EIF => 'T_EIF',
        Token::T_ELSE => 'T_ELSE',
        Token::T_ELSEIF => 'T_ELSEIF',
        Token::T_BLOCK => 'T_BLOCK',
        Token::T_EBLOCK => 'T_EBLOCK',
        Token::T_PARENT => 'T_PARENT',
        Token::T_FILTER => 'T_FILTER',
        Token::T_TRUE => 'T_TRUE',
        Token::T_FALSE => 'T_FALSE',
        Token::T_STRING => 'T_STRING',
        Token::T_OPARENT => 'T_OPARENT',
        Token::T_CPARENT => 'T_CPARENT',
        Token::T_OBJ => 'T_OBJ',
        Token::T_DOT => 'T_DOT',
        Token::T_NAMESPACE => 'T_NAMESPACE',
        Token::T_CLASS => 'T_CLASS',
        Token::T_OBRACKETS => 'T_OBRACKETS',
        Token::T_CBRACKETS => 'T_CBRACKETS',
        Token::T_ALPHA => 'T_ALPHA',
        Token::T_DOTDOT => 'T_DOTDOT',
        Token::T_NUMERIC => 'T_NUMERIC',
        Token::T_PRINT => 'T_PRINT',
        Token::T_TAG => 'T_TAG',
        Token::T_VARIABLE => 'T_VARIABLE',
        Token::T_FUNCTION => 'T_FUNCTION',
        Token::T_START => 'T_START',
        Token::T_END => 'T_END',
        Token::T_ESET => 'T_ESET',
        Token::T_CONCATE => 'T_CONCATE',
        Token::T_COLON => 'T_COLON',
        Token::T_ARRAY => 'T_ARRAY',
        Token::T_ARRAY_OPERATOR => 'T_ARRAY_OPERATOR',
        Token::T_CLASS_OPERATOR => 'T_CLASS_OPERATOR',
        Token::T_SWITCH => 'T_SWITCH',
        Token::T_ESWITCH => 'T_ESWITCH',
        Token::T_CASE => 'T_CASE',
        Token::T_DEFAULT => 'T_DEFAULT',
        Token::T_BREAK => 'T_BREAK',
        Token::T_CONTINUE => 'T_CONTINUE',
        Token::T_ELSEFOR => 'T_ELSEFOR',
        Token::T_RAW => 'T_RAW',
        Token::T_NSSEPERATOR => 'T_NSSEPERATOR',
        Token::T_MACRO => 'T_MACRO',
        Token::T_EMACRO => 'T_EMACRO',
        Token::T_CONSTANT => 'T_CONSTANT',
        Token::T_IMPORT => 'T_IMPORT',
        Token::T_TEND => 'T_TEND',
        Token::T_DO => 'T_DO',
        Token::T_E => 'T_E',
        Token::T_TERNARY_OPERATOR => 'T_TERNARY_OPERATOR',
        Token::T_ADD_ASSIGN => 'T_ADD_ASSIGN',
        Token::T_SUB_ASSIGN => 'T_SUB_ASSIGN',
        Token::T_MUNTI_ASSIGN => 'T_MUNTI_ASSIGN',
        Token::T_DIV_ASSIGN => 'T_DIV_ASSIGN',
        Token::T_CONCAT_ASSIGN => 'T_CONCAT_ASSIGN',
        Token::T_XOR => 'T_XOR',
        Token::T_TEST_OPERATOR => 'T_TEST_OPERATOR',
        Token::T_TFILTER => 'T_TFILTER',
        Token::T_ETFILTER => 'T_ETFILTER',
        Token::T_SPACELESS => 'T_SPACELESS',
        Token::T_ESPACELESS => 'T_ESPACELESS',
        Token::T_NE_IDENTICAL => 'T_NE_IDENTICAL',
        Token::T_PAW => 'T_PAW'
    ];

    /**
     * Array of posible token type value
     *
     * @var array
     */
    protected static $posibleTypesValues = [
        Token::T_PRINT_OPEN => '@(',
        Token::T_PRINT_CLOSE => ')',
        Token::T_TAG_OPEN => '@',
        Token::T_TAG_CLOSE => ')',
        Token::T_AUTOESCAPE => 'autoescape',
        Token::T_EAUTOESCAPE => 'endautoescape',
        Token::T_BLOCK => 'block',
        Token::T_EBLOCK => 'endblock',
        Token::T_PARENT => 'parent',
        Token::T_ELSE => 'else',
        Token::T_ELSEIF => 'elseif',
        Token::T_EXTENDS => 'extends',
        Token::T_FOR => 'for',
        Token::T_ELSEFOR => 'elsefor',
        Token::T_EFOR => 'endfor',
        Token::T_WHILE => 'while',
        Token::T_EWHILE => 'endwhile',
        Token::T_IF => 'if',
        Token::T_EIF => 'endif',
        Token::T_INCLUDE => 'include',
        Token::T_LOAD => 'load',
        Token::T_SET => 'set',
        Token::T_ESET => 'endset',
        Token::T_SWITCH => 'switch',
        Token::T_ESWITCH => 'endswitch',
        Token::T_CASE => 'case',
        Token::T_BREAK => 'break',
        Token::T_CONTINUE => 'continue',
        Token::T_DEFAULT => 'default',
        Token::T_RAW => 'raw',
        Token::T_MACRO => 'macro',
        Token::T_EMACRO => 'endmacro',
        Token::T_IMPORT => 'import',
        Token::T_END => 'end',
        Token::T_DO => 'do',
        Token::T_E => 'e',
        Token::T_ETFILTER => 'filter',
        Token::T_TFILTER => 'endfilter',
        Token::T_SPACELESS => 'spaceless',
        Token::T_ESPACELESS => 'endspaceless',
        Token::T_CONCATE => '~',
        Token::T_SCOLON => ';',
        Token::T_COLON => ':',
        Token::T_NOT => '!',
        Token::T_OPARENT => '(',
        Token::T_CPARENT => ')',
        Token::T_COMMA => ',',
        Token::T_DOT => '.',
        Token::T_FILTER_PIPE => '|',
        Token::T_DOTDOT => '..',
        Token::T_TEST_OPERATOR => 'is',
        Token::T_IN => 'in',
        Token::T_LT => '<',
        Token::T_GT => '>',
        Token::T_LE => '<=',
        Token::T_IDENTICAL => '===',
        Token::T_EQ => '==',
        Token::T_GE => '>=',
        Token::T_NE_IDENTICAL => '!==',
        Token::T_NE => '!=',
        Token::T_PLUS => '+',
        Token::T_MINUS => '-',
        Token::T_DIV => '/',
        Token::T_TIMES => '*',
        Token::T_MOD => '%',
        Token::T_PAW => '**',
        Token::T_OBRACKETS => '[',
        Token::T_CBRACKETS => ']',
        Token::T_ARRAY_OPERATOR => '=>',
        Token::T_AND => '&&',
        Token::T_OR => '||',
        Token::T_XOR => 'xor',
        Token::T_ASSIGN => '=',
        Token::T_PLUS1 => '++',
        Token::T_MINUS1 => '--',
        Token::T_ADD_ASSIGN => '+=',
        Token::T_SUB_ASSIGN => '-=',
        Token::T_MUNTI_ASSIGN => '*=',
        Token::T_DIV_ASSIGN => '/=',
        Token::T_CONCAT_ASSIGN => '.=',
        Token::T_OBJ => '->',
        Token::T_CLASS_OPERATOR => '::',
        Token::T_NSSEPERATOR => '\\',
        Token::T_TERNARY_OPERATOR => '?:',
        Token::T_QUESTION => '?'
    ];

    /**
     * Get the token type revers name
     *
     * @param int $type
     *
     * @return string|null
     */
    public static function getTypeName($type) {
        $type = (int) $type;
        if (isset(static::$tokenTypesNames[$type])) {
            return static::$tokenTypesNames[$type];
        }
        return null;
    }

    /**
     * Guess the givin type value.
     *
     * @param int $type
     *
     * @return string|null
     */
    public static function guessTypeValue($type) {
        $type = (int) $type;
        if (isset(static::$posibleTypesValues[$type])) {
            return static::$posibleTypesValues[$type];
        }
        return null;
    }

}