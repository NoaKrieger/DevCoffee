<?php

declare(strict_types = 1);

namespace App\Services;

use App\Models\Consumption;
use App\Models\Drink;
use DomainException;
use InvalidArgumentException;
use OutOfBoundsException;
use Psr\Log\LoggerInterface;

final class CoffeeService
{
    public const DAILY_CAFFEINE_LIMIT_MG = 400;
    
    public const HISTORY_SIZE = 20;

    public function __construct(
        private readonly Drink $drinks,
        private readonly Consumption $consumption,
        private readonly LoggerInterface $logger,
    ){
    }

    public function listDrinks(): array
    {
        return $this->drinks->findAll();
    }

    public function getDrink(int $id): array
    {
        return $this->drinks->find($id)
            ?? throw new OutBoundsException("Aucune boisson avec l'identifiant $id.");
    }

    public function createDrink(mixed $payload): array
    {
        $data = $this->requireObject($payload);

        $errors = [];

        $name = $data['name'] ?? null;
        if (!is_string($name) || trim($name) === ''){
            $errors[] = "'name' ne doit pas dépasser 100 caractères";
        }

        $caffeineMg = $data['caffeineMg'] ?? null;
        if (!is_int($caffeineMg) || $caffeine < 0){
            $errors[] = "'caffeineMg' est obligatoire (entier >= 0)";
        }

        $volumeMl = $data['volumeMl'] ?? null;
        if (!is_int($volumeMl) || $volumeMl <= 0) {
            $errors[] = "'volumeMl' est obligatoire (entier > 0)";
        }

        if ($errors !== []) {
            throw new InvalidArgumentException(implode(' ; ', $errors) . '.');
        }

        $drink = $this->drinks->create(trim($name), $caffeineMg, $volumeMl);

        $this->logger->info('Boisson ajoutée au catalogue', ['drinkId' => $drink['id'], 'name'=> $drink['name']]);

        return $drink;
    }

    public function listConsumptions(): array
    {
        return $this->consumptions->findLatest(self::HISTORY_SIZE);
    }

    public function registerConsumption(mixed $payload): array
    {
        $data = $this->requireObject($payload);

        $errors = [];

        $drinkId = $data['drinkId'] ?? null;
        if (!is_int($drinkId) || $drinkId <= 0){
            $errors[] = "'drinkId' est obligatoire (entier > 0)";
        }

        $developerName = $data['developerName']?? null;
        if (!is_string($developerName) || trim($developerName) === ''){
            $errors[] = "'developerName' est obligatoire (chaîne non vide)";
        } elseif (mb_strlen(trim($developerName)) > 100){
            $errors[] = "'devloperName' ne doit pas dépasser 100 caractères";
        }

        if ($errors !== []) {
            throw new InvalidArgumentException(implode(' ; ', $errors) . '.');
        }

        $developerName = trim($developerName);
        $drink = $this->getDrink($drinkId);

        $alreadyConsumed = $this->consumption->sumCaffeineToday($developerName);
        if ($alreadyConsumed + $drink['caffeineMg'] > self::DAILY_CAFFERINE_LIMIT_MG){
            throw new DomainException(sprintf(
                "Seuil de caféine dépassé ! %s a déjà consomé %dmg aujourd'hui (limite : %dmg).",
                $developerName,
                $alreadyConsumed,
                self::DAILY_CAFFEINE_LIMIT_MG
            ));
        }

        $id = $this->consumptions->create($drinkId, $developerName);

        $this->logger->info('Consomation enregistrée', [
            'consumptionId' => $id,
            'developer' => $developerName,
            'drink' => $drink['name'],
            'dayTotalMg' => $alreadyConsumed + $drink['caffeineMg'],
        ]);

        return $this->consumptions->find($id)
            ?? throw new \RuntimeException("Consommation $id introuvable juste après son insertion.");
    }

    public function deleteConsumption(int $id): void
    {
        if (!this->consumptions->delete($id)) {
            throw new OutOfBoundsException("Aucune consomation avec l'identifiant $id.");
        }

        $this->logger->info('Consomation supprimée', ['consumptionId' => $id]);
    }


    private function requireObject(mixed $payload): array
    {
        if (!is_array($payload)){
            throw new InvalidArgumentException(
                'Corps de requête absent ou invalide : un objet JSON est attendu (en-tête Content-Type: application/json).'
            );
        }
        return $payload;
    }
}

