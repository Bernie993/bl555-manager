@extends('layouts.app')

@section('title', 'Debug AJAX')

@section('content')
<div class="container">
    <h1>Debug AJAX Calls</h1>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Test Buttons</div>
                <div class="card-body">
                    <button class="btn btn-primary mb-2" onclick="testDebugInfo()">Test Debug Info</button><br>
                    <button class="btn btn-success mb-2" onclick="testAjax()">Test AJAX</button><br>
                    <button class="btn btn-warning mb-2" onclick="test301Status()">Test 301 Status</button><br>
                    <button class="btn btn-info mb-2" onclick="testSync()">Test Sync</button>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Results</div>
                <div class="card-body">
                    <div id="results" style="max-height: 400px; overflow-y: auto;">
                        <p class="text-muted">Click buttons to test...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function logResult(title, data) {
    const results = document.getElementById('results');
    const time = new Date().toLocaleTimeString();
    results.innerHTML += `
        <div class="border-bottom mb-2 pb-2">
            <strong>${time} - ${title}:</strong><br>
            <pre class="small">${JSON.stringify(data, null, 2)}</pre>
        </div>
    `;
    results.scrollTop = results.scrollHeight;
}

function testDebugInfo() {
    console.log('Testing debug info...');
    fetch('/debug-info')
        .then(response => response.json())
        .then(data => {
            console.log('Debug info:', data);
            logResult('Debug Info', data);
        })
        .catch(error => {
            console.error('Debug info error:', error);
            logResult('Debug Info Error', {error: error.message});
        });
}

function testAjax() {
    console.log('Testing AJAX...');
    fetch('/test-ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        console.log('AJAX test:', data);
        logResult('AJAX Test', data);
    })
    .catch(error => {
        console.error('AJAX test error:', error);
        logResult('AJAX Test Error', {error: error.message});
    });
}

function test301Status() {
    console.log('Testing 301 status...');
    fetch('/websites/1/301-status', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        console.log('301 status:', data);
        logResult('301 Status Test', data);
    })
    .catch(error => {
        console.error('301 status error:', error);
        logResult('301 Status Error', {error: error.message});
    });
}

function testSync() {
    console.log('Testing sync...');
    fetch('{{ route("websites.sync-from-cloudflare") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        console.log('Sync test:', data);
        logResult('Sync Test', data);
    })
    .catch(error => {
        console.error('Sync test error:', error);
        logResult('Sync Test Error', {error: error.message});
    });
}
</script>
@endsection
