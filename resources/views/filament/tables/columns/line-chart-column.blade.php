<div
    x-data="{ data: {{ json_encode($column->getChartData()) }} }"
    x-init="initChart($el, data)"
    class="linechart"
    wire:ignore
>
    <canvas id="chart-{{ $getRecord()->id }}" width="200" height="50"></canvas>
    <p>27.8</p>
</div>