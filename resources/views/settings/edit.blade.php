@extends('adminlte::page')

@section('title', 'Edit Setting')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Edit Setting</h1>
        <a href="{{ route('settings.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the errors below.</strong>
        </div>
    @endif

    @php
        $entry = $setting->registryEntry;
        $dataType = $entry?->data_type;
        $typedValue = old('value', is_array($setting->getTypedValue()) ? json_encode($setting->getTypedValue(), JSON_PRETTY_PRINT) : $setting->value);
    @endphp

    <form method="POST" action="{{ route('settings.update', $setting) }}">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Key</label>
                    <input type="text" class="form-control" value="{{ $setting->key }}" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" class="form-control" value="{{ $entry?->category?->label ?? '-' }}" readonly>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Data Type</label>
                    <input type="text" class="form-control" value="{{ $dataType?->label() ?? '-' }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Scope</label>
                    <input type="text" class="form-control" value="{{ $setting->scope?->label() ?? '-' }}" readonly>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Scope ID</label>
                    <input type="text" class="form-control" value="{{ $setting->scope_id }}" readonly>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="value">Value</label>
                    @if ($dataType === \App\Enums\SettingDataType::Boolean)
                        <select id="value" name="value" class="form-control @error('value') is-invalid @enderror" {{ $canUpdate ? '' : 'disabled' }}>
                            <option value="">(null)</option>
                            <option value="1" {{ (string) $typedValue === '1' || $typedValue === true ? 'selected' : '' }}>true</option>
                            <option value="0" {{ (string) $typedValue === '0' || $typedValue === false ? 'selected' : '' }}>false</option>
                        </select>
                    @elseif ($dataType === \App\Enums\SettingDataType::Json)
                        <textarea id="value" name="value" rows="8"
                                  class="form-control @error('value') is-invalid @enderror"
                                  {{ $canUpdate ? '' : 'readonly' }}>{{ $typedValue }}</textarea>
                    @else
                        <input id="value" type="text" name="value"
                               class="form-control @error('value') is-invalid @enderror"
                               value="{{ $typedValue }}"
                               {{ $canUpdate ? '' : 'readonly' }}>
                    @endif
                    @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if ($entry?->default_value !== null)
                        <small class="form-text text-muted">Default value: {{ $entry->default_value }}</small>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="is_active">Active Status</label>
                    <select id="is_active" name="is_active" class="form-control @error('is_active') is-invalid @enderror" {{ $canUpdate ? '' : 'disabled' }}>
                        <option value="1" {{ old('is_active', $setting->is_active ? '1' : '0') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active', $setting->is_active ? '1' : '0') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        @if ($canUpdate)
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('settings.index') }}" class="btn btn-secondary ml-2">Cancel</a>
        @else
            <div class="alert alert-warning mb-0">You have view access only. Updating settings requires the <code>settings.update</code> permission.</div>
        @endif
    </form>
@stop
