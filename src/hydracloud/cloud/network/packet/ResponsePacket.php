<?php

namespace hydracloud\cloud\network\packet;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\data\PacketData;

abstract class ResponsePacket extends CloudPacket {

    private string $requestId = "";

    public function encode(PacketData $packetData): void {
        parent::encode($packetData);
        $packetData->write($this->requestId);
    }

    public function decode(PacketData $packetData): void {
        parent::decode($packetData);
        $this->requestId = $packetData->readString();
    }

    public function getRequestId(): string {
        return $this->requestId;
    }

    public function setRequestId(string $requestId): self {
        $this->requestId = $requestId;
        return $this;
    }

    final public function handle(ServerClient $client): void {}
}