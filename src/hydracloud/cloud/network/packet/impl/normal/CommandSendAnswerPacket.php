<?php

namespace hydracloud\cloud\network\packet\impl\normal;

use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\network\packet\data\PacketData;
use hydracloud\cloud\network\packet\impl\type\CommandExecutionResult;
use hydracloud\cloud\util\promise\Promise;

final class CommandSendAnswerPacket extends CloudPacket {

    public function __construct(private ?CommandExecutionResult $result = null) {}

    public function encodePayload(PacketData $packetData): void {
        $packetData->writeCommandExecutionResult($this->result);
    }

    public function decodePayload(PacketData $packetData): void {
        $this->result = $packetData->readCommandExecutionResult();
    }

    public function getResult(): ?CommandExecutionResult {
        return $this->result;
    }

    public function handle(ServerClient $client): void {
        if (($server = $client->getServer()) !== null) {
            $promise = $server->getInternalCloudServerStorage()->get("command_promise");
            if ($promise instanceof Promise) {
                $promise->resolve($this->result);
            }
        }
    }

    public static function create(CommandExecutionResult $result): self {
        return new self($result);
    }
}