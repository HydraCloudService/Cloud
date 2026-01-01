<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\template\Template;

final class TemplateSyncPacket extends CloudPacket {

    public function __construct(
        private ?Template $template = null,
        private bool $removal = false
    ) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->writeTemplate($this->template);
        $packetData->write($this->removal);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->template = $packetData->readTemplate();
        $this->removal = $packetData->readBool();
    }

    public function getTemplate(): ?Template {
        return $this->template;
    }

    public function isRemoval(): bool {
        return $this->removal;
    }

    public function handle(ServerClient $client): void {}

    public static function create(Template $template, bool $removal): self {
        return new self($template, $removal);
    }
}