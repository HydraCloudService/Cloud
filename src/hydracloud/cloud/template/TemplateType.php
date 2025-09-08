<?php

namespace hydracloud\cloud\template;

use hydracloud\cloud\config\impl\MainConfig;
use hydracloud\cloud\software\Software;
use hydracloud\cloud\software\SoftwareManager;
use hydracloud\cloud\util\enum\EnumTrait;

/**
 * @method static TemplateType SERVER()
 * @method static TemplateType PROXY()
 */
final class TemplateType {
    use EnumTrait;

    protected static function init(): void {
        self::register("server", new TemplateType("SERVER", SoftwareManager::getInstance()->get("PocketMine-MP")));
        self::register("proxy", new TemplateType("PROXY", SoftwareManager::getInstance()->get("WaterdogPE")));
    }

    public static function get(string $name): ?TemplateType {
        self::check();
        return self::$members[strtoupper($name)] ?? null;
    }

    /** @return array<TemplateType> */
    public static function getAll(): array {
        self::check();
        return self::$members;
    }

    public function __construct(
        private readonly string $name,
        private readonly Software $software
    ) {}

    public function __toString(): string {
        return $this->name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getServerTimeout(): int {
        return MainConfig::getInstance()->getServerTimeout($this->name);
    }

    public function getServerPortRange(): array {
        return MainConfig::getInstance()->getServerPortRange($this->name);
    }

    public function getSoftware(): Software {
        return $this->software;
    }

    public function isServer(): bool {
        return $this === self::SERVER();
    }

    public function isProxy(): bool {
        return $this === self::PROXY();
    }
}