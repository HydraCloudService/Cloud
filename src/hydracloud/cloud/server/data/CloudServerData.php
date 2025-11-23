<?php

namespace hydracloud\cloud\server\data;

final class CloudServerData {

    public function __construct(
        private readonly int $port,
        public int           $maxPlayers {
            get {
                return $this->maxPlayers;
            }
            set {
                $this->maxPlayers = $value;
            }
        },
        public ?int          $processId = null {
            get {
                return $this->processId;
            }
            set {
                $this->processId = $value;
            }
        }
    ) {}

    public function getPort(): int {
        return $this->port;
    }

}