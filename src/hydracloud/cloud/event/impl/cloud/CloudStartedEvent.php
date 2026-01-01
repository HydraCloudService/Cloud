<?php

namespace hydracloud\cloud\event\impl\cloud;

use hydracloud\cloud\event\Event;

class CloudStartedEvent extends Event {

    public function __construct(private readonly float $time) {}

    public function getTime(): float {
        return $this->time;
    }
}