@extends('adminlte::page')

@section('title', $permission->key)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><code>{{ $permission->key }}</code></h1>
        <a href="{{ route('permissions.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-5">
            <div class="card card-secondary card-outline">
                <div class="card-header"><h3 class="card-title">Permission Details</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Key</dt>
                        <dd class="col-7"><code>{{ $permission->key }}</code></dd>
                        <dt class="col-5">Name</dt>
                        <dd class="col-7">{{ $permission->name }}</dd>
                        <dt class="col-5">Category</dt>
                        <dd class="col-7">
                            <span class="badge badge-secondary">{{ $permission->category }}</span>
                        </dd>
                        <dt class="col-5">Status</dt>
                        <dd class="col-7">
                            <span class="badge badge-{{ $permission->status->badgeColor() }}">
                                {{ $permission->status->label() }}
                            </span>
                        </dd>
                    </dl>
                    @if ($permission->description)
                        <hr>
                        <p class="mb-0 text-muted small">{{ $permission->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shield-alt mr-2"></i>Assigned to Roles</h3>
                </div>
                <div class="card-body p-0">
                    @if ($permission->roles->isEmpty())
                        <p class="text-muted p-3 mb-0">This permission has not been assigned to any role.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($permission->roles as $role)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('roles.show', $role) }}">{{ $role->name }}</a>
                                    <span class="badge badge-{{ $role->status->badgeColor() }}">
                                        {{ $role->status->label() }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop
