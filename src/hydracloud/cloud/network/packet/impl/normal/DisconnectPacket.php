<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\event\impl\server\ServerCrashEvent;
use hydracloud\cloud\event\impl\server\ServerDisconnectEvent;
use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\client\ServerClientCache;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\network\packet\impl\type\DisconnectReason;
use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\server\CloudServerManager;
use hydracloud\cloud\server\crash\CrashChecker;
use hydracloud\cloud\server\util\ServerStatus;
use hydracloud\cloud\terminal\log\CloudLogger;
use hydracloud\cloud\util\FileUtils;
use hydracloud\cloud\util\terminal\TerminalUtils;

final class DisconnectPacket extends CloudPacket {

    public function __construct(private ?DisconnectReason $disconnectReason = null) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->writeDisconnectReason($this->disconnectReason);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->disconnectReason = $packetData->readDisconnectReason();
    }

    public function handle(ServerClient $client): void {
        if (($server = $client->getServer()) !== null) {
            if ($server->getServerStatus() === ServerStatus::OFFLINE()) {
                if (isset(CloudServerManager::getInstance()->getAll()[$server->getName()])) CloudServerManager::getInstance()->remove($server);
                return;
            }

            $server->setServerStatus(ServerStatus::OFFLINE());
            new ServerDisconnectEvent($server)->call();
            if (CrashChecker::checkCrashed($server, $crashData)) {
                new ServerCrashEvent($server, $crashData)->call();
                CloudLogger::get()->info("The server §b" . $server->getName() . " §ccrashed§r, writing crash file...");
                CloudServerManager::getInstance()->printServerStackTrace($server->getName(), $crashData);
                CrashChecker::writeCrashFile($server, $crashData);
            } else {
                CloudLogger::get()->success("The server §b" . $server->getName() . " §rhas §cdisconnected §rfrom the cloud.");
            }

            if ($server->getCloudServerData()->getProcessId() !== 0) TerminalUtils::kill($server->getCloudServerData()->getProcessId());

            ServerClientCache::getInstance()->remove($server);
            CloudServerManager::getInstance()->remove($server);
            if (!$server->getTemplate()->getSettings()->isStatic()) FileUtils::removeDirectory($server->getPath());
        }
    }

    public function getDisconnectReason(): ?DisconnectReason {
        return $this->disconnectReason;
    }

    public static function create(DisconnectReason $disconnectReason): self {
        return new self($disconnectReason);
    }
}