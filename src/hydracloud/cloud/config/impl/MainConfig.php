<?php

namespace hydracloud\cloud\config\impl;

use hydracloud\cloud\config\Configuration;
use hydracloud\cloud\exception\ExceptionHandler;
use hydracloud\cloud\HydraCloud;
use hydracloud\cloud\provider\CloudProvider;
use hydracloud\cloud\server\util\ServerUtils;
use hydracloud\cloud\terminal\log\level\CloudLogLevel;
use hydracloud\cloud\util\SingletonTrait;
use hydracloud\cloud\util\Utils;

final class MainConfig extends Configuration {
    use SingletonTrait;

    /** @ignored */
    private string $generatedKey;
    public int $memoryLimit = 512 {
        get {
            return $this->memoryLimit;
        }
        set {
            $this->memoryLimit = $value;
            ini_set("memory_limit", ($value < 0 ? "-1" : $value . "M"));
        }
    }
    public string $language = "en_US" {
        get {
            return $this->language;
        }
        set {
            $this->language = $value;
        }
    }
    private string $provider = "json";
    public bool $debugMode = false {
        get {
            return $this->debugMode;
        }
        set {
            $this->debugMode = $value;
        }
    }
    public bool $updateChecks = true {
        get {
            return $this->updateChecks;
        }
        set {
            $this->updateChecks = $value;
        }
    }
    public bool $executeUpdates = true {
        get {
            return $this->executeUpdates;
        }
        set {
            $this->executeUpdates = $value;
        }
    }
    public string $startMethod = "tmux" {
        get {
            return $this->startMethod;
        }
        set {
            $this->startMethod = $value;
        }
    }
    private array $network = [
        "port" => 3656,
        "encryption" => true,
        "only-local" => true
    ];

    private array $httpServer = [
        "enabled" => true,
        "port" => 8000,
        "auth-key" => "123",
        "only-local" => true
    ];

    private array $mysqlSettings = [
        "address" => "127.0.0.1",
        "port" => 3306,
        "user" => "root",
        "password" => "pastepasswordinhere",
        "database" => "cloud"
    ];

    private array $web = [
        "enabled" => false
    ];

    private array $startCommands = [
        "server" => "%CLOUD_PATH%bin/php7/bin/php %SOFTWARE_PATH%PocketMine-MP.phar --no-wizard",
        "proxy" => "java -jar %SOFTWARE_PATH%Waterdog.jar"
    ] {
        get {
            return $this->startCommands;
        }
    }

    private array $serverTimeouts = [
        "server" => 15,
        "proxy" => 20
    ];

    private array $serverPortRanges = [
        "server" => [
            "start" => 40000,
            "end" => 65535
        ],
        "proxy" => [
            "start" => 19132,
            "end" => 20000
        ]
    ] {
        get {
            return $this->serverPortRanges;
        }
    }

    private int $serverPrepareThreads = 0; // By default, we are creating zero threads for that purpose to save some resources. Recommended to use if you've got more than 5 templates or 9 servers running at the same time

