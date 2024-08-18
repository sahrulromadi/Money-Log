<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Section;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    protected static ?string $pluralModelLabel = 'Transaction';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Instruction!')
                    ->description('Create your income or expenditure transactions!')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'category_name')
                            ->options(function () {
                                $userId = Auth::id();
                                return Category::where('user_id', $userId)
                                    ->pluck('category_name', 'id');
                            })
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
                            ->directory('images')
                            ->panelLayout('grid'),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $userId = Auth::id();
                $query->where('user_id', $userId);
            })
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->alignment(Alignment::Center),
                Tables\Columns\TextColumn::make('category.category_name')
                    ->label('Transaction')
                    ->alignment(Alignment::Center)
                    ->description(fn(Transaction $record): string => $record->description)
                    ->searchable(['category_name', 'description'])
                    ->sortable(),
                Tables\Columns\IconColumn::make('category.is_income')
                    ->label('Type')
                    ->alignment(Alignment::Center)
                    ->trueIcon('heroicon-s-currency-dollar')
                    ->falseIcon('heroicon-s-currency-dollar')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->alignment(Alignment::Center)
                    ->money('IDR', locale: 'ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Date')
                    ->alignment(Alignment::Center)
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('d/m/Y')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->date('d/m/Y')
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'category_name', function (Builder $query) {
                        $userId = Auth::id();
                        $query->where('user_id', $userId);
                    }),
                Tables\Filters\TrashedFilter::make(),
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
                Tables\Actions\ViewAction::make()
                    ->modalHeading(fn($record) => $record->category->category_name),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('category.category_name'),
                TextEntry::make('description'),
                TextEntry::make('amount'),
                TextEntry::make('transaction_date')
                    ->dateTime('l, d F Y'),
                ImageEntry::make('image'),
            ])
            ->columns(1)
            ->inlineLabel();
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
            // 'create' => Pages\CreateTransaction::route('/create'),
            // 'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
