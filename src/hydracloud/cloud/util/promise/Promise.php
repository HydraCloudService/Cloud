<?php

namespace hydracloud\cloud\util\promise;

use Closure;

final class Promise {

    private bool $resolved = false {
        get {
            return $this->resolved;
        }
    }
    private mixed $result = null {
        get {
            return $this->result;
        }
    }
    private ?Closure $success = null;
    private ?Closure $failure = null;

    public function resolve(mixed $result): void {
        if ($this->resolved) {
            return;
        }

        $this->result = $result;
        $this->resolved = true;

        if ($this->success !== null) {
            ($this->success)($this->result);
        }

        $this->success = null;
        $this->failure = null;
    }

    public function reject(): void {
        if ($this->resolved) {
            return;
        }

        if ($this->failure !== null) {
            ($this->failure)();
        }

        $this->success = null;
        $this->failure = null;
    }

    public function then(Closure $closure): self {
        if ($this->resolved) {
            if ($this->result !== null) {
                ($closure)($this->result);
            }
        } else {
            $this->success = $closure;
        }

        return $this;
    }

    public function failure(Closure $closure): self {
        if ($this->resolved) {
            if ($this->result === null) {
                ($closure)();
            }
        } else {
            $this->failure = $closure;
        }

        return $this;
    }

}