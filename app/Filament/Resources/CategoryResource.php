<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Toggle;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\CategoryResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CategoryResource\RelationManagers;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $pluralModelLabel = 'Category';
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
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                $slug = Str::slug($state);
                                $baseSlug = $slug;
                                $count = 1;

                                while (Category::where('slug', $slug)->exists()) {
                                    $slug = "{$baseSlug}-" . $count;
                                    $count++;
                                }

                                $set('slug', $slug);
                            })
                            ->required(),
                        TextInput::make('slug')
                            ->required()
                            ->placeholder('Generate automatically')
                            ->unique('categories', 'slug')
                            ->disabled(),
                        Toggle::make('is_income')
                            ->label('Income')
                            ->onIcon('heroicon-s-currency-dollar')
                            ->offIcon('heroicon-s-currency-dollar')
                            ->onColor('success')
                            ->offColor('danger'),
                    ])
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
                TextColumn::make('category_name')
                    ->label('Name')
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_income')
                    ->label('Type')
                    ->alignment(Alignment::Center)
                    ->trueIcon('heroicon-s-currency-dollar')
                    ->falseIcon('heroicon-s-currency-dollar')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->boolean()
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
                SelectFilter::make('is_income')
                    ->label('Type')
                    ->options([
                        true => 'Income',
                        false => 'Expense',
                    ]),
                Tables\Filters\TrashedFilter::make()
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalHeading(fn($record) => $record->category_name),
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
                TextEntry::make('category_name')
                    ->label('Name'),
                IconEntry::make('is_income')
                    ->label('Type')
                    ->boolean()
                    ->trueIcon('heroicon-s-currency-dollar')
                    ->falseIcon('heroicon-s-currency-dollar')
                    ->trueColor('success')
                    ->falseColor('danger')
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
            'index' => Pages\ListCategories::route('/'),
            // 'create' => Pages\CreateCategory::route('/create'),
            // 'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
