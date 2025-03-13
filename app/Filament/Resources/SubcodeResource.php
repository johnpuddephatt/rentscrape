<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubcodeResource\Pages;
use App\Filament\Resources\SubcodeResource\RelationManagers;
use App\Models\Subcode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubcodeResource extends Resource
{
    protected static ?string $model = Subcode::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?string $navigationGroup = 'Geographies';


    public static function getNavigationBadge(): ?string
    {
        return number_format(static::getModel()::count());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('subcode')
                    ->required(),
                Forms\Components\TextInput::make('outcode')
                    ->required(),
                Forms\Components\TextInput::make('district')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subcode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('outcode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('district')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
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
            'index' => Pages\ListSubcodes::route('/'),
            'create' => Pages\CreateSubcode::route('/create'),
            'edit' => Pages\EditSubcode::route('/{record}/edit'),
        ];
    }
}
