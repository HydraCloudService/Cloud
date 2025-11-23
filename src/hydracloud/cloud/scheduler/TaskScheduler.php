<?php

namespace hydracloud\cloud\scheduler;

use JetBrains\PhpStorm\Pure;
use hydracloud\cloud\plugin\CloudPlugin;
use hydracloud\cloud\util\tick\Tickable;
use Random\RandomException;

final class TaskScheduler implements Tickable {

    /** @var array<TaskHandler> */
    private array $tasks = [] {
        get {
            return $this->tasks;
        }
    }

    public function __construct(private readonly CloudPlugin $owner) {}

    /**
     * @throws RandomException
     */
    private function scheduleTask(Task $task, int $delay, int $period, bool $repeat): void {
        $taskHandler = new TaskHandler($task, $delay, $period, $repeat, $this->owner);
        $task->setTaskHandler($taskHandler);

        $tasks = $this->tasks;
        $tasks[$taskHandler->id] = $taskHandler;

        $this->tasks = $tasks;
    }

    /**
     * @throws RandomException
     */
    public function scheduleDelayedTask(Task $task, int $delay): void {
        $this->scheduleTask($task, $delay, -1, false);
    }

    /**
     * @throws RandomException
     */
    public function scheduleRepeatingTask(Task $task, int $period): void {
        $this->scheduleTask($task, -1, $period, true);
    }

    /**
     * @throws RandomException
     */
    public function scheduleDelayedRepeatingTask(Task $task, int $delay, int $period): void {
        $this->scheduleTask($task, $delay, $period, true);
    }

    public function cancel(Task $task): void {
        if (isset($this->tasks[$task->getTaskHandler()->id])) {
            $task->getTaskHandler()->cancel();
            unset($this->tasks[$task->getTaskHandler()->id]);
        }
    }

    public function cancelAll(): void {
        foreach ($this->tasks as $task) {
            $task->cancel();
        }
        $this->tasks = [];
    }

    #[Pure] public function getTaskById(int $id): ?Task {
        foreach ($this->tasks as $task) {
            if ($task->id === $id) {
                return $task->getTask();
            }
        }
        return null;
    }

    public function tick(int $currentTick): void {
        foreach ($this->tasks as $id => $task) {
            if ($task->cancelled) {
                unset($this->tasks[$id]);
                continue;
            }
            $task->onUpdate($currentTick);
        }
    }

    public function getOwner(): CloudPlugin {
        return $this->owner;
    }
}