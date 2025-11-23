<?php

namespace hydracloud\cloud\http\endpoint\impl\server;

use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\util\Router;
use hydracloud\cloud\http\endpoint\EndPoint;
use hydracloud\cloud\server\CloudServer;
use hydracloud\cloud\server\CloudServerManager;
use hydracloud\cloud\template\TemplateManager;

final class CloudServerListEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::GET, "/server/list/");
    }

    public function handleRequest(Request $request, Response $response): array {
        $template = $request->data()->queries()->get("template");

        if ($template === null) {
            return array_values(array_map(fn(CloudServer $cloudServer) => $cloudServer->getName(), CloudServerManager::getInstance()->getAll()));
        } else {
            if (($template = TemplateManager::getInstance()->get($template)) !== null) {
                return array_values(array_map(fn(CloudServer $cloudServer) => $cloudServer->getName(), CloudServerManager::getInstance()->getAll($template)));
            } else {
                return ["error" => "The template doesn't exists!"];
            }
        }
    }

    public function isBadRequest(Request $request): bool {
        return false;
    }
}