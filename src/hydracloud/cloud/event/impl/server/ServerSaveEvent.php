<?php

namespace hydracloud\cloud\event\impl\server;

use hydracloud\cloud\event\Cancelable;
use hydracloud\cloud\event\CancelableTrait;

class ServerSaveEvent extends ServerEvent implements Cancelable {
    use CancelableTrait;
}