<?php

namespace hydracloud\cloud\command\impl;

use hydracloud\cloud\command\argument\def\BoolArgument;
use hydracloud\cloud\command\Command;
use hydracloud\cloud\command\sender\ICommandSender;
use hydracloud\cloud\HydraCloud;

final class ExitCommand extends Command {

    public function __construct() {
        parent::__construct("exit", "Stop the cloud");
        $this->addParameter(new BoolArgument(
            "confirmation",
            false
        ));
    }

    public function run(ICommandSender $sender, string $label, array $args): bool {
        if ($args["confirmation"]) HydraCloud::getInstance()->shutdown();
        return true;
    }
}