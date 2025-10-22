<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Quản lý Rút tiền
                    </h4>
                    <?php if(auth()->user()->role && auth()->user()->role->name === 'partner'): ?>
                        <a href="<?php echo e(route('withdrawals.create')); ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Tạo yêu cầu rút tiền
                        </a>
                    <?php elseif(auth()->user()->hasPermission('withdrawals.create')): ?>
                        <a href="<?php echo e(route('withdrawals.create')); ?>" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Tạo yêu cầu rút tiền
                        </a>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status" onchange="filterWithdrawals()">
                                <option value="">Tất cả trạng thái</option>
                                <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Chờ thanh toán</option>
                                <option value="assistant_completed" <?php echo e(request('status') == 'assistant_completed' ? 'selected' : ''); ?>>Trợ lý đã hoàn thành thanh toán</option>
                                <option value="partner_confirmed" <?php echo e(request('status') == 'partner_confirmed' ? 'selected' : ''); ?>>Đối tác đã xác nhận nhận tiền</option>
                            </select>
                        </div>

                        <?php if(!auth()->user()->hasRole('partner')): ?>
                        <div class="col-md-3">
                            <label for="partner_id" class="form-label">Đối tác</label>
                            <select class="form-select" id="partner_id" name="partner_id" onchange="filterWithdrawals()">
                                <option value="">Tất cả đối tác</option>
                                <?php $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($partner->id); ?>" <?php echo e(request('partner_id') == $partner->id ? 'selected' : ''); ?>>
                                        <?php echo e($partner->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="col-md-<?php echo e(auth()->user()->hasRole('partner') ? '6' : '3'); ?>">
                            <label for="search" class="form-label">Tìm kiếm</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   placeholder="Tìm theo ghi chú, tên đối tác..." 
                                   value="<?php echo e(request('search')); ?>"
                                   onkeypress="if(event.key==='Enter') filterWithdrawals()">
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-primary me-2" onclick="filterWithdrawals()">
                                <i class="fas fa-search me-1"></i>Tìm kiếm
                            </button>
                            <a href="<?php echo e(route('withdrawals.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Xóa bộ lọc
                            </a>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body text-center">
                                    <h5><?php echo e($withdrawals->where('status', 'pending')->count()); ?></h5>
                                    <small>Chờ thanh toán</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h5><?php echo e($withdrawals->where('status', 'assistant_completed')->count()); ?></h5>
                                    <small>Trợ lý đã xử lý</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5><?php echo e($withdrawals->where('status', 'partner_confirmed')->count()); ?></h5>
                                    <small>Hoàn thành</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5><?php echo e(number_format($withdrawals->sum('amount'), 0, ',', '.')); ?> VNĐ</h5>
                                    <small>Tổng tiền</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Withdrawals Table -->
                    <?php if($withdrawals->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Đối tác</th>
                                        <th>Số tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th>Ghi chú</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $withdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $withdrawal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo e(route('withdrawals.show', $withdrawal)); ?>" class="text-decoration-none">
                                                    #<?php echo e($withdrawal->id); ?>

                                                </a>
                                            </td>
                                            <td>
                                                <strong><?php echo e($withdrawal->partner->name); ?></strong>
                                                <br><small class="text-muted"><?php echo e($withdrawal->serviceProposals->count()); ?> đề xuất</small>
                                            </td>
                                            <td>
                                                <strong class="text-primary"><?php echo e($withdrawal->formatted_amount); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo e($withdrawal->getStatusBadgeClass()); ?>">
                                                    <?php echo e($withdrawal->getStatusDisplayName()); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <?php echo e($withdrawal->created_at->format('d/m/Y H:i')); ?>

                                                <br><small class="text-muted"><?php echo e($withdrawal->created_at->diffForHumans()); ?></small>
                                            </td>
                                            <td>
                                                <?php if($withdrawal->note): ?>
                                                    <span class="text-truncate d-inline-block" style="max-width: 150px;" title="<?php echo e($withdrawal->note); ?>">
                                                        <?php echo e($withdrawal->note); ?>

                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <!-- View button -->
                                                <a href="<?php echo e(route('withdrawals.show', $withdrawal)); ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <!-- Action buttons based on status and role -->
                                                <?php $__currentLoopData = $withdrawal->getAvailableActionsFor(auth()->user()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if($action['action'] === 'assistant_process'): ?>
                                                        <button type="button" class="btn btn-sm <?php echo e($action['class']); ?>" 
                                                                title="<?php echo e($action['label']); ?>"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#assistantProcessModal<?php echo e($withdrawal->id); ?>">
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                    <?php elseif($action['action'] === 'partner_confirm'): ?>
                                                        <button type="button" class="btn btn-sm <?php echo e($action['class']); ?>" 
                                                                title="<?php echo e($action['label']); ?>"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#partnerConfirmModal<?php echo e($withdrawal->id); ?>">
                                                            <i class="fas fa-handshake"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                                <!-- Delete button (only for partner and pending status) -->
                                                <?php if(auth()->user()->role && auth()->user()->role->name === 'partner' && 
                                                    $withdrawal->partner_id === auth()->id() && 
                                                    $withdrawal->status === 'pending'): ?>
                                                    <form action="<?php echo e(route('withdrawals.destroy', $withdrawal)); ?>" 
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa yêu cầu rút tiền này?')">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="pagination-container">
                            <div class="pagination-info">
                                Hiển thị <?php echo e($withdrawals->firstItem()); ?> - <?php echo e($withdrawals->lastItem()); ?> 
                                trong tổng số <?php echo e($withdrawals->total()); ?> yêu cầu rút tiền
                            </div>
                            <?php echo e($withdrawals->appends(request()->query())->links()); ?>

                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có yêu cầu rút tiền nào</h5>
                            <?php if(auth()->user()->role && auth()->user()->role->name === 'partner'): ?>
                                <a href="<?php echo e(route('withdrawals.create')); ?>" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>
                                    Tạo yêu cầu rút tiền đầu tiên
                                </a>
                            <?php elseif(auth()->user()->hasPermission('withdrawals.create')): ?>
                                <a href="<?php echo e(route('withdrawals.create')); ?>" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>
                                    Tạo yêu cầu rút tiền đầu tiên
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assistant Process Modals -->
<?php $__currentLoopData = $withdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $withdrawal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if($withdrawal->canBeProcessedByAssistant(auth()->user())): ?>
    <div class="modal fade" id="assistantProcessModal<?php echo e($withdrawal->id); ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>
                        Xác nhận thanh toán
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo e(route('withdrawals.assistant-process', $withdrawal)); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6 class="fw-bold">Yêu cầu rút tiền #<?php echo e($withdrawal->id); ?></h6>
                            <p class="text-muted mb-3">
                                Đối tác: <?php echo e($withdrawal->partner->name); ?><br>
                                Số tiền: <strong class="text-primary"><?php echo e($withdrawal->formatted_amount); ?></strong>
                            </p>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Vui lòng upload ảnh bill chuyển khoản và ghi chú (nếu có) để hoàn tất thanh toán.
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_proof_image<?php echo e($withdrawal->id); ?>" class="form-label">
                                <i class="fas fa-image me-1"></i>
                                Ảnh bill chuyển khoản <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   class="form-control" 
                                   id="payment_proof_image<?php echo e($withdrawal->id); ?>" 
                                   name="payment_proof_image" 
                                   accept="image/*"
                                   required>
                            <div class="form-text">
                                Chọn ảnh bill chuyển khoản (JPG, PNG, GIF, tối đa 5MB)
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="assistant_note<?php echo e($withdrawal->id); ?>" class="form-label">
                                <i class="fas fa-sticky-note me-1"></i>
                                Ghi chú
                            </label>
                            <textarea class="form-control" 
                                      id="assistant_note<?php echo e($withdrawal->id); ?>" 
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
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<!-- Partner Confirm Modals -->
<?php $__currentLoopData = $withdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $withdrawal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if($withdrawal->canBeConfirmedByPartner(auth()->user())): ?>
    <div class="modal fade" id="partnerConfirmModal<?php echo e($withdrawal->id); ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-handshake me-2"></i>
                        Xác nhận đã nhận tiền
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo e(route('withdrawals.partner-confirm', $withdrawal)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6 class="fw-bold">Yêu cầu rút tiền #<?php echo e($withdrawal->id); ?></h6>
                            <p class="text-muted mb-3">
                                Số tiền: <strong class="text-primary"><?php echo e($withdrawal->formatted_amount); ?></strong>
                            </p>
                        </div>
                        
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Trợ lý đã xác nhận chuyển khoản. Vui lòng kiểm tra tài khoản và xác nhận đã nhận được tiền.
                        </div>
                        
                        <div class="mb-3">
                            <label for="partner_confirmation_note<?php echo e($withdrawal->id); ?>" class="form-label">
                                <i class="fas fa-sticky-note me-1"></i>
                                Ghi chú xác nhận
                            </label>
                            <textarea class="form-control" 
                                      id="partner_confirmation_note<?php echo e($withdrawal->id); ?>" 
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
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<script>
function filterWithdrawals() {
    const status = document.getElementById('status').value;
    const partnerId = document.getElementById('partner_id')?.value || '';
    const search = document.getElementById('search').value;
    
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (partnerId) params.append('partner_id', partnerId);
    if (search) params.append('search', search);
    
    const url = '<?php echo e(route("withdrawals.index")); ?>' + (params.toString() ? '?' + params.toString() : '');
    window.location.href = url;
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/bl555pm.com/resources/views/withdrawals/index.blade.php ENDPATH**/ ?>