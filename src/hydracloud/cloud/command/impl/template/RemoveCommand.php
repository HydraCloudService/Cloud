<?php

namespace hydracloud\cloud\command\impl\template;

use hydracloud\cloud\command\argument\def\TemplateArgument;
use hydracloud\cloud\command\Command;
use hydracloud\cloud\command\sender\ICommandSender;
use hydracloud\cloud\template\TemplateManager;

final class RemoveCommand extends Command {

    public function __construct() {
        parent::__construct("remove", "Remove a template");
        $this->addParameter(new TemplateArgument(
            "template",
            false,
            "The template was not found."
        ));
    }

    public function run(ICommandSender $sender, string $label, array $args): bool {
        TemplateManager::getInstance()->remove($args["template"]);
        return true;
    }
}