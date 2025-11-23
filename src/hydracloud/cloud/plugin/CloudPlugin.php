<?php

namespace hydracloud\cloud\plugin;

use hydracloud\cloud\scheduler\TaskScheduler;

abstract class CloudPlugin {

    public bool $enabled = false {
        get {
            return $this->enabled;
        }
        set {
            $this->enabled = $value;
        }
    }
    public TaskScheduler $scheduler {
        get {
            return $this->scheduler;
        }
    }

    public function __construct(private readonly CloudPluginDescription $description) {
        $this->scheduler = new TaskScheduler($this);
    }

    public function onLoad(): void {}

    public function onEnable(): void {}

    public function onDisable(): void {}

    public function getDescription(): CloudPluginDescription {
        return $this->description;
    }

    public function isDisabled(): bool {
        return !$this->enabled;
    }

}