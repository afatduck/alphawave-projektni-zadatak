<?php

namespace App\Filament\Resources\ServiceLogs\Pages;

use App\Filament\Resources\ServiceLogs\ServiceLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceLogs extends ListRecords
{
    protected static string $resource = ServiceLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
