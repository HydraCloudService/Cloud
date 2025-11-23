<?php

namespace hydracloud\cloud\event;

interface Cancelable {

    public function cancel(): void;

    public function uncancel(): void;

    public bool $cancelled {
        get;
    }
}