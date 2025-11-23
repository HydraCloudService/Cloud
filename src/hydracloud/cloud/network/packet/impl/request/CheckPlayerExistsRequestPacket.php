<?php

namespace hydracloud\cloud\network\packet\impl\request;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\network\packet\impl\response\CheckPlayerExistsResponsePacket;
use hydracloud\cloud\network\packet\RequestPacket;
use hydracloud\cloud\player\CloudPlayerManager;

class CheckPlayerExistsRequestPacket extends RequestPacket {

    public function __construct(private string $player = "" {
        get {
            return $this->player;
        }
    }) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->player);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->player = $packetData->readString();
    }

    public function handle(ServerClient $client): void {
        $isPlayer = (CloudPlayerManager::getInstance()->get($this->player) !== null);
        $alreadyConnected = false;

        if ((CloudPlayerManager::getInstance()->get($this->player) !== null) && CloudPlayerManager::getInstance()->get($this->player)->getCurrentServer() !== null) {
            $alreadyConnected = true;
        }

        $result = ($isPlayer && !$alreadyConnected);
        $this->sendResponse(new CheckPlayerExistsResponsePacket($result), $client);
    }
}