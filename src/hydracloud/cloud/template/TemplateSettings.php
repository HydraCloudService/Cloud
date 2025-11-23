<?php

namespace hydracloud\cloud\template;

use hydracloud\cloud\util\Utils;

final class TemplateSettings {

    public function __construct(
        public bool   $lobby {
            get {
                return $this->lobby;
            }
            set {
                $this->lobby = $value;
            }
        },
        public bool   $maintenance {
            get {
                return $this->maintenance;
            }
            set {
                $this->maintenance = $value;
            }
        },
        public bool   $static {
            get {
                return $this->static;
            }
            set {
                $this->static = $value;
            }
        },
        public int    $maxPlayerCount {
            get {
                return $this->maxPlayerCount;
            }
            set {
                $this->maxPlayerCount = $value;
            }
        },
        public int   $minServerCount {
            get {
                return $this->minServerCount;
            }
            set {
                $this->minServerCount = $value;
            }
        },
        public int   $maxServerCount {
            get {
                return $this->maxServerCount;
            }
            set {
                $this->maxServerCount = $value;
            }
        },
        public float $startNewPercentage {
            get {
                return $this->startNewPercentage;
            }
            set {
                $this->startNewPercentage = $value;
            }
        },
        public bool $autoStart {
            get {
                return $this->autoStart;
            }
            set {
                $this->autoStart = $value;
            }
        }
    ) {}

    public function toArray(): array {
        return [
            "lobby" => $this->lobby,
            "maintenance" => $this->maintenance,
            "static" => $this->static,
            "maxPlayerCount" => $this->maxPlayerCount,
            "minServerCount" => $this->minServerCount,
            "maxServerCount" => $this->maxServerCount,
            "startNewPercentage" => $this->startNewPercentage,
            "autoStart" => $this->autoStart
        ];
    }

    public static function create(bool $lobby, bool $maintenance, bool $static, int $maxPlayerCount, int $minServerCount, int $maxServerCount, float $startNewPercentage, bool $autoStart): self {
        return new TemplateSettings($lobby, $maintenance, $static, $maxPlayerCount, $minServerCount, $maxServerCount, $startNewPercentage, $autoStart);
    }

    public static function fromArray(array $data): ?self {
        if (!Utils::containKeys($data, "lobby", "maintenance", "maxPlayerCount", "minServerCount", "maxServerCount", "autoStart")) {
            return null;
        }

        return self::create(...$data);
    }
}