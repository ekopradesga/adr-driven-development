@extends('adminlte::page')

@section('title', 'Create ONU')

@section('content_header')
    <h1>Create ONU</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="POST" action="{{ route('onus.store') }}">
                @csrf
                @include('onus.partials.form')
                <button class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
@stop