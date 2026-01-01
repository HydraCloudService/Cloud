<?php

namespace hydracloud\cloud\http\endpoint\impl\server;

use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\util\Router;
use hydracloud\cloud\http\endpoint\EndPoint;
use hydracloud\cloud\server\CloudServerManager;

final class CloudServerSaveEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::POST, "/server/save/");
    }

    public function handleRequest(Request $request, Response $response): array {
        $name = $request->data()->queries()->get("server");
        $server = CloudServerManager::getInstance()->get($name);

        if ($server === null) {
            return ["error" => "The server doesn't exists!"];
        }

        CloudServerManager::getInstance()->save($server);
        return ["success" => "The cloud is successfully trying to save the given server!"];
    }

    public function isBadRequest(Request $request): bool {
        return !$request->data()->queries()->has("server");
    }
}