<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\network\packet\data\PacketData;

final class ProxyUnregisterServerPacket extends CloudPacket {

    public function __construct(private string $serverName = "") {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->serverName);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->serverName = $packetData->readString();
    }

    public function getServerName(): string {
        return $this->serverName;
    }

    public function handle(ServerClient $client): void {}

    public static function create(string $server): self {
        return new self($server);
    }
}