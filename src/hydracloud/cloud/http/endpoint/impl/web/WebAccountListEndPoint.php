<?php

namespace hydracloud\cloud\http\endpoint\impl\web;

use hydracloud\cloud\http\endpoint\EndPoint;
use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\util\Router;
use hydracloud\cloud\web\WebAccount;
use hydracloud\cloud\web\WebAccountManager;

final class WebAccountListEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::GET, "/webaccount/list/");
    }

    public function handleRequest(Request $request, Response $response): array {
        return array_map(fn(WebAccount $account) => $account->toArray(), array_values(WebAccountManager::getInstance()->getAll()));
    }

    public function isBadRequest(Request $request): bool {
        return false;
    }
}