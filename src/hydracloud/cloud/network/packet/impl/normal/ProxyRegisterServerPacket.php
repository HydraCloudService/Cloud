<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\network\packet\data\PacketData;

final class ProxyRegisterServerPacket extends CloudPacket {

    public function __construct(
        private string $serverName = "" {
            get {
                return $this->serverName;
            }
        },
        private int $port = 0 {
            get {
                return $this->port;
            }
        }
    ) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->serverName);
        $packetData->write($this->port);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->serverName = $packetData->readString();
        $this->port = $packetData->readInt();
    }

    public function handle(ServerClient $client): void {}

    public static function create(string $server, int $port): self {
        return new self($server, $port);
    }
}