<?php

namespace hydracloud\cloud\command\impl;

use hydracloud\cloud\command\Command;
use hydracloud\cloud\command\sender\ICommandSender;
use hydracloud\cloud\setup\impl\ConfigSetup;

final class ConfigureCommand extends Command {

    public function __construct() {
        parent::__construct("configure", "Reconfigure the config");
    }

    public function run(ICommandSender $sender, string $label, array $args): bool {
        new ConfigSetup()->startSetup();
        return true;
    }
}