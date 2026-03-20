<?php

namespace App\Filament\Resources\DeliveryItems;

use App\Filament\Resources\DeliveryItems\Pages\ListDeliveryItems;
use App\Filament\Resources\DeliveryItems\Pages\ViewDeliveryItem;
use App\Filament\Resources\DeliveryItems\Schemas\DeliveryItemForm;
use App\Filament\Resources\DeliveryItems\Tables\DeliveryItemsTable;
use App\Models\DeliveryItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeliveryItemResource extends Resource
{
    protected static ?string $model = DeliveryItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'inventoryItem.product.name';

    public static function table(Table $table): Table
    {
        return DeliveryItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryItems::route('/'),
            'view' => ViewDeliveryItem::route('/{record}'),
        ];
    }
}
