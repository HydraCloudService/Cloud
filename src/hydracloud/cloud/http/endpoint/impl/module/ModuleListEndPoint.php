<?php

namespace hydracloud\cloud\http\endpoint\impl\module;

use hydracloud\cloud\cache\InGameModule;
use hydracloud\cloud\http\endpoint\EndPoint;
use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\util\Router;

final class ModuleListEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::GET, "/module/list/");
    }

    public function handleRequest(Request $request, Response $response): array {
        return InGameModule::getAll();
    }

    public function isBadRequest(Request $request): bool {
        return false;
    }
}