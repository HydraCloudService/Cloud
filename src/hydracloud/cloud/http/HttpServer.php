<?php

namespace hydracloud\cloud\http;

use Closure;
use hydracloud\cloud\traffic\impl\HttpTrafficMonitor;
use hydracloud\cloud\traffic\TrafficMonitor;
use hydracloud\cloud\traffic\TrafficMonitorManager;
use pmmp\thread\ThreadSafeArray;
use hydracloud\cloud\config\impl\MainConfig;
use hydracloud\cloud\event\impl\http\HttpServerInitializeEvent;
use hydracloud\cloud\http\endpoint\EndpointRegistry;
use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\network\SocketClient;
use hydracloud\cloud\http\util\HttpUtils;
use hydracloud\cloud\http\util\Router;
use hydracloud\cloud\http\util\UnhandledHttpRequest;
use hydracloud\cloud\HydraCloud;
use hydracloud\cloud\terminal\log\CloudLogger;
use hydracloud\cloud\thread\Thread;
use hydracloud\cloud\util\net\Address;
use pocketmine\snooze\SleeperHandlerEntry;
use Socket;
use Throwable;

final class HttpServer extends Thread {

    public const REQUEST_READ_LENGTH = 8192;

    private bool $connected = false;

    protected ?Socket $socket = null;
    private ThreadSafeArray $buffer;
    private SleeperHandlerEntry $entry;
    private ?Closure $invalidUrlHandler = null;

    public function __construct(private readonly Address $address) {
        $this->buffer = new ThreadSafeArray();
    }

    public function onRun(): void {
        while ($this->connected) {
            if ($c = $this->accept()) {
                // thanks JS `fetch` and shoutout to my bro ChatGPT for fixing this shit
                $request = "";
                $contentLength = 0;
                $body = "";
                $rawHeaders = "";

                while (($chunk = $c->read(self::REQUEST_READ_LENGTH))) {
                    $request .= $chunk;
                    if (($pos = strpos($request, "\r\n\r\n")) !== false) {
                        $rawHeaders = substr($request, 0, $pos);
                        $bodyStart = substr($request, $pos + 4);

                        if (preg_match('/content-length:\s*(\d+)/i', $rawHeaders, $m)) {
                            $contentLength = (int)$m[1];
                        }

                        $body .= $bodyStart;
                        break;
                    }
                }

                $remaining = $contentLength - strlen($body);
                while ($remaining > 0 && ($chunk = $c->read($remaining))) {
                    $body .= $chunk;
                    $remaining -= strlen($chunk);
                }

                $fullRequest = $rawHeaders . "\r\n\r\n" . $body;

                $this->buffer[] = new UnhandledHttpRequest($fullRequest, $c);
                $this->entry->createNotifier()->wakeupSleeper();
            }
        }
    }

    public function default(Closure $closure): void {
        $this->invalidUrlHandler = $closure;
    }

    private function handleRequest(Address $address, string $request): string {
        $request = HttpUtils::parseRequest($address, $request);
        CloudLogger::get()->debug("Parsing HTTP request from " . $address . "...");
        if (!$request instanceof Request) {
            CloudLogger::get()->debug("HTTP request from " . $address . " could not be parsed, sending response with code 500...");
            return new Response(500);
        }

        TrafficMonitorManager::getInstance()->callHandlers(TrafficMonitorManager::TRAFFIC_HTTP, HttpTrafficMonitor::HTTP_MODE_REQUEST_IN, $request, $address);

        if (Router::getInstance()->isRegistered($request)) return Router::getInstance()->execute($request);
        CloudLogger::get()->debug("No route found for " . $request->data()->method() . " HTTP request from " . $request->data()->address() . ", sending 404 response...");
        $response = new Response(404);
        if ($this->invalidUrlHandler !== null) ($this->invalidUrlHandler)($request, $response);

        TrafficMonitorManager::getInstance()->callHandlers(TrafficMonitorManager::TRAFFIC_HTTP, HttpTrafficMonitor::HTTP_MODE_RESPONSE_OUT, $request, $response, $address);

        return $response;
    }

    public function init(): void {
        if (MainConfig::getInstance()->isHttpServerEnabled()) {
            new HttpServerInitializeEvent()->call();

            EndpointRegistry::registerDefaults();

            try {
                if ($this->bind()) {
                    CloudLogger::get()->success("Successfully bound the HTTP server to §b" . $this->address . "§r.");
                } else {
                    CloudLogger::get()->error("§cFailed to bind the HTTP server to §e" . $this->address . "§r.");
                    return;
                }
            } catch (Throwable $exception) {
                CloudLogger::get()->error("§cFailed to bind the HTTP server to §e" . $this->address . "§8: §e" . $exception->getMessage());
            }

            $this->entry = HydraCloud::getInstance()->getSleeperHandler()->addNotifier(function(): void {
                /** @var UnhandledHttpRequest $data */
                while (($data = $this->buffer->shift()) !== null) {
                    $client = $data->getClient();
                    $buf = $data->getBuffer();

                    CloudLogger::get()->debug("Received incoming HTTP request from " . $client->getAddress() . "...");

                    TrafficMonitorManager::getInstance()->pushBytes(TrafficMonitorManager::TRAFFIC_HTTP, $bytes = strlen($buf), TrafficMonitor::REGULAR_MODE_IN);
                    TrafficMonitorManager::getInstance()->callHandlers(
                        TrafficMonitorManager::TRAFFIC_HTTP,
                        TrafficMonitor::REGULAR_MODE_IN,
                        $buf, $bytes, $client->getAddress()
                    );

                    try {
                        $write = true;

                        if (MainConfig::getInstance()->isHttpServerOnlyLocal() && !$client->getAddress()->isLocal()) $write = false;
                        if (!in_array($client->getAddress(), MainConfig::getInstance()->getWhitelistedIps())) $write = false;

                        CloudLogger::get()->debug(!$write ? "Can't handle HTTP request from " . $client->getAddress() . "..." : "Handling HTTP request from " . $client->getAddress() . "...");
                        if ($write) $client->write($this->handleRequest($client->getAddress(), $buf));
                        $client->close();
                    } catch (Throwable $exception) {
                        CloudLogger::get()->warn("Received an invalid request from §b" . $client->getAddress() . "§r, ignoring...");
                        CloudLogger::get()->debug($buf);
                        CloudLogger::get()->exception($exception);
                    }
                }
            });

            $this->start();
        }
    }

    public function bind(): bool {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        if (!socket_bind($this->socket, $this->address->getAddress(), $this->address->getPort())) return false;
        $this->connected = true;
        return socket_listen($this->socket);
    }

    public function accept(): ?SocketClient {
        if (!$this->connected) return null;
        $c = @socket_accept($this->socket);
        if ($c instanceof Socket) {
            return SocketClient::fromSocket($c);
        }
        return null;
    }

    public function close(): void {
        if (!$this->connected) return;
        $this->connected = false;
        @socket_shutdown($this->socket);
        @socket_close($this->socket);
    }

    public function getAddress(): Address {
        return $this->address;
    }
}