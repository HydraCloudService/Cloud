<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\network\packet\impl\type\TextType;
use hydracloud\cloud\player\CloudPlayerManager;

final class PlayerTextPacket extends CloudPacket {

    public function __construct(
        private string $player = "",
        private string $message = "",
        private ?TextType $textType = null
    ) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->player);
        $packetData->write($this->message);
        $packetData->writeTextType($this->textType);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->player = $packetData->readString();
        $this->message = $packetData->readString();
        $this->textType = $packetData->readTextType();
    }

    public function getPlayer(): string {
        return $this->player;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function getTextType(): TextType {
        return $this->textType;
    }

    public function handle(ServerClient $client): void {
        $player = CloudPlayerManager::getInstance()->get($this->player);
        if ($player !== null) {
            $player->send($this->message, $this->textType);
        } else if ($this->player === "*") {
            foreach (CloudPlayerManager::getInstance()->getAll() as $player) $player->send($this->message, $this->textType);
        }
    }

    public static function create(string $player, string $message, TextType $textType): self {
        return new self($player, $message, $textType);
    }
}