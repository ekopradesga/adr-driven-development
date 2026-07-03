@extends('adminlte::page')

@section('title', $onu->onu_sn)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $onu->onu_sn }}</h1>
        <div class="d-flex flex-wrap" style="gap:.5rem">
            @can('update', $onu)
                @if (!$onu->isRetired())
                    <a href="{{ route('onus.edit', $onu) }}" class="btn btn-warning btn-sm">Edit</a>
                @endif
            @endcan
        </div>
    </div>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Serial</dt><dd class="col-sm-9">{{ $onu->onu_sn }}</dd>
                <dt class="col-sm-3">Status</dt><dd class="col-sm-9"><span class="badge badge-{{ $onu->status->badgeColor() }}">{{ $onu->status->label() }}</span></dd>
                <dt class="col-sm-3">OLT</dt><dd class="col-sm-9">{{ $onu->olt?->name ?? 'None' }}</dd>
                <dt class="col-sm-3">FAT</dt><dd class="col-sm-9">{{ $onu->fat?->name ?? 'None' }}</dd>
                <dt class="col-sm-3">PON Port</dt><dd class="col-sm-9">{{ $onu->pon_port }}</dd>
                <dt class="col-sm-3">ONU Index</dt><dd class="col-sm-9">{{ $onu->onu_index }}</dd>
                <dt class="col-sm-3">Model</dt><dd class="col-sm-9">{{ $onu->model ?? 'None' }}</dd>
                <dt class="col-sm-3">Customer Label</dt><dd class="col-sm-9">{{ $onu->customer_label ?? 'None' }}</dd>
                <dt class="col-sm-3">RX Power</dt><dd class="col-sm-9">{{ $onu->rx_power_dbm ?? 'None' }}</dd>
                <dt class="col-sm-3">TX Power</dt><dd class="col-sm-9">{{ $onu->tx_power_dbm ?? 'None' }}</dd>
                <dt class="col-sm-3">Last Seen</dt><dd class="col-sm-9">{{ $onu->last_seen_at ?? 'None' }}</dd>
                <dt class="col-sm-3">Provisioned</dt><dd class="col-sm-9">{{ $onu->provisioned_at ?? 'None' }}</dd>
                <dt class="col-sm-3">Subscriptions</dt><dd class="col-sm-9">{{ $onu->subscriptions->count() }}</dd>
            </dl>

            <div class="mt-3 d-flex flex-wrap" style="gap:.5rem">
                @can('activate', $onu)
                    @if (!$onu->isRetired() && !$onu->isActive())
                        <form method="POST" action="{{ route('onus.activate', $onu) }}">
                            @csrf
                            <button class="btn btn-success btn-sm">Activate</button>
                        </form>
                    @endif
                @endcan
                @can('offline', $onu)
                    @if ($onu->isActive())
                        <form method="POST" action="{{ route('onus.offline', $onu) }}">
                            @csrf
                            <button class="btn btn-warning btn-sm">Mark Offline</button>
                        </form>
                    @endif
                @endcan
                @can('suspend', $onu)
                    @if (!$onu->isRetired() && ($onu->isActive() || $onu->isOffline()))
                        <form method="POST" action="{{ route('onus.suspend', $onu) }}">
                            @csrf
                            <button class="btn btn-info btn-sm">Suspend</button>
                        </form>
                    @endif
                @endcan
                @can('retire', $onu)
                    @if (!$onu->isRetired())
                        <form method="POST" action="{{ route('onus.retire', $onu) }}">
                            @csrf
                            <button class="btn btn-danger btn-sm">Retire</button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>
@stop