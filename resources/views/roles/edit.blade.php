@extends('adminlte::page')

@section('title', 'Edit Role: ' . $role->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Edit Role: <span class="text-muted">{{ $role->name }}</span></h1>
        <a href="{{ route('roles.show', $role) }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
@stop

@section('content')
    <form method="POST" action="{{ route('roles.update', $role) }}">
        @csrf
        @method('PUT')
        <div class="row">
            {{-- Role Details --}}
            <div class="col-md-5">
                <div class="card card-warning card-outline">
                    <div class="card-header"><h3 class="card-title">Role Details</h3></div>
                    <div class="card-body">

                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input id="name" type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $role->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug <span class="text-danger">*</span></label>
                            <input id="slug" type="text" name="slug"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $role->slug) }}" required>
                            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3"
                                      class="form-control">{{ old('description', $role->description) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}"
                                        {{ old('status', $role->status->value) === $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Permission Assignment --}}
            <div class="col-md-7">
                <div class="card card-secondary card-outline">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Permissions</h3>
                        <div>
                            <button type="button" class="btn btn-xs btn-outline-secondary" id="selectAll">Select All</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary" id="clearAll">Clear All</button>
                        </div>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        @foreach ($allPermissions as $category => $permissions)
                            <h6 class="text-uppercase text-muted font-weight-bold mt-3 mb-2 border-bottom pb-1">
                                {{ $category }}
                            </h6>
                            <div class="row">
                                @foreach ($permissions as $permission)
                                    <div class="col-md-6 mb-1">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input perm-check"
                                                   id="perm_{{ $permission->id }}"
                                                   name="permissions[]"
                                                   value="{{ $permission->id }}"
                                                   {{ in_array($permission->id, old('permissions', $assignedIds)) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="perm_{{ $permission->id }}"
                                                   title="{{ $permission->name }}">
                                                <code class="small">{{ $permission->key }}</code>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-warning">Save Changes</button>
                <a href="{{ route('roles.show', $role) }}" class="btn btn-secondary ml-2">Cancel</a>
            </div>
        </div>
    </form>
@stop

@section('js')
<script>
    document.getElementById('selectAll').addEventListener('click', function () {
        document.querySelectorAll('.perm-check').forEach(cb => cb.checked = true);
    });
    document.getElementById('clearAll').addEventListener('click', function () {
        document.querySelectorAll('.perm-check').forEach(cb => cb.checked = false);
    });
</script>
@endsection
