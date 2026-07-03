<div class="form-group">
    <label for="router_code">Router Code</label>
    <input id="router_code" name="router_code" class="form-control" value="{{ old('router_code', $router->router_code ?? '') }}" required>
</div>
<div class="form-group">
    <label for="name">Name</label>
    <input id="name" name="name" class="form-control" value="{{ old('name', $router->name ?? '') }}" required>
</div>
<div class="form-group">
    <label for="router_type">Router Type</label>
    <select id="router_type" name="router_type" class="form-control" required>
        @foreach (($types ?? []) as $type)
            <option value="{{ $type->value }}" @selected(old('router_type', $router->router_type->value ?? 'core') === $type->value)>{{ $type->label() }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label for="parent_router_id">Parent Router</label>
    <select id="parent_router_id" name="parent_router_id" class="form-control">
        <option value="">None</option>
        @foreach (($parentRouters ?? []) as $parentRouter)
            <option value="{{ $parentRouter->id }}" @selected((string) old('parent_router_id', $router->parent_router_id ?? '') === (string) $parentRouter->id)>{{ $parentRouter->name }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label for="ip_address">IP Address</label>
    <input id="ip_address" name="ip_address" class="form-control" value="{{ old('ip_address', $router->ip_address ?? '') }}" required>
</div>
<div class="form-group">
    <label for="vendor">Vendor</label>
    <input id="vendor" name="vendor" class="form-control" value="{{ old('vendor', $router->vendor ?? '') }}">
</div>
<div class="form-group">
    <label for="model">Model</label>
    <input id="model" name="model" class="form-control" value="{{ old('model', $router->model ?? '') }}">
</div>
<div class="form-group">
    <label for="location_name">Location</label>
    <input id="location_name" name="location_name" class="form-control" value="{{ old('location_name', $router->location_name ?? '') }}">
</div>