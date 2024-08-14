<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'category_name')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->placeholder('Rp 1.000,00')
                    ->required()
                    ->numeric(),
                Forms\Components\DatePicker::make('transaction_date')
                    ->label('Date')
                    ->placeholder('Dec 18, 2024')
                    ->native(false)
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->placeholder('Type here your needs!')
                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->imageEditor()
                    ->downloadable()
                    ->panelLayout('grid'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('category.category_name')
                    ->label('Transaction')
                    ->description(fn(Transaction $record): string => $record->description)
                    ->searchable(['category_name', 'description'])
                    ->sortable(),
                Tables\Columns\IconColumn::make('category.is_income')
                    ->label('Type')
                    ->trueIcon('heroicon-s-currency-dollar')
                    ->falseIcon('heroicon-s-currency-dollar')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->money('IDR', locale: 'ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Date')
                    ->date('d/m/Y')
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
                SelectFilter::make('category')
                    ->relationship('category', 'category_name'),
                Filter::make('transaction_date')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('From'),
                        DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('transaction_date', '<=', $date),
                            );
                    })
            ], layout: FiltersLayout::Modal)
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
