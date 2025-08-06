<?php

namespace hydracloud\cloud\command\argument\def;

use hydracloud\cloud\command\argument\CommandArgument;
use hydracloud\cloud\command\argument\exception\ArgumentParseException;
use hydracloud\cloud\plugin\CloudPlugin;
use hydracloud\cloud\plugin\CloudPluginManager;

final readonly class PluginArgument extends CommandArgument {

    public function parseValue(string $input): CloudPlugin {
        if (($plugin = CloudPluginManager::getInstance()->get($input)) !== null) return $plugin;
        throw new ArgumentParseException();
    }

    public function getType(): string {
        return "plugin";
    }
}