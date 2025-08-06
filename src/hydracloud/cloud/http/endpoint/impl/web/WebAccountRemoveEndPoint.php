<?php

namespace hydracloud\cloud\http\endpoint\impl\web;

use hydracloud\cloud\http\endpoint\EndPoint;
use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\util\Router;
use hydracloud\cloud\web\WebAccountManager;

final class WebAccountRemoveEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::DELETE, "/webaccount/remove/");
    }

    public function handleRequest(Request $request, Response $response): array {
        $name = $request->data()->queries()->get("name");

        if (($account = WebAccountManager::getInstance()->get($name)) === null) {
            return ["error" => "A web account with that name doesn't exists!"];
        }

        WebAccountManager::getInstance()->remove($account);
        return ["success" => "The web account has been successfully removed!"];
    }

    public function isBadRequest(Request $request): bool {
        return !$request->data()->queries()->has("name");
    }
}