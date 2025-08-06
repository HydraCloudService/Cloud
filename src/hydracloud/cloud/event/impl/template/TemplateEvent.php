<?php

namespace hydracloud\cloud\event\impl\template;

use hydracloud\cloud\event\Event;
use hydracloud\cloud\template\Template;

abstract class TemplateEvent extends Event {

    public function __construct(private readonly Template $template) {}

    public function getTemplate(): Template {
        return $this->template;
    }
}