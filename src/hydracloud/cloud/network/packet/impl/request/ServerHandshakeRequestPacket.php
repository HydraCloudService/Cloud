<?php

namespace hydracloud\cloud\network\packet\impl\request;

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

final class ServerHandshakeRequestPacket extends RequestPacket {

    public function __construct(
        private ?string $serverName = null {
            get {
                return $this->serverName;
            }
        },
        private ?int $processId = null {
            get {
                return $this->processId;
            }
        },
        private ?int $maxPlayers = null {
            get {
                return $this->maxPlayers;
            }
        }
    ) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->serverName)
            ->write($this->processId)
            ->write($this->maxPlayers);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->serverName = $packetData->readString();
        $this->processId = $packetData->readInt();
        $this->maxPlayers = $packetData->readInt();
    }

    public function handle(ServerClient $client): void {
        if (($server = CloudServerManager::getInstance()->get($this->serverName)) !== null) {
            ServerClientCache::getInstance()->add($server, $client);
            CloudLogger::get()->success("The server §b" . $server->getName() . " §rhas §aconnected §rto the cloud.");
            $server->getCloudServerData()->maxPlayers = $this->maxPlayers;
            $server->getCloudServerData()->processId = $this->processId;
            $server->setVerifyStatus(VerifyStatus::VERIFIED());
            $server->sync();
            $this->sendResponse(new ServerHandshakeResponsePacket(VerifyStatus::VERIFIED()), $client);
            Network::getInstance()?->broadcastPacket(new ServerSyncPacket($server), $client);
            $server->setServerStatus(ServerStatus::ONLINE());
        } else {
            $this->sendResponse(new ServerHandshakeResponsePacket(VerifyStatus::DENIED()), $client);
        }
    }
}