<?php

namespace hydracloud\cloud\terminal;

use pmmp\thread\ThreadSafeArray;
use hydracloud\cloud\command\CommandManager;
use hydracloud\cloud\command\sender\ConsoleCommandSender;
use hydracloud\cloud\HydraCloud;
use hydracloud\cloud\setup\Setup;
use hydracloud\cloud\terminal\log\CloudLogger;
use hydracloud\cloud\thread\Thread;
use pocketmine\snooze\SleeperHandlerEntry;
use Throwable;

final class Terminal extends Thread {

    private ThreadSafeArray $buffer;
    private SleeperHandlerEntry $entry;

    public function __construct() {
        $this->buffer = new ThreadSafeArray();

        $this->entry = HydraCloud::getInstance()->getSleeperHandler()->addNotifier(function (): void {
            while (($line = $this->buffer->shift()) !== null) {
                try {
                    if (($setup = Setup::getCurrentSetup()) !== null) {
                        $setup->handleInput($line);
                    } else {
                        if (trim($line) == "") return;
                        if (!CommandManager::getInstance()->handleInput(new ConsoleCommandSender(), $line)) {
                            CloudLogger::get()->error("This §bcommand §rdoesn't exists!");
                        }
                    }
                } catch (Throwable $throwable) {
                    CloudLogger::get()->exception($throwable);
                }
            }
        });
    }

    public function onRun(): void {
        $input = fopen("php://stdin", "r");
        while ($this->isRunning()) {
            $this->buffer[] = trim(fgets($input));
            $this->entry->createNotifier()->wakeupSleeper();
        }

        fclose($input);
    }
}