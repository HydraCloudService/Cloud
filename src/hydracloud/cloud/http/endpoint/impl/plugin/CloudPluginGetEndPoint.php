<?php

namespace hydracloud\cloud\http\endpoint\impl\plugin;

use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\util\Router;
use hydracloud\cloud\plugin\CloudPluginManager;
use hydracloud\cloud\http\endpoint\EndPoint;

final class CloudPluginGetEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::GET, "/plugin/get");
    }

    public function handleRequest(Request $request, Response $response): array {
        $plugin = CloudPluginManager::getInstance()->get($request->data()->queries()->get("plugin"));
        if ($plugin === null) {
            return ["error" => "Plugin wasn't found!"];
        }

        return array_merge($plugin->getDescription()->toArray(), ["enabled" => $plugin->isEnabled()]);
    }

    public function isBadRequest(Request $request): bool {
        return !$request->data()->queries()->has("plugin");
    }
}