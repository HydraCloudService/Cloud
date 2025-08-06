<?php

namespace hydracloud\cloud\command\argument\def;

use hydracloud\cloud\command\argument\CommandArgument;

final readonly class BoolArgument extends CommandArgument {

    public function parseValue(string $input): bool {
        if (strtolower($input) == "true" || strtolower($input) == "yes") return true;
        return false;
    }

    public function getType(): string {
        return "boolean";
    }
}