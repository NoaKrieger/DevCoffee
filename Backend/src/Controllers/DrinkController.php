<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CoffeeService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


final class DrinkController
{
    public function __construct(private readonly CoffeeService $service)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        $baseUrl = $this->baseUrl($request);

        $items = array_map(
            fn (array $drink): array => $this->withLinks($drink, $baseUrl),
            $this->service->listDrinks()
        );

        return $this->json($response, $items, 200);
    }

    public function show(Request $request, Response $response): Response
    {

        $drink = $this->service->createDrink($request->getParseBody());

        $baseUrl = $this->baseUrl($request);

        return $this
            ->json($response, $this->withLinks($drink, $baseUrl), 201)
            ->withHeader('Location', "$baseUrl/api/drinks/{$drink['id']}");
    }


    private function withLinks(array $drink, string $baseUrl): array
    {
        return $drink + [
            '_links' => [
                'self' => ['href' => "$baseUrl/api/drinks/{$drink['id']}", 'method' => 'GET' ],
            ],
        ];
    }


    private function baseUrl(Request $request): string
    {
        $uri = $request->getUri();
        $port = $uri->getPort();

        return $uri->getScheme() . '://' . $uri->getHost() . ($port !== null ? ":$port" : '');
    }

    private function json(Response $response, array $data, int $status): Response
    {
        $response->getBody()->write(
            json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
        );

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}