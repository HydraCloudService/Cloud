<?php

namespace hydracloud\cloud\command\impl\plugin;

use hydracloud\cloud\command\argument\def\PluginArgument;
use hydracloud\cloud\command\Command;
use hydracloud\cloud\command\sender\ICommandSender;
use hydracloud\cloud\plugin\CloudPluginManager;

final class EnableCommand extends Command {

    public function __construct() {
        parent::__construct("enable", "Enable a disabled plugin");

        $this->addParameter(new PluginArgument(
            "plugin",
            false,
            "The plugin was not found."
        ));
    }

    public function run(ICommandSender $sender, string $label, array $args): bool {
        $plugin = $args["plugin"];
        CloudPluginManager::getInstance()->enable($plugin);
        return true;
    }
}