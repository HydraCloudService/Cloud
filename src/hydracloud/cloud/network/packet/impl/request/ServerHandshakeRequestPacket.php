<?php

namespace hydracloud\cloud\network\packet\impl\request;

use hydracloud\cloud\config\impl\MainConfig;
use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\client\ServerClientCache;
use hydracloud\cloud\network\Network;
use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\network\packet\impl\normal\ServerSyncPacket;
use hydracloud\cloud\network\packet\impl\response\ServerHandshakeResponsePacket;
use hydracloud\cloud\network\packet\impl\type\VerifyStatus;
use hydracloud\cloud\network\packet\RequestPacket;
use hydracloud\cloud\server\CloudServerManager;
use hydracloud\cloud\server\util\ServerStatus;
use hydracloud\cloud\terminal\log\CloudLogger;
use hydracloud\cloud\util\terminal\TerminalUtils;

final class ServerHandshakeRequestPacket extends RequestPacket {

    public function __construct(
        private ?string $serverName = null,
        private ?string $authKey = null,
        private ?int $processId = null,
        private ?int $maxPlayers = null
    ) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->serverName)
            ->write($this->authKey)
            ->write($this->processId)
            ->write($this->maxPlayers);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->serverName = $packetData->readString();
        $this->authKey = $packetData->readString();
        $this->processId = $packetData->readInt();
        $this->maxPlayers = $packetData->readInt();
    }

    public function getServerName(): ?string {
        return $this->serverName;
    }

    public function getAuthKey(): ?string{
        return $this->authKey;
    }

    public function getProcessId(): ?int {
        return $this->processId;
    }

    public function getMaxPlayers(): ?int {
        return $this->maxPlayers;
    }

    public function handle(ServerClient $client): void {
        if (($server = CloudServerManager::getInstance()->get($this->serverName)) !== null) {
            if ($server->getVerifyStatus() === VerifyStatus::VERIFIED()) {
                $this->sendResponse(new ServerHandshakeResponsePacket(VerifyStatus::DENIED()), $client);
                CloudLogger::get()->warn("§cThe server §b" . $server->getName() . " §cis already verified§r.");
                return;
            }

            if ($this->authKey == null || $this->authKey != MainConfig::getInstance()->getNetworkAuthKey()) {
                $this->sendResponse(new ServerHandshakeResponsePacket(VerifyStatus::DENIED()), $client);
                CloudLogger::get()->warn("The server §b" . $server->getName() . " §rwas §cdenied §rdue to an invalid authentication key.");
                return;
            }
            
            ServerClientCache::getInstance()->add($server, $client);
            CloudLogger::get()->success("The server §b" . $server->getName() . " §rhas §aconnected §rto the cloud.");
            $server->getCloudServerData()->setMaxPlayers($this->maxPlayers);
            $server->getCloudServerData()->setProcessId($this->processId);
            $server->setVerifyStatus(VerifyStatus::VERIFIED());
            $server->sync();
            $this->sendResponse(new ServerHandshakeResponsePacket(VerifyStatus::VERIFIED()), $client);
            Network::getInstance()->broadcastPacket(new ServerSyncPacket($server), $client);
            $server->setServerStatus(ServerStatus::ONLINE());
        } else $this->sendResponse(new ServerHandshakeResponsePacket(VerifyStatus::DENIED()), $client);
    }
}