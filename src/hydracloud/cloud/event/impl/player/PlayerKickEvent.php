<?php

namespace hydracloud\cloud\event\impl\player;

use hydracloud\cloud\event\Cancelable;
use hydracloud\cloud\event\CancelableTrait;
use hydracloud\cloud\player\CloudPlayer;

class PlayerKickEvent extends PlayerEvent implements Cancelable {
    use CancelableTrait;

    public function __construct(
        CloudPlayer $player,
        private readonly string $reason
    ) {
        parent::__construct($player);
    }

    public function getReason(): string {
        return $this->reason;
    }
}