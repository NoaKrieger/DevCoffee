<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CoffeeService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class ConsuptionController
{
    public function __construct(private readonly CoffeeService $service)
    {
    }

    public function index(Request $request, Response $response): Response
    { 
        $baseUrl = $this->baseUrl($request);

        $items = array_map(
            fn (array $consumption): array => $this->withLinks($consumption, $baseUrl),
            $this->service->listConsumptions()
        );

        return $this->json($response, $items, 200);
    }

    public function create(Request $request, Response $response): Response
    {
        $consumption = $this->service->registerConsumption($request->getParsedBody());

        return $this->json($response, $this->withLinks($consumption, $this->baseUrl($request)), 201);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->service->deleteConsumption((int) $args['id']);

        return $response->withStatus(204);
    }

    private function withLinks(array $consuption, string $baseUrl): array
    {
        return $consumption + [
            '_links' => [
                'delete' => ['href' => "$baseUrl/api/consumptions/{$consumption['id']}", 'method' => 'DELETE'],
            ],
        ];
    }

    private function baseUrl(Request $request): string
    {
        $uri = $request->getUri();
        $port = $uri->getPort();

        return $uri->getScheme() . '://' . $uri->getHost() . ($port !== null ? ":$port": '');
    }

    private function json(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) 
        );

        return $response
            -withHeader('Content-Type', 'application/json')
            -withStatus($status);
    }
}