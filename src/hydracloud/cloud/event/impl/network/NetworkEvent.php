<?php

namespace hydracloud\cloud\event\impl\network;

use hydracloud\cloud\event\Event;
use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\CloudPacket;

abstract class NetworkEvent extends Event {

    public function __construct(
        private readonly CloudPacket $packet,
        private readonly ServerClient $client
    ) {}

    public function getPacket(): CloudPacket {
        return $this->packet;
    }

    public function getClient(): ServerClient {
        return $this->client;
    }
}