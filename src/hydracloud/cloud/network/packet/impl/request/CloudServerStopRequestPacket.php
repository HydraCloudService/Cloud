<?php

namespace hydracloud\cloud\network\packet\impl\request;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\network\packet\impl\response\CloudServerStopResponsePacket;
use hydracloud\cloud\network\packet\impl\type\ErrorReason;
use hydracloud\cloud\network\packet\RequestPacket;
use hydracloud\cloud\server\CloudServerManager;

final class CloudServerStopRequestPacket extends RequestPacket {

    public function __construct(private string $server = "") {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->server);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->server = $packetData->readString();
    }

    public function getServer(): string {
        return $this->server;
    }

    public function handle(ServerClient $client): void {
        if (CloudServerManager::getInstance()->stop($this->server)) {
            $this->sendResponse(new CloudServerStopResponsePacket(ErrorReason::NO_ERROR()), $client);
        } else $this->sendResponse(new CloudServerStopResponsePacket(ErrorReason::SERVER_EXISTENCE()), $client);
    }
}