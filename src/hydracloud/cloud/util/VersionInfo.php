<?php

namespace hydracloud\cloud\util;

final class VersionInfo {

    public const string VERSION = "1.0.1";
    public const array DEVELOPERS = ["xxFLORII"];
    public const bool BETA = true;

    public static function getVersion(): int {
        return self::VERSION;
    }

    public static function getDevelopers(): array {
        return self::DEVELOPERS;
    }

    public static function isBeta(): bool {
        return self::BETA;
    }
}