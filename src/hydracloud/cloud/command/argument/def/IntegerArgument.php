<?php

namespace hydracloud\cloud\command\argument\def;

use hydracloud\cloud\command\argument\exception\ArgumentParseException;
use hydracloud\cloud\command\argument\CommandArgument;

final readonly class IntegerArgument extends CommandArgument {

    public function parseValue(string $input): int {
        if (is_numeric($input)) return intval($input);
        return throw new ArgumentParseException();
    }

    public function getType(): string {
        return "integer";
    }
}