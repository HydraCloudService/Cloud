<?php

namespace hydracloud\cloud\command\argument\def;

use hydracloud\cloud\command\argument\exception\ArgumentParseException;
use hydracloud\cloud\command\argument\CommandArgument;

final readonly class FloatArgument extends CommandArgument {

    public function parseValue(string $input): int {
        if (is_numeric($input)) return floatval($input);
        return throw new ArgumentParseException();
    }

    public function getType(): string {
        return "float";
    }
}