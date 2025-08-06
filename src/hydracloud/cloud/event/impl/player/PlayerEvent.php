<?php

namespace hydracloud\cloud\event\impl\player;

use hydracloud\cloud\event\Event;
use hydracloud\cloud\player\CloudPlayer;

abstract class PlayerEvent extends Event {

    public function __construct(private readonly CloudPlayer $player) {}

    public function getPlayer(): CloudPlayer {
        return $this->player;
    }
}