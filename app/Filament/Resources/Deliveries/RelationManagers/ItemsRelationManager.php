<?php

namespace App\Filament\Resources\Deliveries\RelationManagers;

use App\Filament\Tables\Columns\LineChartColumn;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('inventory_item_id')
                    ->relationship('inventoryItem', 'id', fn (Builder $query) => $query->where('status', 'in_stock'))
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->product->name)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('inventoryItem.product.name')
            ->columns([
                TextColumn::make('inventoryItem.product.name')
                    ->searchable(),
                LineChartColumn::make("temperature (24h)")
                    ->state(fn ($record) => $record->temperatures
                    ->map(fn ($t) => [
                        'temperature' => $t->temperature,
                        'time' => $t->recorded_at->format('H'),
                    ])
                    ->toArray()
                ),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
