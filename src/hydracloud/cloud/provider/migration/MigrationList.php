<?php

namespace hydracloud\cloud\provider\migration;

use hydracloud\cloud\util\enum\EnumTrait;

/**
 * @method static JsonToMySqlMigrator JSON_TO_MYSQL()
 */
final class MigrationList {
    use EnumTrait;

    protected static function init(): void {
        self::register("json_to_mysql", new JsonToMySqlMigrator());
    }
}