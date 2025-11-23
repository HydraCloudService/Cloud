<?php

namespace hydracloud\cloud\command\argument\def;

use hydracloud\cloud\command\argument\CommandArgument;
use hydracloud\cloud\command\argument\exception\ArgumentParseException;
use hydracloud\cloud\group\ServerGroup;
use hydracloud\cloud\group\ServerGroupManager;

final readonly class ServerGroupArgument  extends CommandArgument {

    public function parseValue(string $input): ServerGroup {
        if (($group = ServerGroupManager::getInstance()->get($input)) !== null) return $group;
        throw new ArgumentParseException();
    }

    public function getType(): string {
        return "serverGroup";
    }
}