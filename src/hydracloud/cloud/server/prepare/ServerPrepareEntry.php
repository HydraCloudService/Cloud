<?php

namespace hydracloud\cloud\server\prepare;

use hydracloud\cloud\group\ServerGroupManager;
use hydracloud\cloud\server\CloudServer;
use hydracloud\cloud\template\TemplateType;
use hydracloud\cloud\util\FileUtils;
use pmmp\thread\ThreadSafe;

class ServerPrepareEntry extends ThreadSafe {

    public function __construct(
        private readonly string $server,
        private readonly string $uuid,
        private readonly string $template,
        private readonly ?string $group,
        private readonly bool $static,
        private readonly string $templateType
    ) {}

    public function run(): void {
        $serverPath = (!$this->static) ? TEMP_PATH . $this->uuid . "/" : STATIC_PATH . $this->server . "/";
        $templatePath = TEMPLATES_PATH . $this->template . "/";

        if (file_exists($serverPath) && !$this->static) {
            FileUtils::removeDirectory($serverPath);
        }

        FileUtils::copyDirectory($templatePath, $serverPath);

        if ($this->templateType === TemplateType::SERVER()->getName()) FileUtils::copyDirectory(SERVER_PLUGINS_PATH, $serverPath . "plugins/"); else FileUtils::copyDirectory(PROXY_PLUGINS_PATH, $serverPath . "plugins/");
        if ($this->group !== null) FileUtils::copyDirectory(SERVER_GROUPS_PATH . $this->group . "/", $serverPath);

        if (file_exists($serverPath . "server.log") || file_exists($serverPath . "logs/server.log")) {
            unlink(match ($this->templateType) {
                TemplateType::PROXY()->getName() => $serverPath . "logs/server.log",
                default => $serverPath . "server.log"
            });
        }
    }

    public function getServer(): string {
        return $this->server;
    }

    public function getTemplate(): string {
        return $this->template;
    }

    public function getGroup(): ?string {
        return $this->group;
    }

    public function isStatic(): bool {
        return $this->static;
    }

    public function getTemplateType(): string {
        return $this->templateType;
    }

    public static function create(
        string $server,
        string $uuid,
        string $template,
        ?string $group,
        bool $static,
        string $templateType
    ): self {
        return new self($server, $uuid, $template, $group, $static, $templateType);
    }

    public static function fromServer(CloudServer $server): self {
        return self::create(
            $server->getName(),
            $server->getUuid(),
            $server->getTemplateName(),
            ServerGroupManager::getInstance()->get($server->getTemplate())?->getName(),
            $server->getTemplate()->getSettings()->isStatic(),
            $server->getTemplate()->getTemplateType()->getName()
        );
    }
}