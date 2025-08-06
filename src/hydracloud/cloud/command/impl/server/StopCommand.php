<?php

namespace hydracloud\cloud\command\impl\server;

use hydracloud\cloud\command\argument\def\MultipleTypesArgument;
use hydracloud\cloud\command\argument\def\ServerArgument;
use hydracloud\cloud\command\argument\def\ServerGroupArgument;
use hydracloud\cloud\command\argument\def\StringEnumArgument;
use hydracloud\cloud\command\argument\def\TemplateArgument;
use hydracloud\cloud\command\Command;
use hydracloud\cloud\command\sender\ICommandSender;
use hydracloud\cloud\server\CloudServerManager;

final class StopCommand extends Command {

    public function __construct() {
        parent::__construct("stop", "Stop a server");

        $this->addParameter(new MultipleTypesArgument(
            "object",
            [
                new ServerArgument("server", false),
                new TemplateArgument("template", false),
                new ServerGroupArgument("group", false),
                new StringEnumArgument("all", ["all"], false, false)
            ],
            false,
            "The server was not found."
        ));
    }

    public function run(ICommandSender $sender, string $label, array $args): bool {
        $object = $args["object"];

        if (!($object == "all" ? CloudServerManager::getInstance()->stopAll() : CloudServerManager::getInstance()->stop($object))) {
            $sender->warn("The server was not found!");
        }
        return true;
    }
}