<?php

namespace hydracloud\cloud\command;

use hydracloud\cloud\command\impl\ConfigureCommand;
use hydracloud\cloud\command\impl\DebugCommand;
use hydracloud\cloud\command\impl\ExitCommand;
use hydracloud\cloud\command\impl\group\GroupCommand;
use hydracloud\cloud\command\impl\HelpCommand;
use hydracloud\cloud\command\impl\ListCommand;
use hydracloud\cloud\command\impl\player\KickCommand;
use hydracloud\cloud\command\impl\plugin\DisableCommand;
use hydracloud\cloud\command\impl\plugin\EnableCommand;
use hydracloud\cloud\command\impl\plugin\PluginsCommand;
use hydracloud\cloud\command\impl\server\ExecuteCommand;
use hydracloud\cloud\command\impl\server\SaveCommand;
use hydracloud\cloud\command\impl\server\StartCommand;
use hydracloud\cloud\command\impl\server\StopCommand;
use hydracloud\cloud\command\impl\StatusCommand;
use hydracloud\cloud\command\impl\template\CreateCommand;
use hydracloud\cloud\command\impl\template\EditCommand;
use hydracloud\cloud\command\impl\template\MaintenanceCommand;
use hydracloud\cloud\command\impl\template\RemoveCommand;
use hydracloud\cloud\command\impl\VersionCommand;
use hydracloud\cloud\command\impl\web\WebAccountCommand;
use hydracloud\cloud\command\sender\ICommandSender;
use hydracloud\cloud\util\SingletonTrait;

final class CommandManager {
    use SingletonTrait;

    /** @var array<Command> */
    private array $commands = [];

    public function __construct() {
        self::setInstance($this);
        $this->register(new ExitCommand());
        $this->register(new HelpCommand());
        $this->register(new DebugCommand());
        $this->register(new ListCommand());
        $this->register(new VersionCommand());
        $this->register(new ConfigureCommand());
        $this->register(new StatusCommand());

        $this->register(new StartCommand());
        $this->register(new StopCommand());
        $this->register(new ExecuteCommand());
        $this->register(new SaveCommand());

        $this->register(new CreateCommand());
        $this->register(new EditCommand());
        $this->register(new RemoveCommand());
        $this->register(new MaintenanceCommand());

        $this->register(new KickCommand());

        $this->register(new EnableCommand());
        $this->register(new DisableCommand());
        $this->register(new PluginsCommand());

        $this->register(new WebAccountCommand());

        $this->register(new GroupCommand());
    }

    public function handleInput(ICommandSender $sender, string $input): bool {
        $args = explode(" ", $input);
        $name = array_shift($args);
        if (($command = $this->get($name)) === null) return false;

        $command->handle($sender, $name, $args);
        return true;
    }

    public function register(Command $command): void {
        $this->commands[$command->getName()] = $command;
    }

    public function remove(Command|string $command): void {
        $command = $command instanceof Command ? $command->getName() : $command;
        if (isset($this->commands[$command])) unset($this->commands[$command]);
    }

    public function get(string $name): ?Command {
        return $this->commands[$name] ?? null;
    }

    public function getAll(): array {
        return $this->commands;
    }
}