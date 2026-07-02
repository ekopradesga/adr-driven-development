@extends('adminlte::page')

@section('title', $role->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $role->name }}</h1>
        <div>
            @can('update', $role)
                <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
            @endcan
            <a href="{{ route('roles.index') }}" class="btn btn-sm btn-secondary ml-1">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        {{-- Role Info --}}
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Name</dt>
                        <dd class="col-7">{{ $role->name }}</dd>
                        <dt class="col-5">Slug</dt>
                        <dd class="col-7"><code>{{ $role->slug }}</code></dd>
                        <dt class="col-5">Status</dt>
                        <dd class="col-7">
                            <span class="badge badge-{{ $role->status->badgeColor() }}">
                                {{ $role->status->label() }}
                            </span>
                        </dd>
                        <dt class="col-5">Users</dt>
                        <dd class="col-7">{{ $role->users->count() }}</dd>
                    </dl>
                    @if ($role->description)
                        <hr>
                        <p class="mb-0 text-muted small">{{ $role->description }}</p>
                    @endif
                </div>
            </div>

            {{-- Assigned Users --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users mr-2"></i>Assigned Users</h3>
                </div>
                <div class="card-body p-0">
                    @if ($role->users->isEmpty())
                        <p class="text-muted p-3 mb-0">No users assigned.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($role->users as $user)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('users.show', $user) }}">{{ $user->name }}</a>
                                    <span class="badge badge-{{ $user->status->badgeColor() }}">
                                        {{ $user->status->label() }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        {{-- Permissions --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-key mr-2"></i>
                        Permissions
                        <span class="badge badge-secondary ml-2">
                            {{ $assignedIds|length ?? count($assignedIds) }}
                        </span>
                    </h3>
                </div>
                <div class="card-body">
                    @foreach ($allPermissions as $category => $permissions)
                        <h6 class="text-uppercase text-muted font-weight-bold mt-3 mb-2 border-bottom pb-1">
                            {{ $category }}
                        </h6>
                        <div class="row mb-2">
                            @foreach ($permissions as $permission)
                                @php $assigned = in_array($permission->id, $assignedIds) @endphp
                                <div class="col-md-4 mb-1">
                                    <span class="badge {{ $assigned ? 'badge-success' : 'badge-light border text-muted' }}">
                                        {{ $permission->key }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@stop
