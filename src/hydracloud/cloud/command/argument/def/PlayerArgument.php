<?php

namespace hydracloud\cloud\command\argument\def;

use hydracloud\cloud\command\argument\exception\ArgumentParseException;
use hydracloud\cloud\command\argument\CommandArgument;
use hydracloud\cloud\player\CloudPlayer;
use hydracloud\cloud\player\CloudPlayerManager;

final readonly class PlayerArgument extends CommandArgument {

    public function parseValue(string $input): CloudPlayer {
        if (($player = CloudPlayerManager::getInstance()->get($input)) !== null) return $player;
        throw new ArgumentParseException();
    }

    public function getType(): string {
        return "player";
    }
}