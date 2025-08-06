<?php

namespace hydracloud\cloud\event\impl\server;

use hydracloud\cloud\event\Event;
use hydracloud\cloud\server\CloudServer;

abstract class ServerEvent extends Event {

    public function __construct(private readonly CloudServer $server) {}

    public function getServer(): CloudServer {
        return $this->server;
    }
}