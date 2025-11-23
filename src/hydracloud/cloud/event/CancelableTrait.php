<?php

namespace hydracloud\cloud\event;

trait CancelableTrait {

    public bool $cancelled = false {
        get {
            return $this->cancelled;
        }
    }

    public function cancel(): void {
        $this->cancelled = true;
    }

    public function uncancel(): void {
        $this->cancelled = false;
    }

}