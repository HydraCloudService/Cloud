<?php

namespace hydracloud\cloud\command\argument\def;

use hydracloud\cloud\command\argument\exception\ArgumentParseException;
use hydracloud\cloud\command\argument\CommandArgument;
use hydracloud\cloud\server\CloudServer;
use hydracloud\cloud\server\CloudServerManager;

final readonly class ServerArgument extends CommandArgument {

    public function parseValue(string $input): CloudServer {
        if (($server = CloudServerManager::getInstance()->get($input)) !== null) return $server;
        throw new ArgumentParseException();
    }

    public function getType(): string {
        return "server";
    }
}