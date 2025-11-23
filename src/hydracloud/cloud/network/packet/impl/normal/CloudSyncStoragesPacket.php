<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\server\CloudServerManager;

//sending to the sub servers
final class CloudSyncStoragesPacket extends CloudPacket {

    private array $storage = [] {
        get {
            return $this->storage;
        }
    }

    public function __construct() {
        foreach (CloudServerManager::getInstance()->getAll() as $server) {
            if (!$server->getInternalCloudServerStorage()->empty()) {
                $storage = $this->storage;
                $storage[$server->getName()] = $server->getInternalCloudServerStorage()->getAll();

                $this->storage = $storage;
            }
        }
    }

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->storage);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->storage = $packetData->readArray();
    }

    public function handle(ServerClient $client): void {}

    public static function create(): self {
        return new self();
    }
}