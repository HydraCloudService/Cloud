<?php

namespace hydracloud\cloud\web;

use hydracloud\cloud\util\Utils;

final class WebAccount {

    public function __construct(
        private readonly string $name,
        public string $password {
            get {
                return $this->password;
            }
            set {
                $this->password = $value;
            }
        },
        public bool $initialPassword {
            get {
                return $this->initialPassword;
            }
            set {
                $this->initialPassword = $value;
            }
        },
        public WebAccountRoles $role {
            get {
                return $this->role;
            }
            set(WebAccountRoles $value) {
                $this->role = $value;
            }
        }
    ) {}

    public function getName(): string {
        return $this->name;
    }

    public function toArray(): array {
        return [
            "username" => $this->name,
            "password" => $this->password,
            "initialPassword" => $this->initialPassword,
            "role" => $this->role->roleName()
        ];
    }

    public static function create(string $name, string $password, bool $initialPassword, WebAccountRoles $role): self {
        return new self($name, $password, $initialPassword, $role);
    }

    public static function fromArray(array $data): ?self {
        if (!Utils::containKeys($data, "username", "password", "initialPassword", "role")) {
            return null;
        }

        if (($role = WebAccountRoles::get($data["role"])) === null) {
            return null;
        }

        if (!is_bool($data["initialPassword"])) {
            return null;
        }

        return self::create($data["username"], $data["password"], $data["initialPassword"], $role);
    }
}