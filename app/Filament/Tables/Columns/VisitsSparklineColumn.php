<?php

namespace App\Filament\Tables\Columns;

use App\Support\LinkVisitsSparklineData;
use AshAllenDesign\ShortURL\Models\ShortURL;
use Filament\Tables\Columns\Column;

class VisitsSparklineColumn extends Column
{
    protected string $view = 'filament.tables.columns.visits-sparkline-column';

    protected int $days = 7;

    public function days(int $days): static
    {
        $this->days = $days;

        return $this;
    }

    public function getSparklineData(): LinkVisitsSparklineData
    {
        /** @var ShortURL $record */
        $record = $this->getRecord();

        $start = now()->subDays($this->days - 1)->startOfDay();
        $end = now()->endOfDay();

        $visitsByDate = $record->visits()
            ->whereBetween('visited_at', [$start, $end])
            ->selectRaw('DATE(visited_at) as date, COUNT(*) as aggregate')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('aggregate', 'date');

        $points = [];

        foreach ($start->daysUntil($end) as $day) {
            $points[] = (int) ($visitsByDate[$day->format('Y-m-d')] ?? 0);
        }

        return new LinkVisitsSparklineData(
            totalVisits: (int) ($record->visits_count ?? $record->visits()->count()),
            points: $points,
        );
    }
}
