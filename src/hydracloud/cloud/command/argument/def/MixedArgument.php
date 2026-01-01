<?php

namespace hydracloud\cloud\command\argument\def;

use hydracloud\cloud\command\argument\CommandArgument;

final readonly class MixedArgument extends CommandArgument {

    public function parseValue(string $input): string {
        return $input;
    }

    public function getType(): string {
        return "mixed";
    }
}