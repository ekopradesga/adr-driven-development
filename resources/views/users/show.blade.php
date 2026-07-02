@extends('adminlte::page')

@section('title', $user->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $user->name }}</h1>
        <div>
            @can('update', $user)
                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                @if ($user->status->value === 'active' && $user->id !== auth()->id())
                    <form method="POST" action="{{ route('users.suspend', $user) }}" class="d-inline"
                          onsubmit="return confirm('Suspend {{ addslashes($user->name) }}?')">
                        @csrf
                        <button class="btn btn-sm btn-warning"><i class="fas fa-pause mr-1"></i> Suspend</button>
                    </form>
                @elseif ($user->status->value === 'suspended')
                    <form method="POST" action="{{ route('users.activate', $user) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-success"><i class="fas fa-play mr-1"></i> Activate</button>
                    </form>
                @endif
            @endcan
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary ml-1">
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
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        {{-- User Details --}}
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-body box-profile">
                    <h3 class="profile-username text-center">{{ $user->name }}</h3>
                    <p class="text-muted text-center">{{ $user->email }}</p>
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Status</b>
                            <span class="float-right">
                                <span class="badge badge-{{ $user->status->badgeColor() }}">
                                    {{ $user->status->label() }}
                                </span>
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>Roles</b>
                            <span class="float-right">{{ $user->roles->count() }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>Joined</b>
                            <span class="float-right">{{ $user->created_at->format('d M Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Roles --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shield-alt mr-2"></i>Assigned Roles</h3>
                </div>
                <div class="card-body">
                    @if ($user->roles->isEmpty())
                        <p class="text-muted mb-0">No roles assigned.</p>
                    @else
                        @foreach ($user->roles as $role)
                            <div class="mb-3">
                                <span class="badge badge-primary badge-lg mr-2">{{ $role->name }}</span>
                                <small class="text-muted">{{ $role->description }}</small>
                                <div class="mt-1">
                                    @foreach ($role->permissions as $permission)
                                        <span class="badge badge-light border mr-1 mb-1">{{ $permission->key }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <hr>
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Direct Permissions --}}
            @if ($user->directPermissions->isNotEmpty())
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-key mr-2"></i>Direct Permissions</h3>
                    </div>
                    <div class="card-body">
                        @foreach ($user->directPermissions as $permission)
                            <span class="badge badge-info mr-1 mb-1">{{ $permission->key }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop
