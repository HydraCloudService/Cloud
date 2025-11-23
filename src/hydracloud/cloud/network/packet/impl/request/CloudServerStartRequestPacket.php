<?php

namespace hydracloud\cloud\network\packet\impl\request;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\network\packet\impl\response\CloudServerStartResponsePacket;
use hydracloud\cloud\network\packet\impl\type\ErrorReason;
use hydracloud\cloud\network\packet\RequestPacket;
use hydracloud\cloud\server\CloudServerManager;
use hydracloud\cloud\template\TemplateManager;

final class CloudServerStartRequestPacket extends RequestPacket {

    public function __construct(
        private string $template = "",
        private int $count = 0
    ) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->write($this->template);
        $packetData->write($this->count);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->template = $packetData->readString();
        $this->count = $packetData->readInt();
    }

    public function getTemplate(): string {
        return $this->template;
    }

    public function getCount(): int {
        return $this->count;
    }

    public function handle(ServerClient $client): void {
        if (($template = TemplateManager::getInstance()->get($this->template)) !== null) {
            if (count(CloudServerManager::getInstance()->getAll($template)) < $template->getSettings()->getMaxServerCount()) {
                CloudServerManager::getInstance()->start($template, $this->count);
                $this->sendResponse(new CloudServerStartResponsePacket(ErrorReason::NO_ERROR()), $client);
            } else $this->sendResponse(new CloudServerStartResponsePacket(ErrorReason::MAX_SERVERS()), $client);
        } else $this->sendResponse(new CloudServerStartResponsePacket(ErrorReason::TEMPLATE_EXISTENCE()), $client);
    }
}