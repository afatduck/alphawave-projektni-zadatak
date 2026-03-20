<p>A delivery item temperature alert has been triggered.</p>

<p><strong>Delivery Item:</strong> {{ $alert['delivery_item_id'] }}</p>
<p><strong>Recorded At (UTC):</strong> {{ $alert['recorded_at'] }}</p>
<p><strong>Current Temperature:</strong> {{ number_format($alert['temperature'], 1) }}&deg;C</p>
<p><strong>Average For This Hour:</strong> {{ number_format($alert['hour_average'], 1) }}&deg;C</p>
<p><strong>Allowed Range:</strong> &plusmn;{{ number_format($alert['range'], 1) }}&deg;C</p>
<p><strong>Allowed Interval:</strong>
    {{ number_format($alert['hour_average'] - $alert['range'], 1) }}&deg;C
    to
    {{ number_format($alert['hour_average'] + $alert['range'], 1) }}&deg;C
</p>

<p>
    <a href="{{ $alert['delivery_item_url'] }}">Open Delivery Item</a>
</p>
