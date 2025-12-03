<?php

namespace hydracloud\cloud\network\client;

use Closure;
use hydracloud\cloud\network\Network;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\HydraCloud;
use hydracloud\cloud\server\CloudServer;
use hydracloud\cloud\terminal\log\CloudLogger;
use hydracloud\cloud\util\net\Address;
use ReflectionClass;

final class ServerClient {

    private array $delayedPackets = [];

    public function __construct(private readonly Address $address) {}

    public function sendPacket(CloudPacket $packet): bool {
        if (!Network::getInstance()->sendPacket($packet, $this)) {
            CloudLogger::get()->debug("Failed to send packet " . new ReflectionClass($packet)->getShortName() . " to " . $this->address);
            return false;
        }
        return true;
    }

    /** @internal */
    public function unsetDelayedPacket(int $index): void {
        if (isset($this->delayedPackets[$index])) {
            unset($this->delayedPackets[$index]);
            $this->delayedPackets = array_values($this->delayedPackets);
        }
    }

    /**
     * @param CloudPacket $packet
     * @param int $ticks delay in ticks (20 = 1s)
     * @param Closure|null $onSend function(ServerClient $client, CloudPacket $packet, bool $success): void {}
     * @return void
     */
    public function sendDelayedPacket(CloudPacket $packet, int $ticks, ?Closure $onSend = null): void {
        $this->delayedPackets[] = [$packet, HydraCloud::getInstance()->getTick() + $ticks, $onSend];
    }

    public function getDelayedPackets(): array {
        return $this->delayedPackets;
    }

    public function getAddress(): Address {
        return $this->address;
    }

    public function getServer(): ?CloudServer {
        return ServerClientCache::getInstance()->getServer($this);
    }
}