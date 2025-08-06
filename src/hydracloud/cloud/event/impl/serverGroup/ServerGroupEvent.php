<?php

namespace hydracloud\cloud\event\impl\serverGroup;

use hydracloud\cloud\event\Event;
use hydracloud\cloud\group\ServerGroup;

abstract class ServerGroupEvent extends Event {

    public function __construct(private readonly ServerGroup $serverGroup) {}

    public function getServerGroup(): ServerGroup {
        return $this->serverGroup;
    }
}