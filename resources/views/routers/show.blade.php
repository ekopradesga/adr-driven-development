@extends('adminlte::page')

@section('title', $router->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $router->name }}</h1>
        <div>
            @can('update', $router)
                @if (!$router->isRetired())
                    <a href="{{ route('routers.edit', $router) }}" class="btn btn-warning btn-sm">Edit</a>
                @endif
            @endcan
        </div>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Code</dt><dd class="col-sm-9">{{ $router->router_code }}</dd>
                <dt class="col-sm-3">Type</dt><dd class="col-sm-9">{{ $router->router_type->label() }}</dd>
                <dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ $router->status->label() }}</dd>
                <dt class="col-sm-3">IP Address</dt><dd class="col-sm-9">{{ $router->ip_address }}</dd>
                <dt class="col-sm-3">Parent</dt><dd class="col-sm-9">{{ $router->parent?->name ?? 'None' }}</dd>
                <dt class="col-sm-3">Children</dt><dd class="col-sm-9">{{ $router->children->count() }}</dd>
            </dl>
        </div>
    </div>
@stop