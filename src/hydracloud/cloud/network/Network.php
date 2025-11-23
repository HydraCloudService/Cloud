<?php

namespace hydracloud\cloud\network;

use Exception;
use pmmp\thread\ThreadSafeArray;
use hydracloud\cloud\config\impl\MainConfig;
use hydracloud\cloud\event\impl\network\NetworkBindEvent;
use hydracloud\cloud\event\impl\network\NetworkCloseEvent;
use hydracloud\cloud\event\impl\network\NetworkPacketReceiveEvent;
use hydracloud\cloud\event\impl\network\NetworkPacketSendEvent;
use hydracloud\cloud\network\packet\pool\PacketPool;
use hydracloud\cloud\HydraCloud;
use hydracloud\cloud\terminal\log\CloudLogger;
use hydracloud\cloud\thread\Thread;
use hydracloud\cloud\util\net\Address;
use hydracloud\cloud\util\SingletonTrait;
use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\client\ServerClientCache;
use hydracloud\cloud\network\packet\CloudPacket;
use hydracloud\cloud\network\packet\handler\PacketSerializer;
use hydracloud\cloud\network\packet\UnhandledPacketObject;
use pocketmine\snooze\SleeperHandlerEntry;
use Socket;

final class Network extends Thread {
    use SingletonTrait;

    private SleeperHandlerEntry $entry;
    private ThreadSafeArray $buffer;
    private Socket $socket;
    private bool $connected = false {
        get {
            return $this->connected;
        }
    }

    public function __construct(public Address $address {
        get {
            return $this->address;
        }
    }) {
        self::setInstance($this);
        PacketPool::init();
        $this->buffer = new ThreadSafeArray();
    }

    public function onRun(): void {
        while ($this->connected && $this->isRunning()) {
            if ($this->read($buffer, $address, $port) !== false) {
                $this->buffer[] = new UnhandledPacketObject($buffer, $address, $port);
                $this->entry->createNotifier()->wakeupSleeper();
            }
        }
    }

    public function init(): void {
        CloudLogger::get()->info("Trying to bind to §b" . $this->address . "§r...");
        if (!$this->bind($this->address)) {
            CloudLogger::get()->error("§cFailed to bind to §e" . $this->address . "§c!");
            HydraCloud::getInstance()?->shutdown();
            return;
        }

        CloudLogger::get()->success("Successfully bound to §b" . $this->address . "§r.");

        $this->entry = HydraCloud::getInstance()->sleeperHandler->addNotifier(function(): void {
            /** @var UnhandledPacketObject $object */
            while (($object = $this->buffer->shift()) !== null) {
                $buffer = $object->getBuffer();
                $address = new Address($object->getAddress(), $object->getPort());
                $client = ServerClientCache::getInstance()->getByAddress($address) ?? new ServerClient($address);
                $continue = true;
                if (MainConfig::getInstance()->isNetworkOnlyLocal() && !$address->isLocal()) {
                    $continue = false;
                }
                if ($continue) {
                    try {
                        if (($packet = PacketSerializer::decode($buffer)) !== null) {
                            new NetworkPacketReceiveEvent($packet, $client)->call();
                            $packet->handle($client);
                        } else {
                            CloudLogger::get()->warn("Received an unknown packet from §b" . $address . "§r, ignoring...")->debug("Packet buffer: " . (MainConfig::getInstance()->isNetworkEncryptionEnabled() ? base64_decode($buffer) : $buffer));
                        }
                    } catch (Exception $e) {
                        CloudLogger::get()->error("§cFailed to decode a packet!");
                        CloudLogger::get()->debug($buffer);
                        CloudLogger::get()->exception($e);
                    }
                } else {
                    CloudLogger::get()->warn("Received an external packet from §b" . $address . "§r, ignoring...")->debug("Packet buffer: " . $buffer);
                }
            }
        });
    }

    private function bind(Address $address): bool {
        if ($this->connected) {
            return false;
        }
        $this->address = $address;
        $this->socket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if(@socket_bind($this->socket, $address->getAddress(), $address->getPort()) === true) {
            $this->connected = true;
            new NetworkBindEvent($this->address)->call();
            socket_set_option($this->socket, SOL_SOCKET, SO_SNDBUF, 1024 * 1024 * 8);
            socket_set_option($this->socket, SOL_SOCKET, SO_RCVBUF, 1024 * 1024 * 8);
            socket_set_block($this->socket);
        } else {
            return false;
        }
        return true;
    }

    public function write(string $buffer, Address $dst): bool {
        if (!$this->connected) {
            return false;
        }
        return @socket_sendto($this->socket, $buffer, strlen($buffer), 0, $dst->getAddress(), $dst->getPort()) === strlen($buffer);
    }

    public function read(?string &$buffer, ?string &$address, ?int &$port): bool {
        if (!$this->connected) {
            return false;
        }
        return @socket_recvfrom($this->socket, $buffer, 65535, 0, $address, $port) !== false;
    }

    public function close(): void {
        if ($this->connected) {
            new NetworkCloseEvent()->call();
            $this->connected = false;
            $this->quit();
        }
    }

    public function sendPacket(CloudPacket $packet, ServerClient $client): bool {
        $buffer = PacketSerializer::encode($packet);
        $success = $this->write($buffer, $client->getAddress());
        new NetworkPacketSendEvent($packet, $client, $success)->call();
        return $success;
    }

    public function broadcastPacket(CloudPacket $packet, ServerClient... $excluded): void {
        foreach (ServerClientCache::getInstance()->getAll() as $client) {
            if (!in_array($client, $excluded)) {
                $this->sendPacket(clone $packet, $client);
            }
        }
    }

    public static function getInstance(): ?self {
        return self::$instance;
    }
}