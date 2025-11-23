<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\player\CloudPlayer;

final class PlayerSyncPacket extends CloudPacket {

    public function __construct(
        private ?CloudPlayer $player = null {
            get {
                return $this->player;
            }
        },
        private bool $removal = false {
            get {
                return $this->removal;
            }
        }
    ) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->writePlayer($this->player);
        $packetData->write($this->removal);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->player = $packetData->readPlayer();
        $this->removal = $packetData->readBool();
    }

    public function handle(ServerClient $client): void {}

    public static function create(CloudPlayer $player, bool $removal): self {
        return new self($player, $removal);
    }
}