<?php

declare(strict_types=1);

namespace Zazu\Exception;

use Psr\Http\Message\ResponseInterface;

/**
 * The API error envelope, mirroring the other Zazu SDKs' hierarchy:
 * `{ "error": { "type": ..., "message": ..., "param": ... } }`.
 *
 * Match on {@see ApiException::$kind} (`authentication`, `forbidden`,
 * `not_found`, `validation`, `rate_limit`, `server`, `api`) instead of
 * subclassing.
 */
final class ApiException extends \RuntimeException
{
    /**
     * @param string $kind One of: authentication, forbidden, not_found, validation, rate_limit, server, api
     * @param string|null $type The API's error.type field
     * @param string|null $param The API's error.param field
     * @param int|null $retryAfter Seconds; only set for rate_limit (429)
     * @param array<string, mixed> $body The full decoded error body
     */
    public function __construct(
        public readonly int $status,
        public readonly string $kind,
        public readonly ?string $type,
        string $message,
        public readonly ?string $param,
        public readonly ?string $requestId,
        public readonly ?int $retryAfter,
        public readonly array $body,
    ) {
        parent::__construct($message);
    }

    /**
     * @param array<string, mixed> $body
     */
    public static function fromResponse(ResponseInterface $response, array $body): self
    {
        $status = $response->getStatusCode();

        $type = null;
        $message = null;
        $param = null;
        $payload = $body['error'] ?? null;
        if (\is_array($payload)) {
            $type = \is_string($payload['type'] ?? null) ? $payload['type'] : null;
            $message = \is_string($payload['message'] ?? null) ? $payload['message'] : null;
            $param = \is_string($payload['param'] ?? null) ? $payload['param'] : null;
        }

        $retryAfter = null;
        $kind = match (true) {
            $status === 401 => 'authentication',
            $status === 403 => 'forbidden',
            $status === 404 => 'not_found',
            $status === 422 => 'validation',
            $status === 429 => 'rate_limit',
            $status >= 500 => 'server',
            default => 'api',
        };
        if ($status === 429) {
            $retry = $response->getHeaderLine('Retry-After');
            $retryAfter = $retry !== '' ? (int) $retry : null;
        }

        if ($message === null || $message === '') {
            $reason = $response->getReasonPhrase();
            $message = $reason !== '' ? $reason : "HTTP {$status}";
        }

        $requestId = $response->getHeaderLine('X-Request-Id');

        return new self(
            status: $status,
            kind: $kind,
            type: $type,
            message: $message,
            param: $param,
            requestId: $requestId !== '' ? $requestId : null,
            retryAfter: $retryAfter,
            body: $body,
        );
    }
}
