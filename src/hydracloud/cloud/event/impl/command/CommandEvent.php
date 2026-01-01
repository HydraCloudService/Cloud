<?php

namespace hydracloud\cloud\event\impl\command;

use hydracloud\cloud\command\Command;
use hydracloud\cloud\event\Event;

abstract class CommandEvent extends Event {

    public function __construct(private readonly Command $command) {}

    public function getCommand(): Command {
        return $this->command;
    }
}