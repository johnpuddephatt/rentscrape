<?php

namespace App\Filament\Resources\ReportResource\RelationManagers;


use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Exports\ListingExporter;
use Filament\Tables\Actions\ExportAction;

class ListingsRelationManager extends RelationManager
{
    protected static string $relationship = 'listings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('rental_price'),


                Forms\Components\TextInput::make('postcode'),
                Forms\Components\TextInput::make('subcode'),
                Forms\Components\TextInput::make('outcode'),
                Forms\Components\TextInput::make('district'),
                Forms\Components\TextInput::make('address'),
                Forms\Components\TextInput::make('description'),
                Forms\Components\TextInput::make('latitude'),
                Forms\Components\TextInput::make('longitude'),
                Forms\Components\TextInput::make('property_type'),
                Forms\Components\TextInput::make('property_status'),
                Forms\Components\TextInput::make('bedrooms'),
                Forms\Components\TextInput::make('bathrooms'),

                Forms\Components\Checkbox::make('student_friendly'),
                Forms\Components\Checkbox::make('families_allowed'),
                Forms\Components\Checkbox::make('pets_allowed'),
                Forms\Components\Checkbox::make('smokers_allowed'),
                Forms\Components\Checkbox::make('dss_covers_rent'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table

            ->recordTitleAttribute('id')
            ->columns([

                // Tables\Columns\TextColumn::make('listing_id')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('rental_price')
                    ->money('gbp')
                    ->sortable(),
                Tables\Columns\TextColumn::make('postcode')
                    ->searchable(),



                Tables\Columns\TextColumn::make('property_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('property_status')
                    ->label('Status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bedrooms')
                    ->label('Beds')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bathrooms')
                    ->label('Baths')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('student_friendly')->label('Students')->boolean(),
                Tables\Columns\IconColumn::make('families_allowed')->label('Families')->boolean(),
                Tables\Columns\IconColumn::make('pets_allowed')->label('Pets')->boolean(),
                Tables\Columns\IconColumn::make('smokers_allowed')->label('Smokers')->boolean(),
                Tables\Columns\IconColumn::make('dss_covers_rent')->label('DSS Covers Rent')->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordAction(Tables\Actions\ViewAction::class)

            ->headerActions([

                ExportAction::make()
                    ->exporter(ListingExporter::class)
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),

            ]);
        // ->bulkActions([
        //     Tables\Actions\BulkActionGroup::make([
        //         Tables\Actions\DeleteBulkAction::make(),
        //     ]),
        // ]);
    }
}
