<?php

namespace hydracloud\cloud\http\endpoint\impl\module;

use hydracloud\cloud\cache\InGameModule;
use hydracloud\cloud\http\endpoint\EndPoint;
use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\util\Router;
use hydracloud\cloud\network\packet\impl\normal\ModuleSyncPacket;
use hydracloud\cloud\provider\CloudProvider;
use hydracloud\cloud\server\CloudServerManager;

final class ModuleEditEndPoint extends EndPoint {

    public function __construct() {
        parent::__construct(Router::PATCH, "/module/edit/");
    }

    public function handleRequest(Request $request, Response $response): array {
        $module = strtolower($request->data()->queries()->get("module"));
        $value = strtolower($request->data()->queries()->get("value")) == "true";

        if (in_array($module, ["sign", "signmodule", "cloudsigns"])) {
            CloudProvider::current()->setModuleState(InGameModule::SIGN_MODULE, $value);
            $this->sync();
            return ["success" => "The module state has been changed!"];
        } else if (in_array($module, ["npc", "npcmodule", "cloudnpcs"])) {
            CloudProvider::current()->setModuleState(InGameModule::NPC_MODULE, $value);
            $this->sync();
            return ["success" => "The module state has been changed!"];
        } else if (in_array($module, ["hub", "hubcommand", "hubcommandmodule"])) {
            CloudProvider::current()->setModuleState(InGameModule::HUB_COMMAND_MODULE, $value);
            $this->sync();
            return ["success" => "The module state has been changed!"];
        }

        return ["error" => "The module doesn't exists!"];
    }

    public function isBadRequest(Request $request): bool {
        if ($request->data()->queries()->has("module") && $request->data()->queries()->has("value")) return false;
        return true;
    }

    private function sync(): void {
        foreach (CloudServerManager::getInstance()->getAll() as $server) {
            if ($server->getTemplate()->getTemplateType()->isServer()) {
                ModuleSyncPacket::create()->sendPacket($server);
            }
        }
    }
}