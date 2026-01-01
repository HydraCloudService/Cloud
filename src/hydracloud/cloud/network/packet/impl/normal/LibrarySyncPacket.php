<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\library\Library;
use hydracloud\cloud\library\LibraryManager;
use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\network\packet\data\PacketData;

final class LibrarySyncPacket extends CloudPacket {

    private array $data = [];

    public function __construct() {
        foreach (array_filter(LibraryManager::getInstance()->getAll(), fn(Library $library) => $library->isCloudBridgeOnly()) as $lib) {
            $this->data[] = [
                "name" => $lib->getName(),
                "path" => $lib->getUnzipLocation()
            ];
        }
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