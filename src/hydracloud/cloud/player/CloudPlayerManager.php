<?php

namespace hydracloud\cloud\player;

use hydracloud\cloud\event\impl\player\PlayerConnectEvent;
use hydracloud\cloud\event\impl\player\PlayerDisconnectEvent;
use hydracloud\cloud\network\packet\impl\normal\PlayerSyncPacket;
use hydracloud\cloud\terminal\log\CloudLogger;
use hydracloud\cloud\util\SingletonTrait;

final class CloudPlayerManager {
    use SingletonTrait;

    /** @var array<CloudPlayer> */
    private array $players = [];

    public function __construct() {
        self::setInstance($this);
    }

    public function add(CloudPlayer $player): void {
        if ($player->getCurrentServer() === null) CloudLogger::get()->info("Player %s is connected. (On: %s)", $player->getName(), ($player->getCurrentProxy()?->getName() ?? "NULL"));
        else CloudLogger::get()->info("Player %s is connected. (On: %s)", $player->getName(), ($player->getCurrentServer()->getName() ?? "NULL"));

        $this->players[$player->getName()] = $player;
        PlayerSyncPacket::create($player, false)->broadcastPacket();

        (new PlayerConnectEvent($player, ($player->getCurrentServer() ?? $player->getCurrentProxy())))->call();
    }

    public function remove(CloudPlayer $player): void {
        if ($player->getCurrentServer() === null) CloudLogger::get()->info("Player %s is disconnected. (From: %s)", $player->getName(), ($player->getCurrentProxy()?->getName() ?? "NULL"));
        else CloudLogger::get()->info("Player %s is disconnected. (From: %s)", $player->getName(), ($player->getCurrentServer()->getName() ?? "NULL"));

        if (isset($this->players[$player->getName()])) unset($this->players[$player->getName()]);
        (new PlayerDisconnectEvent($player, ($player->getCurrentServer() ?? $player->getCurrentProxy())))->call();

        $player->setCurrentServer(null);
        $player->setCurrentProxy(null);

        PlayerSyncPacket::create($player, true)->broadcastPacket();
    }

    public function get(string $name): ?CloudPlayer {
        if (isset($this->players[$name])) return $this->players[$name];
        foreach ($this->players as $player) {
            if ($player->getXboxUserId() == $name || $player->getUniqueId() == $name) return $player;
        }
        
        return null;
    }

    public function getAll(): array {
        return $this->players;
    }
}