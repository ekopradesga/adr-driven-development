@extends('adminlte::page')

@section('title', 'Roles')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Roles</h1>
        @can('create', \App\Models\Role::class)
            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> New Role
            </a>
        @endcan
    </div>
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

    {{-- Search --}}
    <div class="card card-outline card-primary">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('roles.index') }}" class="form-inline" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Name or slug…" value="{{ request('search') }}">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-search mr-1"></i> Search</button>
                <a href="{{ route('roles.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th class="text-center">Users</th>
                        <th class="text-center">Permissions</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>
                                <a href="{{ route('roles.show', $role) }}">{{ $role->name }}</a>
                                @if ($role->description)
                                    <br><small class="text-muted">{{ Str::limit($role->description, 60) }}</small>
                                @endif
                            </td>
                            <td><code>{{ $role->slug }}</code></td>
                            <td>
                                <span class="badge badge-{{ $role->status->badgeColor() }}">
                                    {{ $role->status->label() }}
                                </span>
                            </td>
                            <td class="text-center">{{ $role->users_count }}</td>
                            <td class="text-center">{{ $role->permissions_count }}</td>
                            <td class="text-right">
                                <a href="{{ route('roles.show', $role) }}" class="btn btn-xs btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('update', $role)
                                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-xs btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('delete', $role)
                                    <form method="POST" action="{{ route('roles.destroy', $role) }}" class="d-inline"
                                          onsubmit="return confirm('Delete role {{ addslashes($role->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-xs btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No roles found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($roles->hasPages())
            <div class="card-footer">{{ $roles->links() }}</div>
        @endif
    </div>
@stop
