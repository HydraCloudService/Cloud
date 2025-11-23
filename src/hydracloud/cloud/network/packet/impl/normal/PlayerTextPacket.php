<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\network\packet\impl\type\TextType;
use hydracloud\cloud\player\CloudPlayerManager;

final class PlayerTextPacket extends CloudPacket {

    public function __construct(
        private string $player = "" {
            get {
                return $this->player;
            }
        },
        private string $message = "" {
            get {
                return $this->message;
            }
        },
        private ?TextType $textType = null {
            get {
                return $this->textType;
            }
        }
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

    public function handle(ServerClient $client): void {
        if (($player = CloudPlayerManager::getInstance()->get($this->player)) !== null) {
            $player->send($this->message, $this->textType);
        }
    }

    public static function create(string $player, string $message, TextType $textType): self {
        return new self($player, $message, $textType);
    }
}