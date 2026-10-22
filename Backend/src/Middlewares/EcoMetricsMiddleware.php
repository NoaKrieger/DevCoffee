<?php

declare(strict_types=1);

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class EcoMetricsMiddleware implements MiddlewareInterface
{
    private const CO2_GRAMS_PER_BYTE = 0.000000011;
    private const CO2_GRAMS_PER_MS = 0.000001;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $start = microtime(true);
        $response = $handler->handle($request);
        $elapsedMs = (microtime(true) - $start) * 1000;
        $body = $response->getBody();
        $payloadBytes = $body->getSize() ?? strlen((string) $body);
        $co2Grams = ($payloadBytes * self::CO2_GRAMS_PER_BYTE) + ($elapsedMs * self::CO2_GRAMS_PER_MS);

        return $response
    ->withHeader('X-Response-Time-Ms', number_format($elapsedMs, 3, '.', ''))
    ->withHeader('X-Payload-Size-Bytes', (string) $payloadBytes)
    ->withHeader('X-Estimated-CO2-Grams', number_format($co2Grams, 9, '.', ''));
    }
}