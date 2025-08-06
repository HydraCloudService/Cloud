<?php

namespace hydracloud\cloud\http\endpoint\impl\maintenance;

use hydracloud\cloud\cache\MaintenanceList;
use hydracloud\cloud\http\endpoint\EndPoint;
use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\util\Router;

final class MaintenanceGetEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::GET, "/maintenance/get/");
    }

    public function handleRequest(Request $request, Response $response): array {
        $player = $request->data()->queries()->get("player");
        return ["player" => $player, "status" => MaintenanceList::is($player)];
    }

    public function isBadRequest(Request $request): bool {
        return !$request->data()->queries()->has("player");
    }
}