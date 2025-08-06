<?php

namespace hydracloud\cloud\http\endpoint\impl\cloud;

use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\util\Router;
use hydracloud\cloud\network\Network;
use hydracloud\cloud\player\CloudPlayer;
use hydracloud\cloud\player\CloudPlayerManager;
use hydracloud\cloud\plugin\CloudPlugin;
use hydracloud\cloud\plugin\CloudPluginManager;
use hydracloud\cloud\http\endpoint\EndPoint;
use hydracloud\cloud\server\CloudServer;
use hydracloud\cloud\server\CloudServerManager;
use hydracloud\cloud\template\Template;
use hydracloud\cloud\template\TemplateManager;
use hydracloud\cloud\util\VersionInfo;

final class CloudInfoEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::GET, "/cloud/info/");
    }

    public function handleRequest(Request $request, Response $response): array {
        $templates = array_map(fn(Template $template) => $template->getName(), TemplateManager::getInstance()->getAll());
        $runningServers = array_map(fn(CloudServer $cloudServer) => $cloudServer->getName(), CloudServerManager::getInstance()->getAll());
        $loadedPlugins = array_map(fn(CloudPlugin $plugin) => $plugin->getDescription()->getName(), CloudPluginManager::getInstance()->getAll());
        $enabledPlugins = array_map(fn(CloudPlugin $plugin) => $plugin->getDescription()->getName(), CloudPluginManager::getInstance()->getAll(true));
        $disabledPlugins = array_filter($loadedPlugins, fn(string $name) => !in_array($name, $enabledPlugins));
        $players = array_map(fn(CloudPlayer $player) => $player->getName(), CloudPlayerManager::getInstance()->getAll());

        return [
            "version" => VersionInfo::VERSION,
            "developer" => VersionInfo::DEVELOPERS,
            "templates" => array_values($templates),
            "runningServers" => array_values($runningServers),
            "players" => array_values($players),
            "loadedPlugins" => array_values($loadedPlugins),
            "enabledPlugins" => array_values($enabledPlugins),
            "disabledPlugins" => array_values($disabledPlugins),
            "network_address" => Network::getInstance()->getAddress()->__toString()
        ];
    }

    public function isBadRequest(Request $request): bool {
        return false;
    }
}