<?php

namespace hydracloud\cloud\http\endpoint\impl\template;

use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\util\Router;
use hydracloud\cloud\http\endpoint\EndPoint;
use hydracloud\cloud\template\TemplateManager;

final class CloudTemplateRemoveEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::DELETE, "/template/remove/");
    }

    public function handleRequest(Request $request, Response $response): array {
        $name = $request->data()->queries()->get("name");
        $template = TemplateManager::getInstance()->get($name);

        if ($template === null) {
            return ["error" => "The template doesn't exists!"];
        }

        TemplateManager::getInstance()->remove($template);
        return ["success" => "The template was removed!"];
    }

    public function isBadRequest(Request $request): bool {
        return !$request->data()->queries()->has("name");
    }
}