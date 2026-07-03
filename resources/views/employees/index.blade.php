@extends('adminlte::page')

@section('title', 'Employees')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Employees</h1>
        @can('create', \App\Models\Employee::class)
            <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">New Employee</a>
        @endcan
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="GET" action="{{ route('employees.index') }}" class="form-inline flex-wrap" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Name, email, phone..." value="{{ request('search') }}">
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
                    <tr><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr>
                            <td><a href="{{ route('employees.show', $employee) }}">{{ $employee->name }}</a></td>
                            <td>{{ $employee->email ?: '—' }}</td>
                            <td>{{ $employee->phone ?: '—' }}</td>
                            <td><span class="badge badge-{{ $employee->status->badgeColor() }}">{{ $employee->status->label() }}</span></td>
                            <td class="text-right"><a href="{{ route('employees.show', $employee) }}" class="btn btn-xs btn-info">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No employees found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-end">{{ $employees->links() }}</div>
@stop