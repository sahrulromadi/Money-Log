<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $startDate = ! is_null($this->filters['startDate'] ?? null) ?
            Carbon::parse($this->filters['startDate']) :
            Carbon::now()->startOfYear();

        $endDate = ! is_null($this->filters['endDate'] ?? null) ?
            Carbon::parse($this->filters['endDate']) :
            Carbon::now();

        $userId = Auth::id();
        $totalExpanses = Transaction::where('user_id', $userId)
            ->whereHas('category', function ($query) {
                $query->where('is_income', false);
            })
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $totalIncomes = Transaction::where('user_id', $userId)
            ->whereHas('category', function ($query) {
                $query->where('is_income', true);
            })
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        $net = $totalIncomes - $totalExpanses;

        return [
            Stat::make('Total Expenses', 'Rp ' . number_format($totalExpanses, '0', ',', '.') . ',00'),
            Stat::make('Total Incomes', 'Rp ' . number_format($totalIncomes, '0', ',', '.') . ',00'),
            Stat::make('Net', ($net > 0 ? 'Rp ' : '-Rp ') . number_format(abs($net), '0', ',', '.') . ',00'),
        ];
    }
}
