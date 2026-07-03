@extends('adminlte::page')

@section('title', 'Cluster ' . $cluster->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div><h1 class="mb-0">{{ $cluster->name }}</h1><small class="text-muted">{{ $cluster->code }}</small></div>
        <div class="d-flex align-items-center" style="gap:.5rem">
            @can('activate', $cluster)
                @if (!$cluster->isActive())
                    <form method="POST" action="{{ route('clusters.activate', $cluster) }}">@csrf<button class="btn btn-sm btn-success">Activate</button></form>
                @endif
            @endcan
            @can('inactivate', $cluster)
                @if ($cluster->isActive())
                    <form method="POST" action="{{ route('clusters.inactivate', $cluster) }}">@csrf<button class="btn btn-sm btn-dark">Inactivate</button></form>
                @endif
            @endcan
            @can('update', $cluster)
                <a href="{{ route('clusters.edit', $cluster) }}" class="btn btn-sm btn-warning">Edit</a>
            @endcan
            <a href="{{ route('clusters.index', $cluster) }}" class="btn btn-sm btn-secondary">Back</a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary card-outline"><div class="card-body">
                <p><span class="badge badge-{{ $cluster->status->badgeColor() }}">{{ $cluster->status->label() }}</span></p>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Code</dt><dd class="col-sm-8">{{ $cluster->code }}</dd>
                    <dt class="col-sm-4">Areas</dt><dd class="col-sm-8">{{ $cluster->serviceAreas->count() }}</dd>
                    <dt class="col-sm-4">Customers</dt><dd class="col-sm-8">{{ $cluster->customers->count() }}</dd>
                </dl>
            </div></div>
        </div>
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title mb-0">Service Areas</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-light"><tr><th>Code</th><th>Name</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                        <tbody>
                            @forelse ($cluster->serviceAreas as $serviceArea)
                                <tr>
                                    <td>{{ $serviceArea->code }}</td>
                                    <td>{{ $serviceArea->name }}</td>
                                    <td>{{ $serviceArea->status->label() }}</td>
                                    <td class="text-right"><a class="btn btn-xs btn-info" href="{{ route('service-areas.show', $serviceArea) }}">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No service areas linked.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop