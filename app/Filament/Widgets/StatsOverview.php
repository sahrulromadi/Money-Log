<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalExpanses = Transaction::whereHas('category', function ($query) {
            $query->where('is_income', false);
        })->sum('amount');
        $totalIncomes = Transaction::whereHas('category', function ($query) {
            $query->where('is_income', true);
        })->sum('amount');
        $net = $totalIncomes - $totalExpanses;

        return [
            Stat::make('Total Expenses', 'Rp ' . number_format($totalExpanses, '0', ',', '.') . ',00'),
            Stat::make('Total Incomes', 'Rp ' . number_format($totalIncomes, '0', ',', '.') . ',00'),
            Stat::make('Net', ($net > 0 ? 'Rp ' : '-Rp ') . number_format(abs($net), '0', ',', '.') . ',00'),
        ];
    }
}
