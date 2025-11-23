<?php

namespace hydracloud\cloud\template;

use hydracloud\cloud\cache\MaintenanceList;
use hydracloud\cloud\event\impl\template\TemplateCreateEvent;
use hydracloud\cloud\event\impl\template\TemplateEditEvent;
use hydracloud\cloud\event\impl\template\TemplateRemoveEvent;
use hydracloud\cloud\group\ServerGroupManager;
use hydracloud\cloud\network\packet\impl\normal\TemplateSyncPacket;
use hydracloud\cloud\player\CloudPlayer;
use hydracloud\cloud\player\CloudPlayerManager;
use hydracloud\cloud\provider\CloudProvider;
use hydracloud\cloud\server\CloudServerManager;
use hydracloud\cloud\server\util\ServerUtils;
use hydracloud\cloud\terminal\log\CloudLogger;
use hydracloud\cloud\util\FileUtils;
use hydracloud\cloud\util\SingletonTrait;
use hydracloud\cloud\util\tick\Tickable;
use RuntimeException;

final class TemplateManager implements Tickable {
    use SingletonTrait;

    /** @var array<Template> */
    private array $templates = [];

    public function __construct() {
        self::setInstance($this);
    }

    public function load(): void {
        CloudProvider::current()->getTemplates()
            ->then(function(array $templates): void {
                $this->templates = $templates;
                if (array_sum(array_map(static fn(Template $template) => $template->getSettings()->minServerCount, array_filter($this->templates, fn(Template $template) => $template->getSettings()->autoStart))) >= 9) {
                    CloudLogger::get()->warn("Your total active server count exceeds §b9§8, §rtherefore you should set §8'§bserverPrepareThreads§8' §rinside your §bconfig.json §rto at least §b1 §ror §b2 §rand restart the the §bcloud§r.");
                }
                ServerGroupManager::getInstance()->load();
            });
    }

    public function create(Template $template): void {
        $startTime = microtime(true);
        CloudProvider::current()->addTemplate($template);

        new TemplateCreateEvent($template)->call();

        CloudLogger::get()->debug("Creating directory: " . $template->getPath());
        if (!file_exists($template->getPath())) {
            if (!mkdir($concurrentDirectory = $template->getPath()) && !is_dir($concurrentDirectory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
        ServerUtils::makeProperties($template);
        $this->templates[$template->getName()] = $template;
        CloudLogger::get()->success("Successfully §acreated §rthe template §b" . $template->getName() . "§r. §8(§rTook §b" . number_format(microtime(true) - $startTime, 3) . "s§8)");
        TemplateSyncPacket::create($template, false)->broadcastPacket();
    }

    public function remove(Template $template): void {
        $startTime = microtime(true);
        CloudProvider::current()->removeTemplate($template);

        new TemplateRemoveEvent($template)->call();

        CloudServerManager::getInstance()->stop($template, true);

        if (file_exists($template->getPath())) {
            FileUtils::removeDirectory($template->getPath());
        }

        if (isset($this->templates[$template->getName()])) {
            unset($this->templates[$template->getName()]);
        }

        CloudLogger::get()->success("Successfully §cremoved §rthe template §b" . $template->getName() . "§r. §8(§rTook §b" . number_format(microtime(true) - $startTime, 3) . "s§8)");
        TemplateSyncPacket::create($template, true)->broadcastPacket();
    }

    public function edit(Template $template, ?bool $lobby, ?bool $maintenance, ?bool $static, ?int $maxPlayerCount, ?int $minServerCount, ?int $maxServerCount, ?float $startNewPercentage, ?bool $autoStart): void {
        $startTime = microtime(true);
        $template->getSettings()->lobby = ($lobby ?? $template->getSettings()->lobby);
        $template->getSettings()->maintenance = ($maintenance ?? $template->getSettings()->maintenance);
        $template->getSettings()->static = ($static ?? $template->getSettings()->static);
        $template->getSettings()->maxPlayerCount = ($maxPlayerCount ?? $template->getSettings()->maxPlayerCount);
        $template->getSettings()->minServerCount = ($minServerCount ?? $template->getSettings()->minServerCount);
        $template->getSettings()->maxServerCount = ($maxServerCount ?? $template->getSettings()->maxServerCount);
        $template->getSettings()->startNewPercentage = ($startNewPercentage ?? $template->getSettings()->startNewPercentage);
        $template->getSettings()->autoStart = ($autoStart ?? $template->getSettings()->autoStart);

        new TemplateEditEvent($template, $lobby, $maintenance, $static, $maxPlayerCount, $minServerCount, $maxServerCount, $startNewPercentage, $autoStart)->call();

        CloudProvider::current()->editTemplate($template, $template->toArray());

        CloudLogger::get()->success("Successfully §eedited §rthe template §b" . $template->getName() . "§r. §8(§rTook §b" . number_format(microtime(true) - $startTime, 3) . "s§8)");
        TemplateSyncPacket::create($template, false)->broadcastPacket();

        if ($template->toArray()["maintenance"]) {
            foreach (array_filter(CloudPlayerManager::getInstance()->getAll(), function(CloudPlayer $player) use($template): bool {
                return ($player->getCurrentServer() !== null && $player->getCurrentServer()->getTemplate() === $template) && !MaintenanceList::is($player->getName());
            }) as $player) {
                $player->kick("MAINTENANCE");
            }
        }
    }

    public function check(string $name): bool {
        return isset($this->templates[$name]);
    }

    public function tick(int $currentTick): void {
        if (!ServerGroupManager::getInstance()->loaded) {
            return;
        }

        foreach (self::getInstance()->getAll() as $template) {
            if ($template->getSettings()->autoStart && ($running = count(CloudServerManager::getInstance()->getAll($template))) < $template->getSettings()->maxServerCount) {
                CloudServerManager::getInstance()->start($template, ($template->getSettings()->minServerCount - $running));
            }

            if (($latest = CloudServerManager::getInstance()->getLatest($template)) !== null) {
                $players = $latest->getCloudPlayerCount();
                $requiredPercentage = $template->getSettings()->startNewPercentage;
                if ($requiredPercentage <= 0) {
                    continue;
                }

                $percentage = $players * 100 / $requiredPercentage;
                if ($percentage >= $requiredPercentage && CloudServerManager::getInstance()->canStartMore($template)) {
                    CloudServerManager::getInstance()->start($template);
                }
            }
        }
    }

    public function get(string $name): ?Template {
        return $this->templates[$name] ?? null;
    }

    public function getAll(): array {
        return $this->templates;
    }
}