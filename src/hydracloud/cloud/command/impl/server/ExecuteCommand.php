<?php

namespace hydracloud\cloud\command\impl\server;

use hydracloud\cloud\command\argument\def\ServerArgument;
use hydracloud\cloud\command\argument\def\StringArgument;
use hydracloud\cloud\command\Command;
use hydracloud\cloud\command\sender\ICommandSender;
use hydracloud\cloud\network\packet\impl\type\CommandExecutionResult;
use hydracloud\cloud\server\CloudServer;
use hydracloud\cloud\server\CloudServerManager;

final class ExecuteCommand extends Command {

    public function __construct() {
        parent::__construct("execute", "Send a command to a server");

        $this->addParameter(new ServerArgument(
            "server",
            false,
            "The server was not found."
        ));

        $this->addParameter(new StringArgument(
            "command",
            false,
            true
        ));
    }

    public function run(ICommandSender $sender, string $label, array $args): bool {
        /** @var CloudServer $server */
        $server = $args["server"];
        $command = $args["command"];

        CloudServerManager::getInstance()->send($server, $command)->then(function(CommandExecutionResult $result) use($server, $sender): void {
            $server->getInternalCloudServerStorage()->remove("command_promise")->remove("command_promise_time");
            $sender->success("The command was successfully handled by the server, response:");
            if (empty($result->getMessages())) $sender->info("§c/");
            else foreach ($result->getMessages() as $message) $sender->info("§b" . $server->getName() . "§8: §r" . $message);
        })->failure(function() use($server, $sender): void {
            $server->getInternalCloudServerStorage()->remove("command_promise")->remove("command_promise_time");
            $sender->error("The command could not be handled by the server.");
        });
        return true;
    }
}