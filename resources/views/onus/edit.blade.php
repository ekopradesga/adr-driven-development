@extends('adminlte::page')

@section('title', 'Edit ONU')

@section('content_header')
    <h1>Edit ONU</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="POST" action="{{ route('onus.update', $onu) }}">
                @csrf
                @method('PUT')
                @include('onus.partials.form')
                <button class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
@stop