<?php

namespace hydracloud\cloud\provider\database;

use hydracloud\cloud\database\query\QueryBuilder;
use hydracloud\cloud\template\TemplateHelper;
use hydracloud\cloud\util\enum\ParameterEnumTrait;

/**
 * @method static QueryBuilder createTables()
 * @method static QueryBuilder addTemplate(array $data)
 * @method static QueryBuilder removeTemplate(string $name)
 * @method static QueryBuilder editTemplate(string $name, array $newData)
 * @method static QueryBuilder getTemplate(string $name)
 * @method static QueryBuilder checkTemplate(string $name)
 * @method static QueryBuilder getTemplates()
 * @method static QueryBuilder addServerGroup(array $data)
 * @method static QueryBuilder removeServerGroup(string $name)
 * @method static QueryBuilder editServerGroup(string $name, array $newData)
 * @method static QueryBuilder getServerGroup(string $name)
 * @method static QueryBuilder checkServerGroup(string $name)
 * @method static QueryBuilder getServerGroups()
 * @method static QueryBuilder setModuleState(string $module, bool $enabled)
 * @method static QueryBuilder getModuleState(string $module)
 * @method static QueryBuilder enablePlayerNotifications(string $player)
 * @method static QueryBuilder disablePlayerNotifications(string $player)
 * @method static QueryBuilder hasNotificationsEnabled(string $player)
 * @method static QueryBuilder addToWhitelist(string $player)
 * @method static QueryBuilder removeFromWhitelist(string $player)
 * @method static QueryBuilder isOnWhitelist(string $player)
 * @method static QueryBuilder getWhitelist()
 */
final class DatabaseQueries {
    use ParameterEnumTrait;

    protected static function init(): void {
        self::register("createTables", static function (): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::TEMPLATES)
                ->create([
                    "name" => "VARCHAR(50) PRIMARY KEY",
                    "lobby" => "BOOL",
                    "maintenance" => "BOOL",
                    "static" => "BOOL",
                    "maxPlayerCount" => "INTEGER",
                    "minServerCount" => "INTEGER",
                    "maxServerCount" => "INTEGER",
                    "startNewPercentage" => "FLOAT",
                    "autoStart" => "BOOL",
                    "templateType" => "VARCHAR(10)"
                ])
                ->changeTable(DatabaseTables::SERVER_GROUPS)
                    ->create([
                        "name" => "VARCHAR(50) PRIMARY KEY",
                        "templates" => "TEXT"
                    ])
                ->changeTable(DatabaseTables::MODULES)
                    ->create([
                        "module" => "VARCHAR(100) PRIMARY KEY",
                        "enabled" => "BOOL"
                    ])
                ->changeTable(DatabaseTables::NOTIFICATIONS)
                    ->create([
                        "player" => "VARCHAR(16) PRIMARY KEY"
                    ])
                ->changeTable(DatabaseTables::MAINTENANCE_LIST)
                    ->create([
                        "player" => "VARCHAR(16) PRIMARY KEY"
                    ]);
        });

        self::register("addTemplate", static function (array $data): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::TEMPLATES)
                ->insert($data);
        });

        self::register("removeTemplate", static function (string $name): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::TEMPLATES)
                ->delete(["name" => $name]);
        });

        self::register("editTemplate", static function (string $name, array $newData): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::TEMPLATES)
                ->update($newData, ["name" => $name]);
        });

        self::register("getTemplate", static function (string $name): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::TEMPLATES)
                ->get(TemplateHelper::KEYS, ["name" => $name]);
        });

        self::register("checkTemplate", static function (string $name): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::TEMPLATES)
                ->has(["name" => $name]);
        });

        self::register("getTemplates", static function (): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::TEMPLATES)
                ->select(TemplateHelper::KEYS, "*");
        });

        self::register("addServerGroup", static function (array $data): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::SERVER_GROUPS)
                ->insert($data);
        });

        self::register("removeServerGroup", static function (string $name): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::SERVER_GROUPS)
                ->delete(["name" => $name]);
        });

        self::register("editServerGroup", static function (string $name, array $newData): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::SERVER_GROUPS)
                ->update($newData, ["name" => $name]);
        });

        self::register("getServerGroup", static function (string $name): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::SERVER_GROUPS)
                ->get(["name", "templates"], ["name" => $name]);
        });

        self::register("checkServerGroup", static function (string $name): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::SERVER_GROUPS)
                ->has(["name" => $name]);
        });

        self::register("getServerGroups", static function (): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::SERVER_GROUPS)
                ->select(["name", "templates"], "*");
        });

        self::register("setModuleState", static function (string $module, bool $enabled): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::MODULES)
                ->update(["enabled" => $enabled], ["module" => $module]);
        });

        self::register("getModuleState", static function (string $module): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::MODULES)
                ->get(["enabled"], ["module" => $module]);
        });

        self::register("enablePlayerNotifications", static function (string $player): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::NOTIFICATIONS)
                ->insert(["player" => $player]);
        });

        self::register("disablePlayerNotifications", static function (string $player): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::NOTIFICATIONS)
                ->delete(["player" => $player]);
        });

        self::register("hasNotificationsEnabled", static function (string $player): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::NOTIFICATIONS)
                ->has(["player" => $player]);
        });

        self::register("addToWhitelist", static function (string $player): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::MAINTENANCE_LIST)
                ->insert(["player" => $player]);
        });

        self::register("removeFromWhitelist", static function (string $player): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::MAINTENANCE_LIST)
                ->delete(["player" => $player]);
        });

        self::register("isOnWhitelist", static function (string $player): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::MAINTENANCE_LIST)
                ->has(["player" => $player]);
        });

        self::register("getWhitelist", static function (): QueryBuilder {
            return QueryBuilder::table(DatabaseTables::MAINTENANCE_LIST)
                ->select(["player"], "*");
        });
    }
}