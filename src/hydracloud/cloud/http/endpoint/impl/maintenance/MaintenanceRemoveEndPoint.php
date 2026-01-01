<?php

namespace hydracloud\cloud\http\endpoint\impl\maintenance;

use hydracloud\cloud\cache\MaintenanceList;
use hydracloud\cloud\http\endpoint\EndPoint;
use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\util\Router;
use hydracloud\cloud\provider\CloudProvider;

final class MaintenanceRemoveEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::DELETE, "/maintenance/remove/");
    }

    public function handleRequest(Request $request, Response $response): array {
        $player = $request->data()->queries()->get("player");

        if (!MaintenanceList::is($player)) {
            return ["error" => "The player is not on the maintenance list!"];
        }

        CloudProvider::current()->removeFromWhitelist($player);
        return ["success" => "The player was removed from the maintenance list!"];
    }

    public function isBadRequest(Request $request): bool {
        return !$request->data()->queries()->has("player");
    }
}