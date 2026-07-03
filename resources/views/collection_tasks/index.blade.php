@extends('adminlte::page')

@section('title', 'Collection Tasks')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Collection Tasks</h1>
        @can('create', \App\Models\CollectionTask::class)
            <a href="{{ route('collection-tasks.create') }}" class="btn btn-primary btn-sm">New Task</a>
        @endcan
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="GET" action="{{ route('collection-tasks.index') }}" class="form-inline flex-wrap" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Customer number, name, address..." value="{{ request('search') }}">
                <select name="status" class="form-control form-control-sm">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-secondary">Search</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="thead-light">
                    <tr><th>Task</th><th>Customer</th><th>Collector</th><th>Status</th><th>Visit</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse ($collectionTasks as $collectionTask)
                        <tr>
                            <td><a href="{{ route('collection-tasks.show', $collectionTask) }}">#{{ $collectionTask->id }}</a></td>
                            <td>{{ $collectionTask->customer?->customer_number }}<small class="text-muted d-block">{{ $collectionTask->customer?->name }}</small></td>
                            <td>{{ $collectionTask->employee?->name ?: '—' }}</td>
                            <td><span class="badge badge-{{ $collectionTask->status->badgeColor() }}">{{ $collectionTask->status->label() }}</span></td>
                            <td>{{ $collectionTask->scheduled_for?->format('d M Y H:i') ?: '—' }}</td>
                            <td class="text-right"><a href="{{ route('collection-tasks.show', $collectionTask) }}" class="btn btn-xs btn-info">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No collection tasks found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end">{{ $collectionTasks->links() }}</div>
@stop