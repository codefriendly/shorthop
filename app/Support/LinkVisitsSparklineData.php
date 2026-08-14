<?php

namespace App\Support;

final readonly class LinkVisitsSparklineData
{
    /**
     * @param  list<int>  $points
     */
    public function __construct(
        public int $totalVisits,
        public array $points,
    ) {}

    public function hasSparkline(): bool
    {
        return count($this->points) > 1;
    }

    public function polyline(int $width = 96, int $height = 28, int $padding = 2): string
    {
        if (count($this->points) < 2) {
            return '';
        }

        $max = max($this->points) ?: 1;
        $min = min($this->points);
        $range = ($max - $min) ?: 1;
        $lastIndex = count($this->points) - 1;

        return collect($this->points)
            ->map(function (int $point, int $index) use ($height, $lastIndex, $min, $padding, $range, $width): string {
                $x = $padding + ($index / $lastIndex) * ($width - (2 * $padding));
                $y = $padding + (1 - (($point - $min) / $range)) * ($height - (2 * $padding));

                return round($x, 1).','.round($y, 1);
            })
            ->implode(' ');
    }
}
