<?php

declare (strict_types=1);

namespace App\Responses;

use DomainException;
use InvalidArgumentException;
use OutOfBoundsException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Headers;
use Slim\Psr7\Response;
use Throwable;

final class ProblemDetailsResponse extends Response
{
    public function __construct(int $status, string $title, string $detail, string $type = 'about:blank')
    {

        parent::__construct($status, new Headers(['Content-Type' => 'application/problem+json']));

        $this->getBody()->write(json_encode([
            'type' =>$type,
            'title' =>$title,
            'status' =>$status,
            'detail' =>$detail,
        ],JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }


    public static function fromThrowable(Throwable $e): self
    {

        $problem = match (true) {
            $e instanceof InvalidArgumentException => new self(400, 'Bad Request', $e->getMessage()),
            $e instanceof OutOfBoundsException => new self(404, 'Not Found', $e->getMessage()),
            $e instanceof DomainException => new self(429, 'Caffeine Limit Exceeded', $e->getMessage()),
            $e instanceof HttpNotFoundException => new self(404, 'Not Found', "La route demandée n'existe pas."),
            $e instanceof HttpMethodNotAllowedException => new self(405, 'Method Not Allowed', 'Cette méthode HTTP n\'est pas autorisée sur cette ressource.'),
            $e instanceof HttpException => new self(
                $e->getCode() >= 400 && $e->getCode() <= 599 ? $e->getCode() : 500,
                'HTTP Error',
                $e->getMessage()
            ),

            default => new self(500, 'Internal Server Error', 'Une erreur interne est survenue.'),

        };

        if ($e instanceof HttpMethodNotAllowedException) {
            return $problem->withHeader('Allow', implode(',',$e->getAllowedMethods()));
        }

        return $problem;
    }
}
