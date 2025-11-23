<?php

namespace hydracloud\cloud\http\endpoint\impl\template;

use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\util\Router;
use hydracloud\cloud\http\endpoint\EndPoint;
use hydracloud\cloud\template\Template;
use hydracloud\cloud\template\TemplateManager;

final class CloudTemplateListEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::GET, "/template/list/");
    }

    public function handleRequest(Request $request, Response $response): array {
        return array_values(array_map(fn(Template $template) => $template->getName(), TemplateManager::getInstance()->getAll()));
    }

    public function isBadRequest(Request $request): bool {
        return false;
    }
}