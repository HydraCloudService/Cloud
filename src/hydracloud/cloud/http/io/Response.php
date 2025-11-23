<?php

namespace hydracloud\cloud\http\io;

use DateTimeInterface;
use hydracloud\cloud\http\util\HttpUtils;
use hydracloud\cloud\http\util\StatusCodes;
use JetBrains\PhpStorm\ArrayShape;
use JsonException;
use function date;
use function implode;
use function is_array;
use function json_encode;
use function strlen;

final class Response {
	
	private string $body = "";
	public ?string $customResponseCodeMessage = null {
        get {
            return $this->customResponseCodeMessage;
        }
    }
    private array $headers = ["Content-Type" => "text/plain", "Content-Length" => 0, "Connection" => "close"];
	
	public function __construct(public int $statusCode = 200 {
        get {
            return $this->statusCode;
        }
    }) { }
	
	public function code(int $statusCode): void {
		$this->statusCode = $statusCode;
	}

    /**
     * @throws JsonException
     */
    public function body(string|array $body): void {
		$this->body = (is_array($body) ? json_encode($body, JSON_THROW_ON_ERROR) : $body);
		if (is_array($body)) {
            $this->contentType("application/json");
        }
		$this->headers["Content-Length"] = strlen($this->body);
	}

    /**
     * @throws JsonException
     */
    public function html(string $body): void {
		$this->contentType("text/html");
		$this->body($body);
	}

    /**
     * @throws JsonException
     */
    public function redirect(string $url, bool $update_body = true): void {
		$this->headers["Location"] = $url;
		$this->code(302);
		if ($update_body) {
            $this->html("<p>Redirecting to <a href='" . $url . "'>" . $url . "</a></p>");
        }
	}
	
	public function contentType(string $type): void {
		$this->headers["Content-Type"] = $type;
	}
	
	public function customResponseCodeMessage(string $message): void {
		$this->customResponseCodeMessage = $message;
	}

    public function __toString(): string {
		$this->headers += $this->getOverwriteHeaders();
		return "HTTP/1.1 " . $this->statusCode . " " . ($this->customResponseCodeMessage ?? StatusCodes::RESPOND_CODES[$this->statusCode] ?? "None") . "\r\n" . implode("\r\n", HttpUtils::encodeHeaders($this->headers)) . "\r\n\r\n" . $this->body;
	}
	
	#[ArrayShape(["Date" => "string"])] private function getOverwriteHeaders(): array {
		return [
			"Date" => date(DateTimeInterface::RFC7231)
		];
	}
}