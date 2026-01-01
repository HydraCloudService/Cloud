<?php

namespace hydracloud\cloud\command\argument\def;

use hydracloud\cloud\command\argument\exception\ArgumentParseException;
use hydracloud\cloud\command\argument\CommandArgument;
use hydracloud\cloud\template\Template;
use hydracloud\cloud\template\TemplateManager;

final readonly class TemplateArgument extends CommandArgument {

    public function parseValue(string $input): Template {
        if (($template = TemplateManager::getInstance()->get($input)) !== null) return $template;
        throw new ArgumentParseException();
    }

    public function getType(): string {
        return "template";
    }
}