<?php

namespace hydracloud\cloud\plugin\loader;

use hydracloud\cloud\plugin\CloudPlugin;
use hydracloud\cloud\plugin\CloudPluginDescription;
use hydracloud\cloud\HydraCloud;
use hydracloud\cloud\terminal\log\CloudLogger;

final class FolderCloudPluginLoader implements CloudPluginLoader {

    public function canLoad(string $path): bool {
        return is_dir($path) && file_exists($path . "/plugin.yml") && file_exists($path . "/src/");
    }

    public function loadPlugin(string $path): string|CloudPlugin {
        $pluginYml = yaml_parse(file_get_contents($path . "/plugin.yml"));
        CloudLogger::get()->debug("Parsing plugin.yml... (" . $path . ")");
        if (!is_array($pluginYml)) return "Can't parse plugin.yml";
        $pluginYml = CloudPluginDescription::fromArray($pluginYml);
        if ($pluginYml === null) return "Incorrect plugin.yml";

        CloudLogger::get()->debug("Adding plugin to class loader (" . $path . ")");
        HydraCloud::getInstance()->getClassLoader()->addPath("", $path . "/src");
        $plugin = new ($pluginYml->getMain())($pluginYml);
        if (!is_subclass_of($plugin, CloudPlugin::class)) return "Is not a valid CloudPlugin";
        return $plugin;
    }
}