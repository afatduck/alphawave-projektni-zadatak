<?php

namespace App\Filament\Resources\ServiceLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class ServiceLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product_name')
                    ->sortable()
                    ->searchable(
                        query: function (Builder $query, string $search) {
                            $query->whereHas('inventoryItem.product', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            });
                        }
                    ),
                TextColumn::make('performed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('action')
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
                TextColumn::make('description'),
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
                SelectFilter::make('action')
                    ->options([
                        'inspection' => 'Inspection',
                        'repair' => 'Repair',
                        'upgrade' => 'Upgrade'
                    ]),
                DateRangeFilter::make("performed_at")
            ])
            ->recordActions([
            ])
            ->toolbarActions([
            ]);
    }
}
