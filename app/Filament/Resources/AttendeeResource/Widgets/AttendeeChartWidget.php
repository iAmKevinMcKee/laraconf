<?php

namespace App\Filament\Resources\AttendeeResource\Widgets;

use App\Filament\Resources\AttendeeResource\Pages\ListAttendees;
use App\Models\Attendee;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class AttendeeChartWidget extends ChartWidget
{
    use InteractsWithPageTable;

    protected static ?string $heading = 'Attendee Signups';

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = null;

    protected static ?string $maxHeight = '200px';

    public ?string $filter = '3months';

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Last Week',
            'month' => 'Last Month',
            '3months' => 'Last 3 Months',
        ];
    }

    protected function getTablePage(): string
    {
        return ListAttendees::class;
    }

    protected function getData(): array
    {
        $filter = $this->filter;

        $query = $this->getPageTableQuery();
        $query->getQuery()->orders = [];

        match ($filter) {
            'week' => $data = Trend::query($query)
                ->between(
                    start: now()->subWeek(),
                    end: now(),
                )
                ->perDay()
                ->count(),
            'month' => $data = Trend::query($query)
                ->between(
                    start: now()->subMonth(),
                    end: now(),
                )
                ->perDay()
                ->count(),
            '3months' => $data = Trend::query($query)
                ->between(
                    start: now()->subMonths(3),
                    end: now(),
                )
                ->perMonth()
                ->count(),
        };

        return [
            'datasets' => [
                [
                    'label' => 'Signups',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
