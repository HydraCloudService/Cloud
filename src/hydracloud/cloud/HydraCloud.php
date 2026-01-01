<?php

namespace hydracloud\cloud;

use hydracloud\cloud\server\prepare\ServerPreparator;
use hydracloud\cloud\terminal\log\level\CloudLogLevel;
use hydracloud\cloud\traffic\TrafficMonitorManager;
use hydracloud\cloud\util\misc\Queue;
use Phar;
use hydracloud\cloud\config\impl\MainConfig;
use hydracloud\cloud\event\EventManager;
use hydracloud\cloud\event\impl\cloud\CloudStartedEvent;
use hydracloud\cloud\exception\ExceptionHandler;
use hydracloud\cloud\http\HttpServer;
use hydracloud\cloud\library\LibraryManager;
use hydracloud\cloud\loader\ClassLoader;
use hydracloud\cloud\network\client\ServerClientCache;
use hydracloud\cloud\network\Network;
use hydracloud\cloud\plugin\CloudPluginManager;
use hydracloud\cloud\scheduler\AsyncPool;
use hydracloud\cloud\server\CloudServerManager;
use hydracloud\cloud\server\util\ServerUtils;
use hydracloud\cloud\setup\impl\ConfigSetup;
use hydracloud\cloud\setup\impl\TemplateSetup;
use hydracloud\cloud\software\SoftwareManager;
use hydracloud\cloud\template\Template;
use hydracloud\cloud\template\TemplateManager;
use hydracloud\cloud\terminal\log\CloudLogger;
use hydracloud\cloud\terminal\log\handler\ShutdownHandler;
use hydracloud\cloud\terminal\log\logger\LoggingCache;
use hydracloud\cloud\terminal\Terminal;
use hydracloud\cloud\thread\ThreadManager;
use hydracloud\cloud\update\UpdateChecker;
use hydracloud\cloud\util\net\Address;
use hydracloud\cloud\util\terminal\TerminalUtils;
use hydracloud\cloud\util\tick\TickableList;
use hydracloud\cloud\util\Utils;
use hydracloud\cloud\util\VersionInfo;
use hydracloud\cloud\web\WebAccountManager;
use pocketmine\snooze\SleeperHandler;
use Throwable;

final class HydraCloud {

    private static ?self $instance = null;

    private bool $running = true;
    private int $tick = 0;
    private float $startTime = 0;

    private Queue $startNotificationQueue;
    private SleeperHandler $sleeperHandler;
    private Terminal $terminal;
    private Network $network;
    private HttpServer $httpServer;
    private TrafficMonitorManager $trafficMonitorManager;

    public function __construct(
        private readonly ClassLoader $classLoader
    ) {
        self::$instance = $this;
        $this->startUp();
    }

    protected function startUp(): void {
        if (Utils::checkRunning($pid)) {
            CloudLogger::get()->error("Another instance of §bHydra§3Cloud §ris already running! §8(§rProcessId: §b" . $pid . "§8)");
            exit(1);
        }

        if (PHP_OS_FAMILY == "Windows") {
            CloudLogger::get()->error("You can't use §bHydra§3Cloud §ron Windows!");
            exit(1);
        }

        if (!ServerUtils::checkBinary()) {
            CloudLogger::get()->error("Please install the following php binary in " . CLOUD_PATH . ":");
            CloudLogger::get()->error("§bhttps://github.com/pmmp/PHP-Binaries/releases/latest");
            exit(1);
        }

        if (!ServerUtils::checkJava()) {
            CloudLogger::get()->error("Please install Java 17!");
            CloudLogger::get()->error("Your operating system: §b" . php_uname());
            exit(1);
        }

        LibraryManager::getInstance()->load();

        if (!ServerUtils::detectStartMethod()) {
            CloudLogger::get()->error("Please install one of the following software:");
            CloudLogger::get()->error("§btmux §8(§rapt-get install tmux§8)");
            CloudLogger::get()->error("§bScreen §8(§rapt-get install screen§9)");
            exit(1);
        }

        Utils::createLockFile();

        ExceptionHandler::set();
        ShutdownHandler::register();

        Utils::downloadPlugins();
        SoftwareManager::getInstance()->downloadAll();
        TerminalUtils::clear();

        $this->startNotificationQueue = Queue::fromType([]);
        $this->sleeperHandler = new SleeperHandler();
        $this->terminal = new Terminal();
        $this->terminal->start();

        CloudLogger::get()->emptyLine()->setUsePrefix(false);
        CloudLogger::get()->info("  §bHydra§3Cloud §8- §rA cloud system for pocketmine servers with proxy support §8- §b" . VersionInfo::VERSION . (VersionInfo::isBeta() ? "§c@BETA" : "") . " §8- §rdeveloped by §b" . implode("§8, §b", VersionInfo::DEVELOPERS));
        CloudLogger::get()->emptyLine()->setUsePrefix(true);

        if (FIRST_RUN) {
            new ConfigSetup()->completion(function(array $results): void {
                $this->start();
                if ($results["defaultLobbyTemplate"] ?? true) {
                    TemplateManager::getInstance()->create(Template::lobby("Lobby"));
                }

                if ($results["defaultProxyTemplate"] ?? true) {
                    TemplateManager::getInstance()->create(Template::proxy("Proxy"));
                }
            })->startSetup();
        } else $this->start();
        $this->tick();
    }

