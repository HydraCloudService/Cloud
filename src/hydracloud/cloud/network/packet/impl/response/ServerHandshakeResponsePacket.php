<?php

namespace hydracloud\cloud\network\packet\impl\response;

use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\network\packet\impl\type\VerifyStatus;
use hydracloud\cloud\network\packet\ResponsePacket;

final class ServerHandshakeResponsePacket extends ResponsePacket {

    public function __construct(
        private ?VerifyStatus $verifyStatus = null {
            get {
                return $this->verifyStatus;
            }
        },
    ) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->writeVerifyStatus($this->verifyStatus);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->verifyStatus = $packetData->readVerifyStatus();
    }

    public static function create(VerifyStatus $verifyStatus): self {
        return new self($verifyStatus);
    }
}