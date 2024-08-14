<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CategoryResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CategoryResource\RelationManagers;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-s-list-bullet';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Instruction!')
                    ->description('Create a category for your income or expenses such as food, transportation, etc')
                    ->schema([
                        TextInput::make('category_name')
                            ->label('Name')
                            ->placeholder('Type here!')
                            ->required(),
                        Toggle::make('is_income')
                            ->label('Income')
                            ->onIcon('heroicon-s-currency-dollar')
                            ->offIcon('heroicon-s-currency-dollar')
                            ->onColor('success')
                            ->offColor('danger'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_income')
                    ->label('Type')
                    ->trueIcon('heroicon-s-currency-dollar')
                    ->falseIcon('heroicon-s-currency-dollar')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_income')
                    ->label('Type')
                    ->options([
                        true => 'Income',
                        false => 'Expense',
                    ]),
                Tables\Filters\TrashedFilter::make()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
