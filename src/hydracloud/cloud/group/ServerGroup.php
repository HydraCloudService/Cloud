<?php

namespace hydracloud\cloud\group;

use hydracloud\cloud\server\CloudServer;
use hydracloud\cloud\template\Template;
use hydracloud\cloud\template\TemplateManager;
use hydracloud\cloud\util\FileUtils;
use hydracloud\cloud\util\Utils;
use JsonException;

final class ServerGroup {

    public function __construct(
        private readonly string $name,
        public array $templates {
            get {
                return $this->templates;
            }
        }
    ) {}

    public function copyDataTo(CloudServer $server): void {
        FileUtils::copyDirectory($this->getPath(), $server->getPath());
    }

    public function add(Template $template): void {
        if (!$this->is($template)) {
            $templates = $this->templates;
            $templates[] = $template->getName();

            $this->templates = $templates;
        }
    }

    public function remove(Template|string $template): void {
        $template = $template instanceof Template ? $template->getName() : $template;
        if ($this->is($template)) {
            unset($this->templates[array_search($template, $this->templates, true)]);
        }
        $this->templates = array_values($this->templates);
    }

    public function is(Template|string $template): bool {
        $template = $template instanceof Template ? $template->getName() : $template;
        return in_array($template, $this->templates, true);
    }

    /**
     * @throws JsonException
     */
    public function toArray(bool $mySql = false): array {
        return [
            "name" => $this->name,
            "templates" => ($mySql ? json_encode($this->templates, JSON_THROW_ON_ERROR) : $this->templates)
        ];
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPath(): string {
        return SERVER_GROUPS_PATH . $this->name . "/";
    }

    /**
     * @throws JsonException
     */
    public static function fromArray(array $data): ?self {
        if (!Utils::containKeys($data, "name", "templates")) {
            return null;
        }
        if (is_string($data["templates"])) {
            $data["templates"] = json_decode($data["templates"], true, 512, JSON_THROW_ON_ERROR);
        }

        $templates = [];
        foreach ((is_array($data["templates"]) ? $data["templates"] : []) as $name) {
            if (TemplateManager::getInstance()->check($name)) {
                $templates[] = $name;
            }
        }

        return new self(
            $data["name"],
            $templates
        );
    }
}