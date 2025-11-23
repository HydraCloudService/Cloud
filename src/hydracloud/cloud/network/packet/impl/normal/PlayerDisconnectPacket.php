<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\client\ServerClientCache;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\player\CloudPlayerManager;

final class PlayerDisconnectPacket extends CloudPacket {

    public function __construct(private ?string $playerName = "") {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->playerName);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->playerName = $packetData->readString();
    }

    public function getPlayer(): string {
        return $this->playerName;
    }

    public function handle(ServerClient $client): void {
        if (($player = CloudPlayerManager::getInstance()->get($this->playerName)) !== null) {
            if ($player->getCurrentProxy() === null) {
                CloudPlayerManager::getInstance()->remove($player);
            } else {
                if (($server = ServerClientCache::getInstance()->getServer($client)) !== null) {
                    if ($server->getTemplate()->getTemplateType()->isProxy()) {
                        CloudPlayerManager::getInstance()->remove($player);
                    }
                }
            }
        }
    }

    public static function create(string $player): self {
        return new self($player);
    }
}