    public function start(): void {
        ini_set("memory_limit", ($memory = MainConfig::getInstance()->getMemoryLimit()) > 0 ? $memory . "M" : "-1");
        CloudLogger::get()->info("The §bCloud §ris §astarting§r...");
        $this->startTime = microtime(true);

        $networkAddress = (MainConfig::getInstance()->isNetworkOnlyLocal() ? "127.0.0.1" : "0.0.0.0");
        $this->network = new Network(new Address($networkAddress, MainConfig::getInstance()->getNetworkPort()));

        $httpAddress = (MainConfig::getInstance()->isHttpServerOnlyLocal() ? "127.0.0.1" : "0.0.0.0");
        $this->httpServer = new HttpServer(new Address($httpAddress, MainConfig::getInstance()->getHttpServerPort()));

        $this->trafficMonitorManager = new TrafficMonitorManager();

        ServerPreparator::getInstance()->init();
        TemplateManager::getInstance()->load();
        CloudPluginManager::getInstance()->loadAll();
        CloudPluginManager::getInstance()->enableAll();

        TickableList::add($this->trafficMonitorManager);
        TickableList::add(CloudPluginManager::getInstance());
        TickableList::add(AsyncPool::getInstance());
        TickableList::add(CloudServerManager::getInstance());
        TickableList::add(TemplateManager::getInstance());
        TickableList::add(ServerClientCache::getInstance());

        $this->network->init();

        if (MainConfig::getInstance()->isHttpServerEnabled()) $this->httpServer->init();
        if (MainConfig::getInstance()->isWebEnabled()) WebAccountManager::getInstance()->load();

        if (MainConfig::getInstance()->isUpdateChecks()) {
            CloudLogger::get()->info("Checking for §bupdates§r...");
            UpdateChecker::getInstance()->check();
        }

        while (($entry = $this->startNotificationQueue->next()) !== null) {
            CloudLogger::get()->send($entry[0], $entry[1], ...$entry[2]);
        }

        $startedTime = (microtime(true) - $this->startTime);
        new CloudStartedEvent($startedTime)->call();
        CloudLogger::get()->success("§bCloud §rhas been §astarted§r. §8(§rTook §b" . number_format($startedTime, 3) . "s§8)");

        if (count(TemplateManager::getInstance()->getAll()) == 0 && FIRST_RUN) {
            CloudLogger::get()->info("No templates found, starting the setup...");
            new TemplateSetup()->startSetup();
        }

        $this->network->start();
    }

    public function handleCrash(): void {
        if (!$this->running) return;
        $this->running = false;
        $this->shutdown();
        echo "--- Uptime: " . round($this->getUptime(), 2) . "s - HydraCloud has crashed, waiting 60s before completely killing the process. ---";
        sleep(60);
        @TerminalUtils::kill(getmypid());
        exit(1);
    }

    public function addStartNotification(string $logMessage, ?CloudLogLevel $logLevel = null, mixed... $params): self {
        $this->startNotificationQueue->add([$logLevel ?? CloudLogLevel::INFO(), $logMessage, $params]);
        return $this;
    }

    public function tick(): void {
        $start = microtime(true);
        while ($this->running) {
            $this->sleeperHandler->sleepUntil($start);
            usleep(50 * 1000);
            $this->tick++;
            TickableList::tick($this->tick);
        }
    }

    public function shutdown(): void {
        if (!$this->running) return;
        $this->running = false;
        try {
            TickableList::clear();
            EventManager::getInstance()->removeAll();
            CloudServerManager::getInstance()->stopAll(true);
            CloudPluginManager::getInstance()->disableAll();
            AsyncPool::getInstance()->shutdown();
            if (isset($this->network)) $this->network->close();
            if (isset($this->terminal)) $this->terminal->quit();
            if (isset($this->httpServer)) $this->httpServer->close();
            ServerPreparator::getInstance()->stop();
            ShutdownHandler::unregister();
            ThreadManager::getInstance()->stopAll();
            CloudLogger::close();
            LoggingCache::clear();
        } catch (Throwable $exception) {
            CloudLogger::get()->error("Cloud crashed while shutting down...");
            CloudLogger::get()->exception($exception);
        }
    }

