<?php

namespace Database\Seeders\Support;

/**
 * Picks list indexes with probability proportional to their weight.
 * Precomputes a cumulative table so each pick is a binary search (O(log n)),
 * which matters when drawing 400k+ medicines out of 15k.
 */
final class WeightedPicker
{
    /** @var float[] */
    private array $cumulative = [];

    private float $total = 0.0;

    /** @param float[] $weights */
    public function __construct(array $weights)
    {
        foreach ($weights as $weight) {
            $this->total += $weight;
            $this->cumulative[] = $this->total;
        }
    }

    /**
     * Zipf-like popularity: the item at rank r gets weight 1 / r^skew.
     * Ranks are shuffled so popularity is unrelated to insertion order.
     */
    public static function zipf(int $count, float $skew, Rng $rng): self
    {
        $ranks = $rng->shuffle(range(1, $count));

        return new self(array_map(fn (int $rank) => 1 / ($rank ** $skew), $ranks));
    }

    public function pick(Rng $rng): int
    {
        $x = $rng->float() * $this->total;
        $low = 0;
        $high = count($this->cumulative) - 1;

        while ($low < $high) {
            $mid = intdiv($low + $high, 2);
            if ($this->cumulative[$mid] > $x) {
                $high = $mid;
            } else {
                $low = $mid + 1;
            }
        }

        return $low;
    }
}
