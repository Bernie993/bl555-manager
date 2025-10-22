@extends('layouts.app')

@section('title', 'Tạo yêu cầu rút tiền')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-plus-circle me-2"></i>Tạo yêu cầu rút tiền</h2>
                <a href="{{ route('withdrawals.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('withdrawals.store') }}" method="POST">
                        @csrf
                        
                        <h5>Chọn đề xuất đã hoàn thành</h5>
                        
                        @if($serviceProposals->count() > 0)
                            @foreach($serviceProposals as $proposal)
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="service_proposals[]" value="{{ $proposal->id }}" id="proposal_{{ $proposal->id }}">
                                <label class="form-check-label" for="proposal_{{ $proposal->id }}">
                                    <strong>{{ $proposal->service_name }}</strong><br>
                                    <small class="text-muted">
                                        SL: {{ $proposal->quantity }}<br>
                                        NCC: {{ $proposal->supplier_name }}
                                    </small>
                                </label>
                                <div class="mt-2">
                                    <label>Số tiền rút (VND):</label>
                                    <input type="number" name="amounts[{{ $proposal->id }}]" class="form-control" min="1" step="1000" placeholder="Nhập số tiền">
                                    <small class="text-muted">Tối đa: {{ number_format($proposal->budget->amount ?? 0) }} VND</small>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Không có đề xuất nào đã hoàn thành để rút tiền.
                            </div>
                        @endif
                        
                        <div class="mb-3">
                            <label for="note" class="form-label">Ghi chú</label>
                            <textarea name="note" id="note" class="form-control" rows="3" placeholder="Nhập ghi chú (tùy chọn)"></textarea>
                        </div>
                        
                        @if($serviceProposals->count() > 0)
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Tạo yêu cầu rút tiền
                            </button>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
