<?php

namespace hydracloud\cloud\database\query;

use hydracloud\cloud\database\util\Connection;
use pmmp\thread\ThreadSafeArray;

final class MultipleQueries extends MySQLQuery {

    public function __construct(private readonly ThreadSafeArray $queries) {}

    public function onRun(Connection $connection): array {
        $results = [];
        /** @var MySQLQuery $query */
        foreach ($this->queries as $query) {
            $query->run($connection);
            $results[] = $query->getResult();
        }
        return $results;
    }

    public static function create(mixed ...$queries): MultipleQueries {
        return new self(ThreadSafeArray::fromArray($queries));
    }
}