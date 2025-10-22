@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Chi tiết yêu cầu rút tiền #{{ $withdrawal->id }}
                    </h4>
                    <span class="badge {{ $withdrawal->getStatusBadgeClass() }} fs-6">
                        {{ $withdrawal->getStatusDisplayName() }}
                    </span>
                </div>

                <div class="card-body">
                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted">Thông tin cơ bản</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Đối tác:</strong></td>
                                    <td>{{ $withdrawal->partner->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Số tiền:</strong></td>
                                    <td class="text-primary fs-5 fw-bold">{{ $withdrawal->formatted_amount }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày tạo:</strong></td>
                                    <td>{{ $withdrawal->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Số đề xuất:</strong></td>
                                    <td>{{ $withdrawal->serviceProposals->count() }} đề xuất</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Ghi chú từ Partner</h6>
                            @if($withdrawal->note)
                                <div class="alert alert-light">
                                    <i class="fas fa-quote-left me-2"></i>
                                    {{ $withdrawal->note }}
                                </div>
                            @else
                                <p class="text-muted fst-italic">Không có ghi chú</p>
                            @endif
                        </div>
                    </div>

                    <!-- Service Proposals -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-list me-2"></i>
                            Danh sách đề xuất ({{ $withdrawal->serviceProposals->count() }})
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Dịch vụ</th>
                                        <th>Nhà cung cấp</th>
                                        <th>Số tiền đề xuất</th>
                                        <th>Số tiền rút</th>
                                        <th>Ngân sách</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($withdrawal->serviceProposals as $proposal)
                                        <tr>
                                            <td>
                                                <a href="{{ route('service-proposals.show', $proposal) }}" class="text-decoration-none">
                                                    <strong>{{ $proposal->service_name }}</strong>
                                                </a>
                                                <br><small class="text-muted">SL: {{ $proposal->quantity }}</small>
                                            </td>
                                            <td>{{ $proposal->supplier_name }}</td>
                                            <td>
                                                <strong class="text-primary">{{ $proposal->formatted_amount }}</strong>
                                            </td>
                                            <td>
                                                <strong class="text-success">{{ number_format($proposal->pivot->amount, 0, ',', '.') }} VNĐ</strong>
                                            </td>
                                            <td>
                                                @if($proposal->budget)
                                                    {{ $proposal->budget->name }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" class="text-end">Tổng tiền rút:</th>
                                        <th class="text-success">{{ $withdrawal->formatted_amount }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Processing Information -->
                    @if($withdrawal->status !== 'pending')
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-history me-2"></i>
                                Lịch sử xử lý
                            </h6>

                            <!-- Assistant Processing -->
                            @if($withdrawal->assistant_processed_at)
                                <div class="card mb-3">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Trợ lý đã xử lý thanh toán
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Trợ lý:</strong> {{ $withdrawal->assistantProcessor->name }}</p>
                                                <p><strong>Thời gian:</strong> {{ $withdrawal->assistant_processed_at->format('d/m/Y H:i') }}</p>
                                                @if($withdrawal->assistant_note)
                                                    <p><strong>Ghi chú:</strong> {{ $withdrawal->assistant_note }}</p>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                @if($withdrawal->payment_proof_image)
                                                    <p><strong>Ảnh bill chuyển khoản:</strong></p>
                                                    <img src="{{ asset('storage/' . $withdrawal->payment_proof_image) }}" 
                                                         class="img-fluid rounded" 
                                                         style="max-height: 200px; cursor: pointer;"
                                                         onclick="showImageModal('{{ asset('storage/' . $withdrawal->payment_proof_image) }}')"
                                                         alt="Bill chuyển khoản">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Partner Confirmation -->
                            @if($withdrawal->partner_confirmed_at)
                                <div class="card mb-3">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-handshake me-2"></i>
                                            Đối tác đã xác nhận nhận tiền
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Đối tác:</strong> {{ $withdrawal->partnerConfirmer->name }}</p>
                                        <p><strong>Thời gian:</strong> {{ $withdrawal->partner_confirmed_at->format('d/m/Y H:i') }}</p>
                                        @if($withdrawal->partner_confirmation_note)
                                            <p><strong>Ghi chú:</strong> {{ $withdrawal->partner_confirmation_note }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Action Card -->
            <div class="card mb-4 sticky-top" style="top: 20px;">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>
                        Thao tác
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Action buttons based on status and role -->
                    @foreach($withdrawal->getAvailableActionsFor(auth()->user()) as $action)
                        @if($action['action'] === 'assistant_process')
                            <button type="button" class="btn {{ $action['class'] }} w-100 mb-2" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#assistantProcessModal">
                                <i class="fas fa-check-circle me-1"></i>
                                {{ $action['label'] }}
                            </button>
                        @elseif($action['action'] === 'partner_confirm')
                            <button type="button" class="btn {{ $action['class'] }} w-100 mb-2" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#partnerConfirmModal">
                                <i class="fas fa-handshake me-1"></i>
                                {{ $action['label'] }}
                            </button>
                        @endif
                    @endforeach

                    <!-- Delete button (only for partner and pending status) -->
                    @if(auth()->user()->hasRole('partner') && 
                        $withdrawal->partner_id === auth()->id() && 
                        $withdrawal->status === 'pending')
                        <form action="{{ route('withdrawals.destroy', $withdrawal) }}" 
                              method="POST" class="w-100 mb-2"
                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa yêu cầu rút tiền này?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash me-1"></i>
                                Xóa yêu cầu
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('withdrawals.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-arrow-left me-1"></i>
                        Quay lại danh sách
                    </a>
                </div>
            </div>

            <!-- Status Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-timeline me-2"></i>
                        Tiến trình
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Step 1: Created -->
                        <div class="timeline-item completed">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Tạo yêu cầu rút tiền</h6>
                                <small class="text-muted">{{ $withdrawal->created_at->format('d/m/Y H:i') }}</small>
                                <p class="mb-0 small">Bởi: {{ $withdrawal->partner->name }}</p>
                            </div>
                        </div>

                        <!-- Step 2: Assistant Process -->
                        <div class="timeline-item {{ $withdrawal->assistant_processed_at ? 'completed' : ($withdrawal->status === 'pending' ? 'active' : '') }}">
                            <div class="timeline-marker {{ $withdrawal->assistant_processed_at ? 'bg-info' : ($withdrawal->status === 'pending' ? 'bg-warning' : 'bg-light') }}"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Trợ lý xử lý thanh toán</h6>
                                @if($withdrawal->assistant_processed_at)
                                    <small class="text-muted">{{ $withdrawal->assistant_processed_at->format('d/m/Y H:i') }}</small>
                                    <p class="mb-0 small">Bởi: {{ $withdrawal->assistantProcessor->name }}</p>
                                @else
                                    <small class="text-muted">Chờ xử lý</small>
                                @endif
                            </div>
                        </div>

                        <!-- Step 3: Partner Confirm -->
                        <div class="timeline-item {{ $withdrawal->partner_confirmed_at ? 'completed' : ($withdrawal->status === 'assistant_completed' ? 'active' : '') }}">
                            <div class="timeline-marker {{ $withdrawal->partner_confirmed_at ? 'bg-success' : ($withdrawal->status === 'assistant_completed' ? 'bg-warning' : 'bg-light') }}"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Đối tác xác nhận nhận tiền</h6>
                                @if($withdrawal->partner_confirmed_at)
                                    <small class="text-muted">{{ $withdrawal->partner_confirmed_at->format('d/m/Y H:i') }}</small>
                                    <p class="mb-0 small">Bởi: {{ $withdrawal->partnerConfirmer->name }}</p>
                                @else
                                    <small class="text-muted">Chờ xác nhận</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assistant Process Modal -->
@if($withdrawal->canBeProcessedByAssistant(auth()->user()))
<div class="modal fade" id="assistantProcessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>
                    Xác nhận thanh toán
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('withdrawals.assistant-process', $withdrawal) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Vui lòng upload ảnh bill chuyển khoản để hoàn tất thanh toán cho đối tác.
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_proof_image" class="form-label">
                            <i class="fas fa-image me-1"></i>
                            Ảnh bill chuyển khoản <span class="text-danger">*</span>
                        </label>
                        <input type="file" 
                               class="form-control" 
                               id="payment_proof_image" 
                               name="payment_proof_image" 
                               accept="image/*"
                               required>
                        <div class="form-text">
                            Chọn ảnh bill chuyển khoản (JPG, PNG, GIF, tối đa 5MB)
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="assistant_note" class="form-label">
                            <i class="fas fa-sticky-note me-1"></i>
                            Ghi chú
                        </label>
                        <textarea class="form-control" 
                                  id="assistant_note" 
                                  name="assistant_note" 
                                  rows="3"
                                  placeholder="Ghi chú về việc chuyển khoản (không bắt buộc)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Hủy
                    </button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-check-circle me-1"></i>
                        Xác nhận đã thanh toán
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Partner Confirm Modal -->
@if($withdrawal->canBeConfirmedByPartner(auth()->user()))
<div class="modal fade" id="partnerConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-handshake me-2"></i>
                    Xác nhận đã nhận tiền
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('withdrawals.partner-confirm', $withdrawal) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Trợ lý đã xác nhận chuyển khoản. Vui lòng kiểm tra tài khoản và xác nhận đã nhận được tiền.
                    </div>
                    
                    <div class="mb-3">
                        <label for="partner_confirmation_note" class="form-label">
                            <i class="fas fa-sticky-note me-1"></i>
                            Ghi chú xác nhận
                        </label>
                        <textarea class="form-control" 
                                  id="partner_confirmation_note" 
                                  name="partner_confirmation_note" 
                                  rows="3"
                                  placeholder="Ghi chú xác nhận đã nhận tiền (không bắt buộc)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>
                        Hủy
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-handshake me-1"></i>
                        Xác nhận đã nhận tiền
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ảnh bill chuyển khoản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" alt="Bill chuyển khoản">
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    padding-left: 10px;
}

.timeline-item.completed .timeline-marker {
    box-shadow: 0 0 0 2px #28a745;
}

.timeline-item.active .timeline-marker {
    box-shadow: 0 0 0 2px #ffc107;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 2px #ffc107; }
    50% { box-shadow: 0 0 0 6px rgba(255, 193, 7, 0.3); }
    100% { box-shadow: 0 0 0 2px #ffc107; }
}
</style>

<script>
function showImageModal(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}
</script>
@endsection
