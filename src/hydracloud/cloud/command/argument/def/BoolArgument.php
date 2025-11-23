<?php

namespace hydracloud\cloud\command\argument\def;

use hydracloud\cloud\command\argument\CommandArgument;

final readonly class BoolArgument extends CommandArgument {

    public function parseValue(string $input): bool {
        return strtolower($input) === "true" || strtolower($input) === "yes";
    }

    public function getType(): string {
        return "boolean";
    }
}