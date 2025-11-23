<?php

namespace hydracloud\cloud\http\endpoint\impl\plugin;

use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\endpoint\EndPoint;
use hydracloud\cloud\http\util\Router;
use hydracloud\cloud\plugin\CloudPluginManager;

final class CloudPluginEnableEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::POST, "/plugin/enable/");
    }

    public function handleRequest(Request $request, Response $response): array {
        $plugin = CloudPluginManager::getInstance()->get($request->data()->queries()->get("plugin"));
        if ($plugin === null) {
            return ["error" => "Plugin wasn't found!"];
        }

        if ($plugin->isEnabled()) {
            return ["error" => "Plugin is already enabled!"];
        }

        CloudPluginManager::getInstance()->enable($plugin);
        return ["success" => "Plugin was enabled!"];
    }

    public function isBadRequest(Request $request): bool {
        return !$request->data()->queries()->has("plugin");
    }
}