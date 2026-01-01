<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\CloudPacket;

final class KeepAlivePacket extends CloudPacket {

    public function handle(ServerClient $client): void {
        if (($server = $client->getServer()) !== null) {
            $server->setLastCheckTime(time());
            $server->sendDelayedPacket(new KeepAlivePacket(), 50);
        }
    }
}