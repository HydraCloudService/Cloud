<?php

namespace hydracloud\cloud\network\packet\impl\request;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\network\packet\impl\response\CheckPlayerNotifyResponsePacket;
use hydracloud\cloud\network\packet\RequestPacket;
use hydracloud\cloud\provider\CloudProvider;

final class CheckPlayerNotifyRequestPacket extends RequestPacket {

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
        CloudProvider::current()->hasNotificationsEnabled($this->player)
            ->then(fn(bool $v) => $this->sendResponse(new CheckPlayerNotifyResponsePacket($v), $client));
    }
}