<?php

namespace hydracloud\cloud\event\impl\player;

use hydracloud\cloud\player\CloudPlayer;
use hydracloud\cloud\server\CloudServer;

class PlayerConnectEvent extends PlayerEvent {

    public function __construct(
        CloudPlayer $player,
        private readonly CloudServer $server
    ) {
        parent::__construct($player);
    }

    public function getServer(): CloudServer {
        return $this->server;
    }
}