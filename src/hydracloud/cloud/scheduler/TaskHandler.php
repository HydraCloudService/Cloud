<?php

namespace hydracloud\cloud\scheduler;

use hydracloud\cloud\plugin\CloudPlugin;
use Random\RandomException;

final class TaskHandler {

    public int $id {
        get {
            return $this->id;
        }
    }
    public bool $cancelled = false {
        get {
            return $this->cancelled;
        }
    }
    private int $last = 0;

    /**
     * @throws RandomException
     */
    public function __construct(
        private readonly Task $task,
        private int $delay {
            get {
                return $this->delay;
            }
        },
        private readonly int $period,
        private readonly bool $repeat,
        private readonly CloudPlugin $owner
    ) {
        $this->id = random_int(PHP_INT_MIN, PHP_INT_MAX);
    }

    public function cancel(): void {
        if (!$this->cancelled) {
            $this->cancelled = true;
            $this->task->onCancel();
        }
    }

    public function onUpdate(int $tick): void {
        if ($this->delay > 0) {
            if (--$this->delay === 0) {
                $this->last = $tick;
                $this->task->onRun();

                if (!$this->isRepeat()) {
                    $this->cancel();
                }
            }
            return;
        }

        if ($tick >= ($this->last + $this->period)) {
            $this->last = $tick;
            $this->task->onRun();

            if (!$this->isRepeat()) {
                $this->cancel();
            }
        }
    }

    public function getTask(): Task {
        return $this->task;
    }

    public function getPeriod(): int {
        return $this->period;
    }

    public function isRepeat(): bool {
        return $this->repeat;
    }

    public function getOwner(): CloudPlugin {
        return $this->owner;
    }
}