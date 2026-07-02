@extends('adminlte::page')

@section('title', 'Permissions')

@section('content_header')
    <h1>Permissions</h1>
@stop

@section('content')
    <div class="callout callout-info">
        <p class="mb-0">
            <i class="fas fa-info-circle mr-1"></i>
            Permissions are managed through seeders. Use Role Management to assign permissions to roles.
        </p>
    </div>

    {{-- Search / Filter --}}
    <div class="card card-outline card-secondary">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('permissions.index') }}" class="form-inline" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Key or name…" value="{{ request('search') }}">
                <select name="category" class="form-control form-control-sm">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                            {{ ucfirst($category) }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-search mr-1"></i> Search</button>
                <a href="{{ route('permissions.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Key</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($permissions as $permission)
                        <tr>
                            <td><code>{{ $permission->key }}</code></td>
                            <td>{{ $permission->name }}</td>
                            <td><span class="badge badge-secondary">{{ $permission->category }}</span></td>
                            <td>
                                <span class="badge badge-{{ $permission->status->badgeColor() }}">
                                    {{ $permission->status->label() }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('permissions.show', $permission) }}" class="btn btn-xs btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No permissions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($permissions->hasPages())
            <div class="card-footer">{{ $permissions->links() }}</div>
        @endif
    </div>
@stop
