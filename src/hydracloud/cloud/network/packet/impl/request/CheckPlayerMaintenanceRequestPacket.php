<?php

namespace hydracloud\cloud\network\packet\impl\request;

use hydracloud\cloud\cache\MaintenanceList;
use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\network\packet\impl\response\CheckPlayerMaintenanceResponsePacket;
use hydracloud\cloud\network\packet\RequestPacket;

final class CheckPlayerMaintenanceRequestPacket extends RequestPacket {

    public function __construct(private string $player = "") {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->player);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->player = $packetData->readString();
    }

    public function getPlayer(): string {
        return $this->player;
    }

    public function handle(ServerClient $client): void {
        $this->sendResponse(new CheckPlayerMaintenanceResponsePacket(MaintenanceList::is($this->player)), $client);
    }
}