    public function __construct() {
        parent::__construct(STORAGE_PATH . "config.json", self::TYPE_JSON);
        self::setInstance($this);
        $this->httpServer["auth-key"] = ($this->generatedKey = Utils::generateString(10));

        $defaultHttp = $this->httpServer;
        $defaultNetwork = $this->network;
        $defaultWeb = $this->web;
        $defaultMySql = $this->mysqlSettings;
        $defaultStartCommands = $this->startCommands;
        $defaultServerTimeouts = $this->serverTimeouts;
        $defaultServerPortRanges = $this->serverPortRanges;

        ExceptionHandler::tryCatch(function (array $defaultHttp, array $defaultNetwork, array $defaultWeb, array $defaultMySql, array $defaultStartCommands, array $defaultServerTimeouts, array $defaultServerPortRanges): void {
            $this->load();
            foreach (array_keys($defaultHttp) as $key) {
                if (!isset($this->httpServer[$key])) {
                    $this->httpServer[$key] = $defaultHttp[$key];
                }
            }

            foreach (array_keys($defaultNetwork) as $key) {
                if (!isset($this->network[$key])) {
                    $this->network[$key] = $defaultNetwork[$key];
                }
            }

            foreach (array_keys($defaultWeb) as $key) {
                if (!isset($this->web[$key])) {
                    $this->web[$key] = $defaultWeb[$key];
                }
            }

            foreach (array_keys($defaultMySql) as $key) {
                if (!isset($this->mysqlSettings[$key])) {
                    $this->mysqlSettings[$key] = $defaultMySql[$key];
                }
            }

            $startCommands = $this->startCommands;
            foreach (array_keys($defaultStartCommands) as $key) {
                if (!isset($startCommands[$key])) {
                    $startCommands[$key] = $defaultStartCommands[$key];
                }
            }
            $this->startCommands = $startCommands;

            $serverTimeouts = $this->serverTimeouts;
            foreach (array_keys($defaultServerTimeouts) as $key) {
                if (!isset($this->serverTimeouts[$key])) {
                    $serverTimeouts[$key] = $defaultServerTimeouts[$key];
                }
            }
            $this->serverTimeouts = $serverTimeouts;

            if (!in_array(strtolower($this->startMethod), ["tmux", "screen"])) {
                $this->startMethod = "tmux";
            }

            if (!in_array(strtolower($this->provider), ["mysql", "json"])) {
                $this->provider = "json";
            }

            if ($this->serverPrepareThreads < 0) {
                $this->serverPrepareThreads = 0;
            } // If this is 0, server preparing remains inside the main-thread, therefore blocking it during the process
            else if ($this->serverPrepareThreads > 5) {
                $this->serverPrepareThreads = 5;
            }

            foreach ($this->serverPortRanges as $key => $data) {
                if (!is_array($data)) {
                    $serverPortRanges = $this->serverPortRanges;
                    $serverPortRanges[$key] = [];
                    $this->serverPortRanges = $serverPortRanges;
                }
                if (!isset($data["start"])) {
                    $this->serverPortRanges[$key]["start"] = random_int(40000, 41000);
                }
                if (!isset($data["end"])) {
                    $this->serverPortRanges[$key]["end"] = random_int(41000, 42000);
                }

                $start = $this->serverPortRanges[$key]["start"];
                $end = $this->serverPortRanges[$key]["end"];

                if ($start <= 0 || $end <= 0) {
                    HydraCloud::getInstance()?->notifyOnStart("Invalid port range §8(§b{$start}§8-§b{$end}§8) §rfor server type §8'§b" . $key . "§8'§r: §bStart §7or §bend §7can not be less or equal to §b0§r: §cResetting the entry, please review your config...", CloudLogLevel::WARN());
                    unset($this->serverPortRanges[$key]);
                    continue;
                }

                if ($start > $end) {
                    HydraCloud::getInstance()?->notifyOnStart("Invalid port range §8(§b{$start}§8-§b{$end}§8) §rfor server type §8'§b" . $key . "§8'§r: §bStart §ris §chigher §rthan §bend§r: §cResetting the entry, please review your config...", CloudLogLevel::WARN());
                    unset($this->serverPortRanges[$key]);
                    continue;
                }

                if (($start + 50) > $end) {
                    HydraCloud::getInstance()?->notifyOnStart("Invalid port range §8(§b{$start}§8-§b{$end}§8) §rfor server type §8'§b" . $key . "§8'§r: §bEnd §rneeds to be at least §b50 ports higher §rthan §bstart§r: §cResetting the entry, please review your config...", CloudLogLevel::WARN());
                    unset($this->serverPortRanges[$key]);
                }
            }

            foreach (array_keys($defaultServerPortRanges) as $key) {
                if (!isset($this->serverPortRanges[$key])) {
                    $serverPortRanges = $this->serverPortRanges;
                    $serverPortRanges[$key] = $defaultServerPortRanges[$key];
                    $this->serverPortRanges = $serverPortRanges;
                }
            }

            $this->save();
        }, "Failed to load main config", static fn() => HydraCloud::getInstance()?->shutdown(), $defaultHttp, $defaultNetwork, $defaultWeb, $defaultMySql, $defaultStartCommands, $defaultServerTimeouts, $defaultServerPortRanges);
    }

