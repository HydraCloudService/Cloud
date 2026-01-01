<?php

namespace hydracloud\cloud\http\endpoint\impl\web;

use hydracloud\cloud\http\endpoint\EndPoint;
use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\util\Router;
use hydracloud\cloud\util\Utils;
use hydracloud\cloud\web\WebAccount;
use hydracloud\cloud\web\WebAccountManager;
use hydracloud\cloud\web\WebAccountRoles;

final class WebAccountCreateEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::POST, "/webaccount/create/");
    }

    public function handleRequest(Request $request, Response $response): array {
        $name = $request->data()->queries()->get("name");
        $role = WebAccountRoles::get($request->data()->queries()->get("role", "default")) ?? WebAccountRoles::DEFAULT;

        if (WebAccountManager::getInstance()->check($name)) {
            return ["error" => "A web account with that name already exists!"];
        }

        WebAccountManager::getInstance()->create(new WebAccount(
            $name, password_hash($pw = Utils::generateString(6), PASSWORD_BCRYPT), true, $role
        ));

        return ["success" => "The web account was created!", "initial_password" => $pw];
    }

    public function isBadRequest(Request $request): bool {
        return !$request->data()->queries()->has("name");
    }
}