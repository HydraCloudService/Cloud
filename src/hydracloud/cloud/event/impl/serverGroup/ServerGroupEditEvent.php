<?php

namespace hydracloud\cloud\event\impl\serverGroup;

use hydracloud\cloud\group\ServerGroup;

class ServerGroupEditEvent extends ServerGroupEvent {

    public function __construct(
        ServerGroup $serverGroup,
        private readonly array $newTemplates
    ) {
        parent::__construct($serverGroup);
    }

    public function getNewTemplates(): array {
        return $this->newTemplates;
    }
}