    public function setProvider(string $provider): void {
        $this->provider = $provider;
        CloudProvider::select();
    }

    public function setNetworkPort(int $port): void {
        $this->network["port"] = $port;
    }

    public function setNetworkEncryption(bool $value): void {
        $this->network["encryption"] = $value;
    }

    public function setNetworkOnlyLocal(bool $value): void {
        $this->network["onlyLocal"] = $value;
    }

    public function setHttpServerEnabled(bool $value): void {
        $this->httpServer["enabled"] = $value;
    }

    public function setHttpServerPort(int $value): void {
        $this->httpServer["port"] = $value;
    }

    public function setHttpServerOnlyLocal(bool $value): void {
        $this->httpServer["onlyLocal"] = $value;
    }

    public function setWebEnabled(bool $value): void {
        $this->web["enabled"] = $value;
    }

    public function setStartCommand(string $templateType, string $startCommand): void {
        $startCommands = $this->startCommands;
        $startCommands[strtolower($templateType)] = $startCommand;
        $this->startCommands = $startCommands;
    }

    public function setServerTimeouts(string $templateType, int $timeout): void {
        $this->serverTimeouts[strtolower($templateType)] = $timeout;
    }

    public function setServerPortRange(string $templateType, int $start, int $end): void {
        if ($end > 65535) {
            $end = 65535;
        }

        $serverPortRanges = $this->serverPortRanges;
        $serverPortRanges[strtolower($templateType)] = ["start" => $start, "end" => $end];
        $this->serverPortRanges = $serverPortRanges;
    }

    public function setServerPrepareThreads(int $serverPrepareThreads): void {
        if ($serverPrepareThreads < 0) {
            $serverPrepareThreads = 0;
        } else if ($serverPrepareThreads > 5) {
            $serverPrepareThreads = 5;
        }
        $this->serverPrepareThreads = $serverPrepareThreads;
    }

    public function getProvider(): string {
        return strtolower($this->provider);
    }

    public function getNetworkPort(): int {
        return $this->network["port"];
    }

    public function isNetworkEncryptionEnabled(): bool {
        return $this->network["encryption"];
    }

    public function isNetworkOnlyLocal(): bool {
        return $this->network["only-local"] ?? true;
    }

    public function isHttpServerEnabled(): bool {
        return $this->httpServer["enabled"];
    }

    public function getHttpServerPort(): int {
        return $this->httpServer["port"];
    }

    public function getHttpServerAuthKey(): string {
        return $this->httpServer["auth-key"];
    }

    public function isHttpServerOnlyLocal(): bool {
        return $this->httpServer["only-local"] ?? true;
    }

    public function getMySqlAddress(): string {
        return $this->mysqlSettings["address"];
    }

    public function getMySqlPort(): int {
        return $this->mysqlSettings["port"];
    }

    public function getMySqlUser(): string {
        return $this->mysqlSettings["user"];
    }

    public function getMySqlPassword(): string {
        return $this->mysqlSettings["password"];
    }

    public function getMySqlDatabase(): string {
        return $this->mysqlSettings["database"];
    }

    public function isWebEnabled(): bool {
        return $this->web["enabled"];
    }

    public function getStartCommand(string $software): string {
        return $this->startCommands[strtolower($software)] ?? "";
    }

    public function getServerTimeout(string $templateType): int {
        return $this->serverTimeouts[strtolower($templateType)] ?? ServerUtils::DEFAULT_TIMEOUT;
    }

    public function getServerTimeouts(): array {
        return $this->serverTimeouts;
    }

    public function getServerPortRange(string $templateType): ?array {
        return $this->serverPortRanges[strtolower($templateType)] ?? null;
    }

    public function getServerPrepareThreads(): int {
        return $this->serverPrepareThreads;
    }
}