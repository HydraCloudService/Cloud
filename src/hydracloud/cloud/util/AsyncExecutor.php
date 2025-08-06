<?php

namespace hydracloud\cloud\util;

use Closure;
use hydracloud\cloud\scheduler\AsyncClosureTask;
use hydracloud\cloud\scheduler\AsyncPool;
use hydracloud\cloud\scheduler\AsyncTask;

final class AsyncExecutor {

    public static function execute(Closure $asyncClosure, ?Closure $syncClosure = null, mixed ...$args): void {
        AsyncPool::getInstance()->submitTask(new AsyncClosureTask(fn(AsyncTask $task) => ($asyncClosure)(), function(mixed $result) use($syncClosure, $args): void {
            if ($syncClosure !== null) $syncClosure($result, ...$args);
        }));
    }
}