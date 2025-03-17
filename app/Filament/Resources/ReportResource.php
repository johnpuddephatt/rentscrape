<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Filament\Resources\ReportResource\RelationManagers;
use App\Models\Outcode;
use App\Models\Report;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';





    // 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('outcode')
                    ->label('Area')
                    ->options(Outcode::orderBy('outcode')->get()->pluck('outcode', 'outcode'))
                    ->searchable(),

                Forms\Components\Select::make('source')
                    ->options([
                        'zoopla_api' => 'Zoopla (API)',
                        'openrent_scraper' => 'OpenRent (Scraper)',
                    ])
                    ->required(),

                // Forms\Components\Select::make('status')
                //     ->options([
                //         'failed' => 'Failed',
                //         'in_progress' => 'In Progress',
                //         'complete' => 'Complete',
                //     ])
                //     ->required(),



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('outcode')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable(),


                Tables\Columns\TextColumn::make('source')
                    ->sortable(),

                Tables\Columns\TextColumn::make('Listings')
                    ->state(function (Report $record) {
                        return $record->listings()->count();
                    }),

                Tables\Columns\IconColumn::make('status')
                    ->icon(fn(string $state): string => match ($state) {
                        'new' => 'heroicon-o-ellipsis-horizontal',
                        'processing' => 'heroicon-o-arrow-path',
                        'complete' => 'heroicon-o-check-circle',
                        'error' => 'heroicon-o-exclamation-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'new' => 'gray',
                        'processing' => 'warning',
                        'complete' => 'success',
                        'error' => 'danger',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(fn(Report $record): string => ReportResource::getUrl('view', ['record' => $record]))
            ->poll('5s');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('outcode'),
                Infolists\Components\TextEntry::make('source'),
                Infolists\Components\TextEntry::make('status')->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'new' => 'gray',
                        'processing' => 'warning',
                        'complete' => 'success',
                        'error' => 'danger',
                    }),

                Infolists\Components\TextEntry::make('error')->hidden(
                    fn(Report $record) => $record->error === null,
                ),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ListingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'view' => Pages\ViewReport::route('/{record}'),
        ];
    }
}
