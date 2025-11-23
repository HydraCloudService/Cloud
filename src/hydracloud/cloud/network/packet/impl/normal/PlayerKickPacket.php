<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\player\CloudPlayerManager;

final class PlayerKickPacket extends CloudPacket {

    public function __construct(
        private string $playerName = "" {
            get {
                return $this->playerName;
            }
        },
        private string $reason = "" {
            get {
                return $this->reason;
            }
        }
    ) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->playerName);
        $packetData->write($this->reason);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->playerName = $packetData->readString();
        $this->reason = $packetData->readString();
    }

    public function handle(ServerClient $client): void {
        if (($player = CloudPlayerManager::getInstance()->get($this->playerName)) !== null) {
            $player->kick($this->reason);
        }
    }

    public static function create(string $player, string $reason): self {
        return new self($player, $reason);
    }
}