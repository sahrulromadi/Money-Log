<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\CategoryResource;
use Illuminate\Support\Facades\Auth;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
