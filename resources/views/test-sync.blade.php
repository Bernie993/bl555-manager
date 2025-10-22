@extends('layouts.app')

@section('title', 'Test Cloudflare Sync')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Test Cloudflare Sync Page</h5>
                </div>
                <div class="card-body">
                    <p>This is a simple test page to verify the routing and view rendering works.</p>
                    <p>Current user: {{ auth()->user()->name ?? 'Not authenticated' }}</p>
                    <p>User role: {{ auth()->user()->role->name ?? 'No role' }}</p>
                    <p>Timestamp: {{ now() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
