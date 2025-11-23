<?php

namespace hydracloud\cloud\http\endpoint;

use hydracloud\cloud\http\endpoint\impl\cloud\CloudInfoEndPoint;
use hydracloud\cloud\http\endpoint\impl\maintenance\MaintenanceAddEndPoint;
use hydracloud\cloud\http\endpoint\impl\maintenance\MaintenanceGetEndPoint;
use hydracloud\cloud\http\endpoint\impl\maintenance\MaintenanceListEndPoint;
use hydracloud\cloud\http\endpoint\impl\maintenance\MaintenanceRemoveEndPoint;
use hydracloud\cloud\http\endpoint\impl\module\ModuleEditEndPoint;
use hydracloud\cloud\http\endpoint\impl\module\ModuleGetEndPoint;
use hydracloud\cloud\http\endpoint\impl\module\ModuleListEndPoint;
use hydracloud\cloud\http\endpoint\impl\player\CloudPlayerGetEndPoint;
use hydracloud\cloud\http\endpoint\impl\player\CloudPlayerKickEndPoint;
use hydracloud\cloud\http\endpoint\impl\player\CloudPlayerListEndPoint;
use hydracloud\cloud\http\endpoint\impl\player\CloudPlayerTextEndPoint;
use hydracloud\cloud\http\endpoint\impl\plugin\CloudPluginDisableEndPoint;
use hydracloud\cloud\http\endpoint\impl\plugin\CloudPluginEnableEndPoint;
use hydracloud\cloud\http\endpoint\impl\plugin\CloudPluginGetEndPoint;
use hydracloud\cloud\http\endpoint\impl\plugin\CloudPluginListEndPoint;
use hydracloud\cloud\http\endpoint\impl\server\CloudServerExecuteEndPoint;
use hydracloud\cloud\http\endpoint\impl\server\CloudServerGetEndPoint;
use hydracloud\cloud\http\endpoint\impl\server\CloudServerListEndPoint;
use hydracloud\cloud\http\endpoint\impl\server\CloudServerLogsGetEndPoint;
use hydracloud\cloud\http\endpoint\impl\server\CloudServerSaveEndPoint;
use hydracloud\cloud\http\endpoint\impl\server\CloudServerStartEndPoint;
use hydracloud\cloud\http\endpoint\impl\server\CloudServerStopEndPoint;
use hydracloud\cloud\http\endpoint\impl\template\CloudTemplateCreateEndPoint;
use hydracloud\cloud\http\endpoint\impl\template\CloudTemplateEditEndPoint;
use hydracloud\cloud\http\endpoint\impl\template\CloudTemplateGetEndPoint;
use hydracloud\cloud\http\endpoint\impl\template\CloudTemplateListEndPoint;
use hydracloud\cloud\http\endpoint\impl\template\CloudTemplateRemoveEndPoint;
use hydracloud\cloud\http\io\Request;
use hydracloud\cloud\http\io\Response;
use hydracloud\cloud\http\util\Router;
use hydracloud\cloud\terminal\log\CloudLogger;

final class EndpointRegistry {

    /** @var array<EndPoint> */
    private static array $endPoints = [];

    public static function registerDefaults(): void {
        $endPoints = [
            new CloudInfoEndPoint(),
            new CloudPlayerGetEndPoint(), new CloudPlayerTextEndPoint(), new CloudPlayerKickEndPoint(), new CloudPlayerListEndPoint(),
            new CloudPluginGetEndPoint(), new CloudPluginEnableEndPoint(), new CloudPluginDisableEndPoint(), new CloudPluginListEndPoint(),
            new CloudTemplateCreateEndPoint(), new CloudTemplateRemoveEndPoint(), new CloudTemplateGetEndPoint(), new CloudTemplateListEndPoint(), new CloudTemplateEditEndPoint(),
            new CloudServerStartEndPoint(), new CloudServerStopEndPoint(), new CloudServerSaveEndPoint(), new CloudServerExecuteEndPoint(), new CloudServerGetEndPoint(), new CloudServerListEndPoint(), new CloudServerLogsGetEndPoint(),
            new ModuleGetEndPoint(), new ModuleListEndPoint(), new ModuleEditEndPoint(),
            new MaintenanceAddEndPoint(), new MaintenanceRemoveEndPoint(), new MaintenanceGetEndPoint(), new MaintenanceListEndPoint()
        ];

        foreach ($endPoints as $endPoint) {
            self::addEndPoint($endPoint);
        }
    }

    public static function addEndPoint(EndPoint $endPoint): void {
        if (in_array(strtoupper($endPoint->getRequestMethod()), Request::SUPPORTED_REQUEST_METHODS)) {
            self::$endPoints[$endPoint->getPath()] = $endPoint;
            Router::getInstance()->{strtolower($endPoint->getRequestMethod())}($endPoint->getPath(), function (Request $request, Response $response) use ($endPoint): void {
                $response->contentType("application/json");
                if (!$request->authorized()) {
                    $response->code(401);
                    CloudLogger::get()->warn("Received an unauthorized request by §b" . $request->data()->address() . "§r, ignoring...");
                    return;
                }

                if ($endPoint->isBadRequest($request)) {
                    $response->code(400);
                    return;
                }

                $response->body($endPoint->handleRequest($request, $response));
            });
        }
    }

    public static function removeEndPoint(EndPoint $endPoint): void {
        if (isset(self::$endPoints[$endPoint->getPath()])) unset(self::$endPoints[$endPoint->getPath()]);
    }

    public static function getEndPoint(string $path): ?EndPoint {
        return self::$endPoints[$path] ?? null;
    }
}