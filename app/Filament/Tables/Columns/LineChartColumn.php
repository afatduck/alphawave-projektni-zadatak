<?php

namespace App\Filament\Tables\Columns;

use Closure;
use Filament\Tables\Columns\Column;

class LineChartColumn extends Column
{
    protected string $view = 'filament.tables.columns.line-chart-column';

    public function getChartData(): array
    {
        return $this->getState() ?? [];
    }
}
