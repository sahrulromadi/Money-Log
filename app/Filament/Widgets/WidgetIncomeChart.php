<?php

namespace App\Filament\Widgets;

use Flowframe\Trend\Trend;
use App\Models\Transaction;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;

class WidgetIncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Total Incomes Chart';
    protected static string $color = 'success';

    protected function getData(): array
    {
        $data = Trend::query(
            Transaction::whereHas(
                'category',
                function ($query) {
                    $query->where('is_income', true);
                }
            )
        )
            ->dateColumn('transaction_date')
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->sum('amount');

        return [
            'datasets' => [
                [
                    'label' => 'Total Incomes',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
