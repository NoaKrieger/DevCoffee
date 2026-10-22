<?php

declare(strict_types=1);

namespace App\Models;

use PDO;


final class Drink
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, name, caffeine_mg, volume_ml FROM drinks ORDER BY id'
        );

        return array_map($this->hydrate(...), $stmt->fetchAll());
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, caffeine_mg, volume_ml FROM drinks WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    public function create(string $name, int $caffeineMg, int $volumeMl): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO drinks (name, caffeine_mg, volume_ml)
            VALUES (:name, :caffeine_mg, :volume_ml)'
        );
        $stmt->execute([
            'name' => $name,
            'caffeine_mg' => $caffeineMg,
            'volume_ml' =>$volumeMl,
        ]);

        return [
            'id' => (int) $this->pdo->lastInsertId(),
            'name' => $name,
            'caffeine_mg' =>$caffeineMg,
            'volumeMl' => $volumeMl,
        ];
    }

    private function hydrate(array $row): array
    {
        return [
            'id' =>(int) $row['id'],
            'name' => (string) $row['name'],
            'caffeineMg' =>(int) $row['caffeine_mg'],
            'volumeMl' =>(int) $row['volume_ml'],
        ];
    }
}