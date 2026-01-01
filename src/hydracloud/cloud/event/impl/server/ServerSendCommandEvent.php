<?php

namespace hydracloud\cloud\event\impl\server;

use hydracloud\cloud\event\Cancelable;
use hydracloud\cloud\event\CancelableTrait;
use hydracloud\cloud\server\CloudServer;

class ServerSendCommandEvent extends ServerEvent implements Cancelable {
    use CancelableTrait;

    public function __construct(
        CloudServer $server,
        private readonly string $commandLine
    ) {
        parent::__construct($server);
    }

    public function getCommandLine(): string {
        return $this->commandLine;
    }
}