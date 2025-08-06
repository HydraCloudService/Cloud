<?php

namespace hydracloud\cloud\database\query;

use Closure;
use hydracloud\cloud\database\util\Connection;

class CustomQuery extends MySQLQuery {

    public function __construct(private readonly Closure $closure) {}

    public function onRun(Connection $connection): mixed {
        return ($this->closure)($connection);
    }

    public static function custom(Closure $closure): self {
        return new self($closure);
    }
}