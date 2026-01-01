<?php

namespace hydracloud\cloud\command\impl\template;

use hydracloud\cloud\command\argument\def\StringArgument;
use hydracloud\cloud\command\argument\def\StringEnumArgument;
use hydracloud\cloud\command\Command;
use hydracloud\cloud\command\sender\ICommandSender;
use hydracloud\cloud\setup\impl\TemplateSetup;
use hydracloud\cloud\template\Template;
use hydracloud\cloud\template\TemplateManager;
use hydracloud\cloud\template\TemplateSettings;
use hydracloud\cloud\template\TemplateType;

final class CreateCommand extends Command {

    public function __construct() {
        parent::__construct("create", "Create a template");
        $this->addParameter(new StringArgument(
            "name",
            true
        ));

        $this->addParameter(new StringEnumArgument(
            "type",
            ["server", "proxy"],
            false,
            true
        ));
    }

    public function run(ICommandSender $sender, string $label, array $args): bool {
        $name = $args["name"] ?? null;
        if ($name === null) {
            new TemplateSetup()->startSetup();
            return true;
        } else {
            if (!TemplateManager::getInstance()->check($name)) {
                $templateType = TemplateType::SERVER();
                if (isset($args["type"])) $templateType = TemplateType::get($args["type"]) ?? TemplateType::SERVER();

                TemplateManager::getInstance()->create(Template::create($name, TemplateSettings::create(false, true, false, 20, 0, 2, 100, false), $templateType));
            } else $sender->error("The template already exists!");
        }
        return true;
    }
}