@extends('adminlte::page')

@section('title', 'Create Router')

@section('content_header')
    <h1>Create Router</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <form method="POST" action="{{ route('routers.store') }}">
                @csrf
                @include('routers.partials.form')
                <button class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
@stop