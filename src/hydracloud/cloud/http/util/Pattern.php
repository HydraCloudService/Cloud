<?php

namespace hydracloud\cloud\http\util;

use JetBrains\PhpStorm\Pure;

final class Pattern {

    public const string TYPE_STRING = "string";
    public const string TYPE_INT = "int";
    public const string TYPE_FLOAT = "float";

    public const string OPTION_NAME = "name";
    public const string OPTION_MIN_LENGTH = "len-min";
    public const string OPTION_MAX_LENGTH = "len-max";
    public const string OPTION_TYPE = "type";

    #[Pure] public static function isValid(string $string, array $pattern): bool {
        if (isset($pattern[self::OPTION_MIN_LENGTH]) && (strlen($string) < $pattern[self::OPTION_MIN_LENGTH])) {
            return false;
        }

        if (isset($pattern[self::OPTION_MAX_LENGTH]) && (strlen($string) > $pattern[self::OPTION_MAX_LENGTH])) {
            return false;
        }

        if (isset($pattern[self::OPTION_TYPE]) && (self::asType($string) !== $pattern[self::OPTION_MIN_LENGTH])) {
            return false;
        }

        return true;
    }

    protected static function asType(mixed $string): string {
        return match(true) {
            (((int)$string === $string)) => "int",
            (((float)$string === $string)) => "float",
            default => "string"
        };
    }
}