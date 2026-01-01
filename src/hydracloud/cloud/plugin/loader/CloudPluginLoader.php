<?php

namespace hydracloud\cloud\plugin\loader;

use hydracloud\cloud\plugin\CloudPlugin;

interface CloudPluginLoader {

    public function canLoad(string $path): bool;

    public function loadPlugin(string $path): string|CloudPlugin;
}