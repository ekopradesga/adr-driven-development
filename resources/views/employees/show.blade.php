@extends('adminlte::page')

@section('title', 'Employee ' . $employee->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div><h1 class="mb-0">{{ $employee->name }}</h1><small class="text-muted">Employee profile</small></div>
        <div>
            @can('update', $employee)
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-warning">Edit</a>
            @endcan
            <a href="{{ route('employees.index') }}" class="btn btn-sm btn-secondary">Back</a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-outline card-primary"><div class="card-body">
                <p><span class="badge badge-{{ $employee->status->badgeColor() }}">{{ $employee->status->label() }}</span></p>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $employee->email ?: '—' }}</dd>
                    <dt class="col-sm-4">Phone</dt><dd class="col-sm-8">{{ $employee->phone ?: '—' }}</dd>
                    <dt class="col-sm-4">User</dt><dd class="col-sm-8">{{ $employee->user?->name ?: '—' }}</dd>
                </dl>
            </div></div>
        </div>
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title mb-0">Collection Tasks</h3></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-light"><tr><th>Task</th><th>Status</th><th>Customer</th><th class="text-right">Actions</th></tr></thead>
                        <tbody>
                            @forelse ($employee->collectionTasks as $task)
                                <tr>
                                    <td><a href="{{ route('collection-tasks.show', $task) }}">Task #{{ $task->id }}</a></td>
                                    <td>{{ $task->status->label() }}</td>
                                    <td>{{ $task->customer?->customer_number }}</td>
                                    <td class="text-right"><a class="btn btn-xs btn-info" href="{{ route('collection-tasks.show', $task) }}">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No tasks assigned.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop