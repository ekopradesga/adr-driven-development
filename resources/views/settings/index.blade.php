@extends('adminlte::page')

@section('title', 'Settings')

@section('content_header')
    <h1>Settings</h1>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <form method="GET" action="{{ route('settings.index') }}" class="form-row align-items-end mb-3">
        <div class="col-md-4 mb-2">
            <label for="search" class="mb-1">Search</label>
            <input id="search" type="text" name="search" class="form-control form-control-sm"
                   value="{{ request('search') }}" placeholder="Key or name...">
        </div>
        <div class="col-md-3 mb-2">
            <label for="category" class="mb-1">Category</label>
            <select id="category" name="category" class="form-control form-control-sm">
                <option value="">All</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->code }}" {{ request('category') === $category->code ? 'selected' : '' }}>
                        {{ $category->label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <label for="scope" class="mb-1">Scope</label>
            <select id="scope" name="scope" class="form-control form-control-sm">
                <option value="">All</option>
                @foreach ($scopes as $scope)
                    <option value="{{ $scope->value }}" {{ request('scope') === $scope->value ? 'selected' : '' }}>
                        {{ $scope->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <label for="is_active" class="mb-1">Status</label>
            <select id="is_active" name="is_active" class="form-control form-control-sm">
                <option value="">All</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-md-1 mb-2 text-right">
            <button type="submit" class="btn btn-sm btn-secondary w-100">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Key</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Data Type</th>
                    <th>Scope</th>
                    <th>Scope ID</th>
                    <th>Current Value</th>
                    <th>Status</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($settings as $setting)
                    @php
                        $typedValue = $setting->getTypedValue();
                        $renderedValue = is_array($typedValue)
                            ? json_encode($typedValue)
                            : (is_bool($typedValue) ? ($typedValue ? 'true' : 'false') : (string) $typedValue);
                    @endphp
                    <tr>
                        <td><code>{{ $setting->key }}</code></td>
                        <td>{{ $setting->registryEntry?->name ?? '-' }}</td>
                        <td>{{ $setting->registryEntry?->category?->label ?? '-' }}</td>
                        <td>{{ $setting->registryEntry?->data_type?->label() ?? '-' }}</td>
                        <td>{{ $setting->scope?->label() ?? '-' }}</td>
                        <td>{{ $setting->scope_id }}</td>
                        <td style="max-width: 280px; white-space: normal; word-break: break-word;">{{ $renderedValue }}</td>
                        <td>
                            @if ($setting->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-right">
                            @can('view', $setting)
                                <a href="{{ route('settings.edit', $setting) }}" class="btn btn-xs btn-primary">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No settings found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($settings->hasPages())
        <div class="mt-3">
            {{ $settings->links() }}
        </div>
    @endif
@stop
