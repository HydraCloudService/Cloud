<?php

namespace hydracloud\cloud\group;

use hydracloud\cloud\server\CloudServer;
use hydracloud\cloud\template\Template;
use hydracloud\cloud\template\TemplateManager;
use hydracloud\cloud\util\FileUtils;
use hydracloud\cloud\util\Utils;

final class ServerGroup {

    public function __construct(
        private readonly string $name,
        private array $templates
    ) {}

    public function copyDataTo(CloudServer $server): void {
        FileUtils::copyDirectory($this->getPath(), $server->getPath());
    }

    public function add(Template $template): void {
        if (!$this->is($template)) $this->templates[] = $template->getName();
    }

    public function remove(Template|string $template): void {
        $template = $template instanceof Template ? $template->getName() : $template;
        if ($this->is($template)) unset($this->templates[array_search($template, $this->templates)]);
        $this->templates = array_values($this->templates);
    }

    public function is(Template|string $template): bool {
        $template = $template instanceof Template ? $template->getName() : $template;
        return in_array($template, $this->templates);
    }

    public function toArray(bool $mySql = false): array {
        return [
            "name" => $this->name,
            "templates" => ($mySql ? json_encode($this->templates) : $this->templates)
        ];
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPath(): string {
        return SERVER_GROUPS_PATH . $this->name . "/";
    }

    public function getTemplates(): array {
        return $this->templates;
    }

    public static function fromArray(array $data): ?self {
        if (!Utils::containKeys($data, "name", "templates")) return null;
        if (is_string($data["templates"])) $data["templates"] = json_decode($data["templates"], true);

        $templates = [];
        foreach ((is_array($data["templates"]) ? $data["templates"] : []) as $name) {
            if (TemplateManager::getInstance()->check($name)) $templates[] = $name;
        }

        return new self(
            $data["name"],
            $templates
        );
    }
}