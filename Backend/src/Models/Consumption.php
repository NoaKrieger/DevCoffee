<?php 

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Consumption
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findLatest(int $limit = 20): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.developer_name, d.name AS drink_name, d.caffeine_mg, c.consumed_at
                FROM consumption c
                JOIN drinks d ON d.id = c.drink_id
                ORDER BY c.consumed_at DESC, c.id DESC
                LIMIT :limit'
        );

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map($this->hydrate(...), $stmt->fetchAll());
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.developer_name, d.name AS drink_name, d.caffeine_mg, c.consumed_at
                FROM consumption c
                JOIN drinks d ON d.id = c.drink_id
                WHERE c.id = :id'
        );
        $stmt->execute(['id'=> $id]);

        $row = $stmt->fetch();

        return $row === false ? null : $this->hydrate($row);
    }

    public function create(int $drinkId, string $developerName): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO consumptions (drink_id, developer_name)
            VALUES (:drink_id, :developer_name)'
        );
        $stmt->execute([
            'drink_id' => $drinkId,
            'developer_name' => $developerName,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM consumption WHERE id = :id');
        $stmt->execute(['id'=> $id]);

        return $stmt->rowCount() > 0;
    }

    public function sumCaffeineToday(string $developerName): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(SUM(d.caffeine_mg), 0)
                FROM consumption c
                JOIN drinks d ON d.id = c.drink_id
                WHERE c.developer_name = :developer_name
                AND c.consumed_at >= CURDATE()
                AND c.consumed_at < CURDATE() + INTERNAL 1 DAY'
        );
        $stmt->execute(['developer_name' => $developerName]);

        return (int) $stmt->fetchColumn();
    }

    private function hydrate(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'developerName' => (string) $row['developer_name'],
            'drinkName' => (string) $row['drink_name'],
            'caffeineMg' => (int) $row['caffeine_mg'],
            'consumedAt' =>(string) $row['consumed_at'],
        ];
    }
}