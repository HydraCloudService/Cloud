<?php

namespace hydracloud\cloud\server\crash;

use JsonException;
use RuntimeException;

final class CrashDumpReader {

    private string $filePath {
        get {
            return $this->filePath;
        }
    }
    public ?array $data = null {
        get {
            return $this->data;
        }
    }

    public function __construct(string $filePath) {
        $this->filePath = $filePath;
        $this->readData();
    }

    /**
     * @throws JsonException
     */
    private function readData(): void {
        $fileHandle = fopen($this->filePath, 'rb');

        $start = false;
        $end = false;

        $data = "";
        while ($line = fgets($fileHandle)) {
            $line = trim($line);

            if ($start === true) {
                if ($line === "===END CRASH DUMP===") {
                    $end = true;
                    break;
                }

                $data .= $line;
            } elseif ($line === "===BEGIN CRASH DUMP===") {
                $start = true;
            }
        }

        fclose($fileHandle);

        if ($start === true && $end === true && trim($data) !== "") {
            $data = base64_decode($data);
            $data = zlib_decode($data);
            $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            $this->data = $data;
        }
    }

    public function hasRead(): bool {
        return is_array($this->data);
    }

    public function getFileName(): string {
        return basename($this->filePath);
    }

    public function getCreationTime(): float {
        if (!$this->hasRead()) {
            throw new RuntimeException("No data was read");
        }

        return (float) $this->data["time"];
    }
}