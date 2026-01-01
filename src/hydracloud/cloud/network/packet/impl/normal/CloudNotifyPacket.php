<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\network\packet\data\PacketData;

final class CloudNotifyPacket extends CloudPacket {

    public function __construct(private string $message = "") {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->message);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->message = $packetData->readString();
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function handle(ServerClient $client): void {}

    public static function create(string $message): self {
        return new self($message);
    }
}