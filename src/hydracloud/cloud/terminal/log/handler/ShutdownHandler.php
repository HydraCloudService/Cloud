<?php

namespace hydracloud\cloud\terminal\log\handler;

use hydracloud\cloud\HydraCloud;
use hydracloud\cloud\terminal\log\CloudLogger;

final class ShutdownHandler {

    public static function register(): void {
        register_shutdown_function(static fn() => self::shutdown());

        if (function_exists("pcntl_signal")) {
            pcntl_signal(SIGTERM, static fn(int $signo) => self::shutdown());
            pcntl_signal(SIGINT, static fn(int $signo) => self::shutdown());
            pcntl_signal(SIGHUP, static fn(int $signo) => self::shutdown());
            pcntl_async_signals(true);
        }
    }

    public static function unregister(): void {
        register_shutdown_function(fn() => null);

        if (function_exists("pcntl_signal")) {
            pcntl_signal(SIGTERM, SIG_DFL);
            pcntl_signal(SIGINT, SIG_DFL);
            pcntl_signal(SIGHUP, SIG_DFL);
        }
    }

    private static function shutdown(): void {
        CloudLogger::get()->emptyLine();
        HydraCloud::getInstance()?->shutdown();
    }
}