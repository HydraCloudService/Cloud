<?php

namespace hydracloud\cloud\server\prepare;

use Closure;
use hydracloud\cloud\config\impl\MainConfig;
use hydracloud\cloud\HydraCloud;
use hydracloud\cloud\terminal\log\CloudLogger;
use hydracloud\cloud\util\SingletonTrait;

class ServerPreparator {
    use SingletonTrait;

    private array $completionHandlers = [];
    /** @var array<ServerPrepareThread> */
    private array $threads = [];

    public function init(): void {
        self::setInstance($this);

        CloudLogger::get()->debug("Starting threads to prepare starting servers... (" . ($count = MainConfig::getInstance()->getServerPrepareThreads()) . ")");
        if ($this->isAsync()) {
            for ($i = 0; $i < $count; $i++) {
                $thread = new ServerPrepareThread();

                $sleeperHandlerEntry = HydraCloud::getInstance()->getSleeperHandler()->addNotifier(
                    function () use ($thread, $i): void {
                        /** @var ServerPrepareEntry $entry */
                        while (($entry = $thread->getFinishedPreparations()->shift()) !== null) {
                            $id = spl_object_id($entry);
                            [$completionHandler] = $this->completionHandlers[$id];
                            if ($completionHandler !== null) ($completionHandler)();
                            unset($this->completionHandlers[$id]);
                        }
                    }
                );

                $thread->setSleeperHandlerEntry($sleeperHandlerEntry);
                $thread->start();
                $this->threads[] = $thread;
            }
        }
    }

    public function stop(): void {
        foreach ($this->threads as $thread) {
            $thread->quit();
        }
    }

    public function submitEntry(ServerPrepareEntry $entry, ?Closure $completionHandler): void {
        CloudLogger::get()->debug("Preparing server (" . $entry->getServer() . "): §b" . ($this->isAsync() ? "async" : "sync"));
        if (!$this->isAsync()) {
            $entry->run();
            if ($completionHandler !== null) ($completionHandler)();
            return;
        }

        $this->completionHandlers[spl_object_id($entry)] = [$completionHandler, $entry];
        $this->getLeastBusyThread()->pushToQueue($entry);
    }

    protected function getLeastBusyThread(): ServerPrepareThread {
        $threads = $this->threads;
        usort($threads, static fn(ServerPrepareThread $a, ServerPrepareThread $b) => $a->getPrepareQueue()->count() <=> $b->getPrepareQueue()->count());
        return $threads[0];
    }

    public function isAsync(): bool {
        return MainConfig::getInstance()->getServerPrepareThreads() > 0;
    }
}