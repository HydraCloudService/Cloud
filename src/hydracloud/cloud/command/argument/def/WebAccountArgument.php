<?php

namespace hydracloud\cloud\command\argument\def;

use hydracloud\cloud\command\argument\CommandArgument;
use hydracloud\cloud\command\argument\exception\ArgumentParseException;
use hydracloud\cloud\web\WebAccount;
use hydracloud\cloud\web\WebAccountManager;

final readonly class WebAccountArgument extends CommandArgument {

    public function parseValue(string $input): WebAccount {
        if (($account = WebAccountManager::getInstance()->get($input)) !== null) return $account;
        throw new ArgumentParseException();
    }

    public function getType(): string {
        return "web_account";
    }
}