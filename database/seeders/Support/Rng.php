<?php

namespace Database\Seeders\Support;

use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Deterministic random source. Each seeder gets its own instance derived from
 * the global seed, so changing one seeder does not reshuffle the others.
 */
final class Rng
{
    private Randomizer $randomizer;

    public function __construct(int $seed)
    {
        $this->randomizer = new Randomizer(new Mt19937($seed));
    }

    public function int(int $min, int $max): int
    {
        return $this->randomizer->getInt($min, $max);
    }

    public function float(): float
    {
        return $this->randomizer->nextFloat();
    }

    public function chance(float $probability): bool
    {
        return $this->randomizer->nextFloat() < $probability;
    }

    public function pick(array $list): mixed
    {
        return $list[$this->randomizer->getInt(0, count($list) - 1)];
    }

    /** @param array<array-key, int|float> $weights key => weight */
    public function weighted(array $weights): int|string
    {
        $x = $this->randomizer->nextFloat() * array_sum($weights);

        foreach ($weights as $key => $weight) {
            $x -= $weight;
            if ($x < 0) {
                return $key;
            }
        }

        return array_key_last($weights);
    }

    public function shuffle(array $list): array
    {
        return $this->randomizer->shuffleArray($list);
    }

    /** Standard normal sample (Box-Muller). */
    public function normal(): float
    {
        $u = max($this->randomizer->nextFloat(), 1e-12);

        return sqrt(-2 * log($u)) * cos(2 * M_PI * $this->randomizer->nextFloat());
    }

    /**
     * UUIDv7 (same format Laravel's HasUuids generates) with the record's own
     * timestamp embedded, and the random part drawn from the seeded engine.
     */
    public function uuid7(int $unixMs): string
    {
        $bytes = substr(pack('J', $unixMs), 2).$this->randomizer->getBytes(10);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x70);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);
        $hex = bin2hex($bytes);

        return substr($hex, 0, 8).'-'.substr($hex, 8, 4).'-'.substr($hex, 12, 4).'-'
            .substr($hex, 16, 4).'-'.substr($hex, 20);
    }

    /** UUID for a record created at $unixSeconds. */
    public function uuidAt(int $unixSeconds): string
    {
        return $this->uuid7($unixSeconds * 1000 + $this->randomizer->getInt(0, 999));
    }
}
