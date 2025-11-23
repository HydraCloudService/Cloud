<?php

namespace hydracloud\cloud\http\endpoint\impl\web;

use hydracloud\cloud\http\endpoint\EndPoint;
use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\util\Router;
use hydracloud\cloud\web\WebAccountManager;
use hydracloud\cloud\web\WebAccountRoles;

final class WebAccountUpdateEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::PATCH, "/webaccount/update/");
    }

    public function handleRequest(Request $request, Response $response): array {
        $name = $request->data()->queries()->get("name");
        $action = strtolower($request->data()->queries()->get("action"));
        $value = $request->data()->queries()->get("value");

        if (($account = WebAccountManager::getInstance()->get($name)) === null) {
            return ["error" => "A web account with that name doesn't exists!"];
        }

        if (!in_array($action, ["role", "password"])) {
            return ["error" => "Please provide a valid action! (role, password)"];
        }

        if ($action == "password") {
            WebAccountManager::getInstance()->update($account, $value, null);
        } else {
            if (($role = WebAccountRoles::get($value)) !== null) {
                WebAccountManager::getInstance()->update($account, null, $role);
            } else return ["error" => "Please provide a valid role! (admin, default)"];
        }

        return ["success" => "The web account has been updated!"];
    }

    public function isBadRequest(Request $request): bool {
        if ($request->data()->queries()->has("name") && $request->data()->queries()->has("action") && $request->data()->queries()->has("value")) return false;
        return true;
    }
}