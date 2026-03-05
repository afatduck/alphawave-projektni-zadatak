<?php

namespace App\Filament\Resources\Documents\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('title'),
                TextColumn::make('file_name'),
                TextColumn::make('document_type')
                    ->badge()
                    ->color(fn (string $state) => match($state) {
                        'PDF', 'DOCX', 'DOC' => 'info',
                        'PNG', 'JPG', 'JPEG', 'WEBP', 'SVG', 'GIF' => 'danger',
                        'XLSX', 'XLS', 'CSV', 'JSON' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('tags')
                    ->badge()
                    ->searchable(),
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
            ->recordActions([
            ])
            ->toolbarActions([
            ]);
    }
}
