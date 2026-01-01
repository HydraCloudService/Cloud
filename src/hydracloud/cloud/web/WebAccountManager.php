<?php

namespace hydracloud\cloud\web;

use hydracloud\cloud\config\Config;
use hydracloud\cloud\config\impl\MainConfig;
use hydracloud\cloud\config\type\ConfigTypes;
use hydracloud\cloud\http\endpoint\EndpointRegistry;
use hydracloud\cloud\http\endpoint\impl\web\WebAccountCreateEndPoint;
use hydracloud\cloud\http\endpoint\impl\web\WebAccountGetEndPoint;
use hydracloud\cloud\http\endpoint\impl\web\WebAccountListEndPoint;
use hydracloud\cloud\http\endpoint\impl\web\WebAccountRemoveEndPoint;
use hydracloud\cloud\http\endpoint\impl\web\WebAccountUpdateEndPoint;
use hydracloud\cloud\util\SingletonTrait;

final class WebAccountManager {
    use SingletonTrait;

    /** @var array<WebAccount> */
    private array $accounts = [];
    private Config $accountsConfig;

    public function __construct() {
        self::setInstance($this);
        $this->accountsConfig = new Config(WEB_PATH . "accounts.json", ConfigTypes::JSON());
    }

    public function load(): void {
        if (!MainConfig::getInstance()->isWebEnabled()) return;
        foreach ($this->accountsConfig->getAll() as $data) {
            if (($account = WebAccount::fromArray($data)) !== null) {
                $this->accounts[$account->getName()] = $account;
            }
        }

        foreach ([new WebAccountCreateEndPoint(), new WebAccountRemoveEndPoint(), new WebAccountGetEndPoint(), new WebAccountUpdateEndPoint(), new WebAccountListEndPoint()] as $endPoint) EndpointRegistry::addEndPoint($endPoint);
    }

    public function create(WebAccount $account): void {
        if (!MainConfig::getInstance()->isWebEnabled()) return;
        $this->accounts[$account->getName()] = $account;
        $this->accountsConfig->set($account->getName(), $account->toArray());
        $this->accountsConfig->save();
    }

    public function update(WebAccount $account, ?string $password, ?WebAccountRoles $role): void {
        if (!MainConfig::getInstance()->isWebEnabled()) return;
        if ($password !== null) {
            $account->setPassword($password);
            $account->setInitialPassword(false);
        }

        if ($role !== null) $account->setRole($role);

        $this->accountsConfig->set($account->getName(), $account->toArray());
        $this->accountsConfig->save();
    }

    public function remove(WebAccount $account): void {
        if (!MainConfig::getInstance()->isWebEnabled()) return;

        if ($this->check($account->getName())) unset($this->accounts[$account->getName()]);

        $this->accountsConfig->remove($account->getName());
        $this->accountsConfig->save();
    }

    public function check(string $name): bool {
        return isset($this->accounts[$name]);
    }

    public function get(string $name): ?WebAccount {
        if (!MainConfig::getInstance()->isWebEnabled()) return null;
        return $this->accounts[$name] ?? null;
    }

    public function getAll(): array {
        if (!MainConfig::getInstance()->isWebEnabled()) return [];
        return $this->accounts;
    }
}