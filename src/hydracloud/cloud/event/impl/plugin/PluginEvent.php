<?php

namespace hydracloud\cloud\event\impl\plugin;

use hydracloud\cloud\event\Event;
use hydracloud\cloud\plugin\CloudPlugin;

abstract class PluginEvent extends Event {

    public function __construct(private readonly CloudPlugin $plugin) {}

    public function getPlugin(): CloudPlugin {
        return $this->plugin;
    }
}