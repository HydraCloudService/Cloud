<?php

namespace hydracloud\cloud\http\network;

use hydracloud\cloud\traffic\TrafficMonitor;
use hydracloud\cloud\traffic\TrafficMonitorManager;
use pmmp\thread\ThreadSafe;
use hydracloud\cloud\util\net\Address;
use Socket;

final class SocketClient extends ThreadSafe {

    protected ?Socket $socket = null;

    public function __construct(protected Address $address) {}

    public static function fromSocket(Socket $socket): ?SocketClient {
        if (!@socket_getpeername($socket, $peerAddress, $peerPort)) {
            @socket_close($socket);
            return null;
        }

        $c = new SocketClient(new Address($peerAddress, $peerPort));
        $c->socket = $socket;
        return $c;
    }

    public function read(int $len): false|string {
        return @socket_read($this->socket, $len);
    }

    public function write(string $buffer): bool {
        TrafficMonitorManager::getInstance()->pushBytes(TrafficMonitorManager::TRAFFIC_HTTP, strlen($buffer), TrafficMonitor::REGULAR_MODE_OUT);
        return (@socket_write($this->socket, $buffer) === strlen($buffer));
    }

    public function close(): void {
        @socket_shutdown($this->socket);
        @socket_close($this->socket);
    }

    public function getAddress(): Address {
        return $this->address;
    }
}