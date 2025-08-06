<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\cache\InGameModule;
use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\network\packet\data\PacketData;

final class ModuleSyncPacket extends CloudPacket {

    private array $data;

    public function __construct() {
        $this->data = [
            "sign" => ["enabled" => InGameModule::getModuleState(InGameModule::SIGN_MODULE)],
            "npc" => ["enabled" => InGameModule::getModuleState(InGameModule::NPC_MODULE)],
            "hub_command" => ["enabled" => InGameModule::getModuleState(InGameModule::HUB_COMMAND_MODULE)],
        ];
    }

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->data);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->data = $packetData->readArray();
    }

    public function getData(): array {
        return $this->data;
    }

    public function handle(ServerClient $client): void {}

    public static function create(): self {
        return new self();
    }
}