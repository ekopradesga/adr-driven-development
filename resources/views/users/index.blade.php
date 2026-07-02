@extends('adminlte::page')

@section('title', 'Users')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Users</h1>
        @can('create', \App\Models\User::class)
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> New User
            </a>
        @endcan
    </div>
@stop

@section('content')
    {{-- Flash Messages --}}
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

    {{-- Search / Filter --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Search &amp; Filter</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}" class="form-inline flex-wrap" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Name or email…" value="{{ request('search') }}">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-secondary">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            </form>
        </div>
    </div>

    {{-- Results --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <a href="{{ route('users.show', $user) }}">{{ $user->name }}</a>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach ($user->roles as $role)
                                    <span class="badge badge-secondary">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @php $status = $user->status @endphp
                                <span class="badge badge-{{ $status->badgeColor() }}">
                                    {{ $status->label() }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('users.show', $user) }}" class="btn btn-xs btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('update', $user)
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-xs btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if ($user->status->value === 'active')
                                        <form method="POST" action="{{ route('users.suspend', $user) }}" class="d-inline"
                                              onsubmit="return confirm('Suspend {{ addslashes($user->name) }}?')">
                                            @csrf
                                            <button class="btn btn-xs btn-warning" title="Suspend">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                        </form>
                                    @elseif ($user->status->value === 'suspended')
                                        <form method="POST" action="{{ route('users.activate', $user) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-xs btn-success" title="Activate">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                                @can('delete', $user)
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline"
                                              onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-xs btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@stop
