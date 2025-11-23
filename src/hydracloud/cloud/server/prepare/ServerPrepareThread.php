<?php

namespace hydracloud\cloud\server\prepare;

use hydracloud\cloud\thread\Thread;
use pmmp\thread\ThreadSafeArray;
use pocketmine\snooze\SleeperHandlerEntry;

class ServerPrepareThread extends Thread {

    public SleeperHandlerEntry $sleeperHandlerEntry {
        set(SleeperHandlerEntry $value) {
            $this->sleeperHandlerEntry = $value;
        }
    }
    /** @var ThreadSafeArray<ServerPrepareEntry> */
    public ThreadSafeArray $prepareQueue {
        get {
            return $this->prepareQueue;
        }
    }
    /** @var ThreadSafeArray<ServerPrepareEntry> */
    public ThreadSafeArray $finishedPreparations {
        get {
            return $this->finishedPreparations;
        }
    }

    public function __construct() {
        $this->prepareQueue = new ThreadSafeArray();
        $this->finishedPreparations = new ThreadSafeArray();
    }

    public function onRun(): void {
        while (true) {
            $this->synchronized(function (): void {
                if ($this->isRunning() &&
                    $this->prepareQueue->count() === 0 &&
                    $this->finishedPreparations->count() === 0) {
                    $this->wait();
                }
            });

            /** @var ServerPrepareEntry $entry */
            if (($entry = $this->prepareQueue->shift()) !== null) {
                $entry->run();
                $this->finishedPreparations[] = $entry;
                $this->sleeperHandlerEntry->createNotifier()->wakeupSleeper();
            }
        }
    }

    public function pushToQueue(ServerPrepareEntry $entry): void {
        $this->synchronized(function () use ($entry): void {
            $this->prepareQueue[] = $entry;
            $this->notify();
        });
    }

}