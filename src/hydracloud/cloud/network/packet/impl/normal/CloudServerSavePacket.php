<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\server\CloudServerManager;

final class CloudServerSavePacket extends CloudPacket {

    public function handle(ServerClient $client): void {
        if (($server = $client->getServer()) !== null) {
            CloudServerManager::getInstance()->save($server);
        }
    }

    public static function create(): self {
        return new self();
    }
}