<?php

namespace App\Filament\Resources\ServiceLogs;

use App\Filament\Resources\ServiceLogs\Pages\CreateServiceLog;
use App\Filament\Resources\ServiceLogs\Pages\EditServiceLog;
use App\Filament\Resources\ServiceLogs\Pages\ListServiceLogs;
use App\Filament\Resources\ServiceLogs\Schemas\ServiceLogForm;
use App\Filament\Resources\ServiceLogs\Tables\ServiceLogsTable;
use App\Models\ServiceLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServiceLogResource extends Resource
{
    protected static ?string $model = ServiceLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'action';

    public static function table(Table $table): Table
    {
        return ServiceLogsTable::configure($table);
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
            'index' => ListServiceLogs::route('/'),
        ];
    }
}
