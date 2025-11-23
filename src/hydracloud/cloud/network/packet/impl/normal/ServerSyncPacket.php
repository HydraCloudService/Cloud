<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\server\CloudServer;

final class ServerSyncPacket extends CloudPacket {

    public function __construct(
        private ?CloudServer $server = null {
            get {
                return $this->server;
            }
        },
        private bool $removal = false {
            get {
                return $this->removal;
            }
        }
    ) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->writeServer($this->server);
        $packetData->write($this->removal);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->server = $packetData->readServer();
        $this->removal = $packetData->readBool();
    }

    public function handle(ServerClient $client): void {}

    public static function create(CloudServer $server, bool $removal): self {
        return new self($server, $removal);
    }
}