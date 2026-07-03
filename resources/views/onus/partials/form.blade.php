<div class="form-group">
    <label for="olt_id">OLT</label>
    <select id="olt_id" name="olt_id" class="form-control" required>
        <option value="">Select OLT</option>
        @foreach (($olts ?? []) as $olt)
            <option value="{{ $olt->id }}" @selected((string) old('olt_id', $onu->olt_id ?? '') === (string) $olt->id)>{{ $olt->name }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label for="fat_id">FAT</label>
    <select id="fat_id" name="fat_id" class="form-control">
        <option value="">None</option>
        @foreach (($fats ?? []) as $fat)
            <option value="{{ $fat->id }}" @selected((string) old('fat_id', $onu->fat_id ?? '') === (string) $fat->id)>{{ $fat->name }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label for="onu_sn">ONU Serial</label>
    <input id="onu_sn" name="onu_sn" class="form-control" value="{{ old('onu_sn', $onu->onu_sn ?? '') }}" required>
</div>
<div class="form-group">
    <label for="onu_index">ONU Index</label>
    <input id="onu_index" name="onu_index" type="number" min="1" class="form-control" value="{{ old('onu_index', $onu->onu_index ?? '') }}" required>
</div>
<div class="form-group">
    <label for="pon_port">PON Port</label>
    <input id="pon_port" name="pon_port" class="form-control" value="{{ old('pon_port', $onu->pon_port ?? '') }}" required>
</div>
<div class="form-group">
    <label for="model">Model</label>
    <input id="model" name="model" class="form-control" value="{{ old('model', $onu->model ?? '') }}">
</div>
<div class="form-group">
    <label for="customer_label">Customer Label</label>
    <input id="customer_label" name="customer_label" class="form-control" value="{{ old('customer_label', $onu->customer_label ?? '') }}">
</div>
<div class="form-group">
    <label for="rx_power_dbm">RX Power (dBm)</label>
    <input id="rx_power_dbm" name="rx_power_dbm" class="form-control" value="{{ old('rx_power_dbm', $onu->rx_power_dbm ?? '') }}">
</div>
<div class="form-group">
    <label for="tx_power_dbm">TX Power (dBm)</label>
    <input id="tx_power_dbm" name="tx_power_dbm" class="form-control" value="{{ old('tx_power_dbm', $onu->tx_power_dbm ?? '') }}">
</div>
<div class="form-group">
    <label for="last_seen_at">Last Seen At</label>
    <input id="last_seen_at" name="last_seen_at" type="datetime-local" class="form-control" value="{{ old('last_seen_at', isset($onu?->last_seen_at) ? $onu->last_seen_at?->format('Y-m-d\TH:i') : '') }}">
</div>
<div class="form-group">
    <label for="provisioned_at">Provisioned At</label>
    <input id="provisioned_at" name="provisioned_at" type="datetime-local" class="form-control" value="{{ old('provisioned_at', isset($onu?->provisioned_at) ? $onu->provisioned_at?->format('Y-m-d\TH:i') : '') }}">
</div>