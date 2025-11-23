<?php

namespace hydracloud\cloud\command\impl;

use hydracloud\cloud\command\Command;
use hydracloud\cloud\command\sender\ICommandSender;
use hydracloud\cloud\config\impl\MainConfig;
use hydracloud\cloud\exception\ExceptionHandler;

final class DebugCommand extends Command {

    public function __construct() {
        parent::__construct("debug", "Enable or disable the debug mode");
    }

    public function run(ICommandSender $sender, string $label, array $args): bool {
        if (MainConfig::getInstance()->isDebugMode()) {
            $sender->success("The §edebug mode §rhas been §cdisabled§r!");
            MainConfig::getInstance()->setDebugMode(false);
        } else {
            $sender->success("The §edebug mode §rhas been §aenabled§r!");
            MainConfig::getInstance()->setDebugMode(true);
        }

        ExceptionHandler::tryCatch(fn() => MainConfig::getInstance()->save(), "Failed to save main config");
        return true;
    }
}