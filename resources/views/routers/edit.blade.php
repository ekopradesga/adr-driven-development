@extends('adminlte::page')

@section('title', 'Edit Router')

@section('content_header')
    <h1>Edit Router</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="POST" action="{{ route('routers.update', $router) }}">
                @csrf
                @method('PUT')
                @include('routers.partials.form')
                <button class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>
@stop