    public function getUptime(): float {
        if ($this->startTime <= 0) return 0;
        return microtime(true) - $this->startTime;
    }

    public function getTrafficMonitorManager(): TrafficMonitorManager {
        return $this->trafficMonitorManager;
    }

    public function getHttpServer(): HttpServer {
        return $this->httpServer;
    }

    public function getNetwork(): Network {
        return $this->network;
    }

    public function getTerminal(): Terminal {
        return $this->terminal;
    }

    public function getStartNotificationQueue(): Queue {
        return $this->startNotificationQueue;
    }

    public function getSleeperHandler(): SleeperHandler {
        return $this->sleeperHandler;
    }

    public function getClassLoader(): ClassLoader {
        return $this->classLoader;
    }

    public function getStartTime(): float {
        return $this->startTime;
    }

    public function getTick(): int {
        return $this->tick;
    }

    public function isRunning(): bool {
        return $this->running;
    }

    public static function getInstance(): ?self {
        return self::$instance;
    }
}

if (Phar::running() !== "") {
    require Phar::running() . "/vendor/autoload.php";
} else {
    require __DIR__ . "/vendor/autoload.php";
}

require_once "loader/ClassLoader.php";
require_once "HydraCloud.php";

define("IS_PHAR", Phar::running() !== "");
define("SOURCE_PATH", __DIR__ . "/");

if (IS_PHAR) {
    define("CLOUD_PATH", str_replace("phar://", "", dirname(__DIR__, 4) . DIRECTORY_SEPARATOR));
} else {
    define("CLOUD_PATH", dirname(__DIR__, 3) . DIRECTORY_SEPARATOR);
}

define("STORAGE_PATH", CLOUD_PATH . "storage/");
define("CRASH_PATH", CLOUD_PATH . "storage/crashes/");
define("LIBRARY_PATH", STORAGE_PATH . "libraries/");
define("PLUGINS_PATH", STORAGE_PATH . "plugins/");
define("SERVER_PLUGINS_PATH", STORAGE_PATH . "plugins/server/");
define("PROXY_PLUGINS_PATH", STORAGE_PATH . "plugins/proxy/");
define("CLOUD_PLUGINS_PATH", STORAGE_PATH . "plugins/cloud/");
define("SOFTWARE_PATH", STORAGE_PATH . "software/");
define("IN_GAME_PATH", STORAGE_PATH . "inGame/");
define("WEB_PATH", STORAGE_PATH . "web/");
define("LOG_PATH", STORAGE_PATH . "cloud.log");
define("TEMP_PATH", CLOUD_PATH . "tmp/");
define("STATIC_PATH", CLOUD_PATH . "static/");
define("TEMPLATES_PATH", CLOUD_PATH . "templates/");
define("SERVER_GROUPS_PATH", CLOUD_PATH . "groups/");
define("FIRST_RUN", !file_exists(STORAGE_PATH . "config.json"));

if (!file_exists(STORAGE_PATH)) mkdir(STORAGE_PATH);
if (!file_exists(LIBRARY_PATH)) mkdir(LIBRARY_PATH);
if (!file_exists(CRASH_PATH)) mkdir(CRASH_PATH);
if (!file_exists(PLUGINS_PATH)) mkdir(PLUGINS_PATH);
if (!file_exists(SERVER_PLUGINS_PATH)) mkdir(SERVER_PLUGINS_PATH);
if (!file_exists(PROXY_PLUGINS_PATH)) mkdir(PROXY_PLUGINS_PATH);
if (!file_exists(CLOUD_PLUGINS_PATH)) mkdir(CLOUD_PLUGINS_PATH);
if (!file_exists(SOFTWARE_PATH)) mkdir(SOFTWARE_PATH);
if (!file_exists(IN_GAME_PATH)) mkdir(IN_GAME_PATH);
if (!file_exists(WEB_PATH)) mkdir(WEB_PATH);
if (!file_exists(LOG_PATH)) file_put_contents(LOG_PATH, "");
if (!file_exists(TEMPLATES_PATH)) mkdir(TEMPLATES_PATH);
if (!file_exists(SERVER_GROUPS_PATH)) mkdir(SERVER_GROUPS_PATH);
if (!file_exists(TEMP_PATH)) mkdir(TEMP_PATH);
if (!file_exists(STATIC_PATH)) mkdir(STATIC_PATH);

$classLoader = new ClassLoader();
$classLoader->init();

do {
    new HydraCloud($classLoader);
} while (false);

Utils::deleteLockFile();

@TerminalUtils::kill(getmypid());
exit(1);