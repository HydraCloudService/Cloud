<?php

namespace hydracloud\cloud\event\impl\server;

use hydracloud\cloud\server\CloudServer;

class ServerCrashEvent extends ServerEvent {

    public function __construct(
        CloudServer $server,
        private readonly array $data
    ) {
        parent::__construct($server);
    }

    public function getData(): array {
        return $this->data;
    }
}