<?php

namespace hydracloud\cloud\network\packet\impl\type;

use hydracloud\cloud\language\Language;
use hydracloud\cloud\network\client\ServerClient;
use hydracloud\cloud\network\client\ServerClientCache;
use hydracloud\cloud\network\packet\impl\normal\CloudNotifyPacket;
use hydracloud\cloud\util\enum\EnumTrait;

/**
 * @method static NotifyType STARTING()
 * @method static NotifyType STOPPING()
 * @method static NotifyType TIMED()
 * @method static NotifyType CRASHED()
 * @method static NotifyType START_FAILED()
 */
final class NotifyType {
    use EnumTrait;

    protected static function init(): void {
        self::register("starting", new NotifyType("STARTING", Language::current()->translate("inGame.notify.message.starting")));
        self::register("stopping", new NotifyType("STOPPING", Language::current()->translate("inGame.notify.message.stopping")));
        self::register("timed", new NotifyType("TIMED", Language::current()->translate("inGame.notify.message.timed")));
        self::register("crashed", new NotifyType("CRASHED", Language::current()->translate("inGame.notify.message.crashed")));
        self::register("start_failed", new NotifyType("START_FAILED", Language::current()->translate("inGame.notify.message.start_failed")));
    }

    public function __construct(
        private readonly string $name,
        private readonly string $message
    ) {}

    public function send(array $params): void {
        CloudNotifyPacket::create(str_replace(array_keys($params), array_values($params), $this->message))->broadcastPacket(
            ...ServerClientCache::getInstance()->pick(fn(ServerClient $client) => $client->getServer() !== null && $client->getServer()->getTemplate()->getTemplateType()->isProxy())
        );
    }

    public function getName(): string {
        return $this->name;
    }

    public function getMessage(): string {
        return $this->message;
    }
}