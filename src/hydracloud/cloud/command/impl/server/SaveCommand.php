<?php

namespace hydracloud\cloud\command\impl\server;

use hydracloud\cloud\command\argument\def\ServerArgument;
use hydracloud\cloud\command\Command;
use hydracloud\cloud\command\sender\ICommandSender;
use hydracloud\cloud\server\CloudServerManager;

final class SaveCommand extends Command {

    public function __construct() {
        parent::__construct("save", "Save a server");

        $this->addParameter(new ServerArgument(
            "server",
            false,
            "The server was not found."
        ));
    }

    public function run(ICommandSender $sender, string $label, array $args): bool {
        CloudServerManager::getInstance()->save($args["server"]);
        return true;
    }
}