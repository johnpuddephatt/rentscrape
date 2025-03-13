<?php

namespace App\Filament\Exports;

use App\Models\Listing;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ListingExporter extends Exporter
{
    protected static ?string $model = Listing::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('listing_id'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
            ExportColumn::make('rental_price'),
            ExportColumn::make('postcode'),
            ExportColumn::make('subcode'),
            ExportColumn::make('outcode'),
            ExportColumn::make('district'),
            ExportColumn::make('address'),
            ExportColumn::make('description'),
            ExportColumn::make('latitude'),
            ExportColumn::make('longitude'),
            ExportColumn::make('property_type'),
            ExportColumn::make('property_status'),
            ExportColumn::make('bedrooms'),
            ExportColumn::make('bathrooms'),
            ExportColumn::make('student_friendly'),
            ExportColumn::make('families_allowed'),
            ExportColumn::make('pets_allowed'),
            ExportColumn::make('smokers_allowed'),
            ExportColumn::make('dss_covers_rent'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your listing export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
