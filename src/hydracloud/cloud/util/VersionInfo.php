<?php

namespace hydracloud\cloud\util;

final class VersionInfo {

    public const VERSION = "2.0.5";
    public const DEVELOPERS = ["xxFLORII"];
    public const BETA = true;

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