<?php

namespace hydracloud\cloud\http\endpoint\impl\plugin;

use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\util\Router;
use hydracloud\cloud\plugin\CloudPluginManager;
use hydracloud\cloud\http\endpoint\EndPoint;

final class CloudPluginDisableEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::POST, "/plugin/disable/");
    }

    public function handleRequest(Request $request, Response $response): array {
        $plugin = CloudPluginManager::getInstance()->get($request->data()->queries()->get("plugin"));
        if ($plugin === null) {
            return ["error" => "Plugin wasn't found!"];
        }

        if ($plugin->isDisabled()) {
            return ["error" => "Plugin is already disabled!"];
        }

        CloudPluginManager::getInstance()->disable($plugin);
        return ["success" => "Plugin was disabled!"];
    }

    public function isBadRequest(Request $request): bool {
        return !$request->data()->queries()->has("plugin");
    }
}