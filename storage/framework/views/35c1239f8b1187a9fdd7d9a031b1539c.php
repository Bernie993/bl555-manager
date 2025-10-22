<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-plus me-2"></i>
                        Tạo yêu cầu rút tiền
                    </h4>
                </div>

                <div class="card-body">
                    <form action="<?php echo e(route('withdrawals.store')); ?>" method="POST" id="withdrawalForm">
                        <?php echo csrf_field(); ?>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Service Proposals Selection -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-list me-2"></i>
                                            Chọn đề xuất đã hoàn thành
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Chọn các đề xuất đã hoàn thành thanh toán và nhập số tiền muốn rút từ mỗi đề xuất.
                                        </div>

                                        <?php if($serviceProposals->count() > 0): ?>
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="50">
                                                                <input type="checkbox" id="selectAll" onchange="toggleAllProposals()">
                                                            </th>
                                                            <th>Dịch vụ</th>
                                                            <th>Số tiền đề xuất</th>
                                                            <th>Ngân sách</th>
                                                            <th>Số tiền rút</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $__currentLoopData = $serviceProposals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proposal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr class="<?php echo e(($proposal->is_fully_withdrawn ?? false) ? 'table-secondary' : ''); ?>">
                                                                <td>
                                                                    <?php if($proposal->is_fully_withdrawn ?? false): ?>
                                                                        <input type="checkbox" disabled class="form-check-input" style="opacity: 0.3;">
                                                                        <span class="badge bg-success ms-2">Đã hoàn tất</span>
                                                                    <?php else: ?>
                                                                        <input type="checkbox" 
                                                                               class="proposal-checkbox" 
                                                                               name="service_proposals[]" 
                                                                               value="<?php echo e($proposal->id); ?>"
                                                                               onchange="toggleProposal(<?php echo e($proposal->id); ?>)">
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <strong><?php echo e($proposal->service_name); ?></strong>
                                                                    <br><small class="text-muted">SL: <?php echo e($proposal->quantity); ?></small>
                                                                    <br><small class="text-muted">NCC: <?php echo e($proposal->supplier_name); ?></small>
                                                                </td>
                                                                <td>
                                                                    <strong class="text-primary"><?php echo e($proposal->formatted_amount); ?></strong>
                                                                    <?php if(($proposal->total_withdrawn ?? 0) > 0): ?>
                                                                        <br><small class="text-info">
                                                                            <i class="fas fa-info-circle"></i> Đã rút: <?php echo e(number_format($proposal->total_withdrawn ?? 0)); ?> VNĐ
                                                                        </small>
                                                                    <?php endif; ?>
                                                                    <?php if(!($proposal->is_fully_withdrawn ?? false) && ($proposal->remaining_amount ?? 0) < $proposal->amount): ?>
                                                                        <br><small class="text-success fw-bold">
                                                                            <i class="fas fa-coins"></i> Khả dụng: <?php echo e(number_format($proposal->remaining_amount ?? $proposal->amount)); ?> VNĐ
                                                                        </small>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if($proposal->budget): ?>
                                                                        <?php echo e($proposal->budget->name); ?>

                                                                    <?php else: ?>
                                                                        <span class="text-muted">-</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if($proposal->is_fully_withdrawn ?? false): ?>
                                                                        <div class="text-center p-3 bg-light rounded">
                                                                            <i class="fas fa-check-circle text-success fs-4"></i>
                                                                            <br><span class="text-success fw-bold">Đã rút hết tiền</span>
                                                                        </div>
                                                                    <?php else: ?>
                                                                        <div class="input-group">
                                                                            <input type="number" 
                                                                                   class="form-control withdrawal-amount" 
                                                                                   name="amounts[<?php echo e($proposal->id); ?>]" 
                                                                                   id="amount_<?php echo e($proposal->id); ?>"
                                                                                   min="1" 
                                                                                   max="<?php echo e($proposal->remaining_amount ?? $proposal->amount); ?>"
                                                                                   step="1"
                                                                                   placeholder="<?php echo e(number_format($proposal->remaining_amount ?? $proposal->amount)); ?>"
                                                                                   disabled
                                                                                   onchange="calculateTotal()">
                                                                            <span class="input-group-text">VNĐ</span>
                                                                        </div>
                                                                        <small class="text-success fw-bold">
                                                                            <i class="fas fa-hand-holding-usd"></i> Tối đa có thể rút: <?php echo e(number_format($proposal->remaining_amount ?? $proposal->amount)); ?> VNĐ
                                                                        </small>
                                                                    <?php endif; ?>
                                                                    
                                                                    <!-- Hidden input for proposal ID -->
                                                                    <?php if(!($proposal->is_fully_withdrawn ?? false)): ?>
                                                                        <input type="hidden" 
                                                                               name="service_proposals[<?php echo e($proposal->id); ?>][id]" 
                                                                               value="<?php echo e($proposal->id); ?>">
                                                                    <?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-4">
                                                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                                <h5 class="text-muted">Không có đề xuất nào đã hoàn thành thanh toán</h5>
                                                <p class="text-muted">Bạn cần có ít nhất một đề xuất đã hoàn thành thanh toán để tạo yêu cầu rút tiền.</p>
                                                <a href="<?php echo e(route('service-proposals.index')); ?>" class="btn btn-primary">
                                                    <i class="fas fa-arrow-left me-1"></i>
                                                    Quay lại danh sách đề xuất
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Summary Card -->
                                <div class="card mb-4 sticky-top" style="top: 20px;">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-calculator me-2"></i>
                                            Tóm tắt yêu cầu
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Số đề xuất đã chọn:</label>
                                            <div class="h5 text-primary" id="selectedCount">0</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="amount" class="form-label">
                                                Tổng số tiền rút <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       class="form-control <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                       id="amount" 
                                                       name="amount" 
                                                       value="<?php echo e(old('amount')); ?>"
                                                       min="1" 
                                                       step="1"
                                                       readonly
                                                       required>
                                                <span class="input-group-text">VNĐ</span>
                                            </div>
                                            <div class="form-text">Tự động tính từ các đề xuất đã chọn</div>
                                            <?php $__errorArgs = ['amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>

                                        <div class="mb-3">
                                            <label for="note" class="form-label">
                                                <i class="fas fa-sticky-note me-1"></i>
                                                Ghi chú
                                            </label>
                                            <textarea class="form-control <?php $__errorArgs = ['note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                      id="note" 
                                                      name="note" 
                                                      rows="4"
                                                      placeholder="Ghi chú về yêu cầu rút tiền (không bắt buộc)"><?php echo e(old('note')); ?></textarea>
                                            <?php $__errorArgs = ['note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        </div>

                                        <hr>

                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                                <i class="fas fa-paper-plane me-1"></i>
                                                Tạo yêu cầu rút tiền
                                            </button>
                                            <a href="<?php echo e(route('withdrawals.index')); ?>" class="btn btn-outline-secondary">
                                                <i class="fas fa-arrow-left me-1"></i>
                                                Quay lại
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedProposals = [];

function toggleAllProposals() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.proposal-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
        toggleProposal(checkbox.value);
    });
}

function toggleProposal(proposalId) {
    console.log('Toggle proposal called for ID:', proposalId);
    const checkbox = document.querySelector(`input[value="${proposalId}"]`);
    const amountInput = document.getElementById(`amount_${proposalId}`);
    
    console.log('Checkbox found:', checkbox);
    console.log('Amount input found:', amountInput);
    console.log('Checkbox checked:', checkbox ? checkbox.checked : 'N/A');
    
    if (checkbox && checkbox.checked) {
        // Enable amount input and add to selected proposals
        amountInput.disabled = false;
        amountInput.required = true;
        amountInput.focus();
        
        if (!selectedProposals.includes(proposalId)) {
            selectedProposals.push(proposalId);
        }
        console.log('Added proposal. Selected proposals:', selectedProposals);
    } else if (checkbox) {
        // Disable amount input and remove from selected proposals
        amountInput.disabled = true;
        amountInput.required = false;
        amountInput.value = '';
        
        selectedProposals = selectedProposals.filter(id => id != proposalId);
        console.log('Removed proposal. Selected proposals:', selectedProposals);
    }
    
    updateSummary();
    calculateTotal();
}

function calculateTotal() {
    let total = 0;
    
    selectedProposals.forEach(proposalId => {
        const amountInput = document.getElementById(`amount_${proposalId}`);
        const amount = parseFloat(amountInput.value) || 0;
        total += amount;
    });
    
    document.getElementById('amount').value = total;
    updateSubmitButton();
}

function updateSummary() {
    document.getElementById('selectedCount').textContent = selectedProposals.length;
}

function updateSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    const totalAmount = parseFloat(document.getElementById('amount').value) || 0;
    
    if (selectedProposals.length > 0 && totalAmount > 0) {
        submitBtn.disabled = false;
    } else {
        submitBtn.disabled = true;
    }
}

// Add event listeners to amount inputs
document.addEventListener('DOMContentLoaded', function() {
    const amountInputs = document.querySelectorAll('.withdrawal-amount');
    amountInputs.forEach(input => {
        input.addEventListener('input', calculateTotal);
    });
});

// Form validation
document.getElementById('withdrawalForm').addEventListener('submit', function(e) {
    console.log('Form validation started');
    console.log('Selected proposals:', selectedProposals);
    
    // Debug form data before submit
    const formData = new FormData(this);
    console.log('Form data being sent:');
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
    
    // Debug logs (keep for monitoring)
    console.log('Form will be submitted now...');
    
    if (selectedProposals.length === 0) {
        e.preventDefault();
        alert('Vui lòng chọn ít nhất một đề xuất để rút tiền.');
        return false;
    }
    
    const totalAmount = parseFloat(document.getElementById('amount').value) || 0;
    console.log('Total amount:', totalAmount);
    
    if (totalAmount <= 0) {
        e.preventDefault();
        alert('Tổng số tiền rút phải lớn hơn 0.');
        return false;
    }
    
    // Validate individual amounts
    let hasError = false;
    selectedProposals.forEach(proposalId => {
        const amountInput = document.getElementById(`amount_${proposalId}`);
        const amount = parseFloat(amountInput.value) || 0;
        
        if (amount <= 0) {
            hasError = true;
            amountInput.classList.add('is-invalid');
        } else {
            amountInput.classList.remove('is-invalid');
        }
    });
    
    if (hasError) {
        e.preventDefault();
        alert('Vui lòng nhập số tiền hợp lệ cho tất cả đề xuất đã chọn.');
        return false;
    }
    
    return true;
});

</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/bl555pm.com/resources/views/withdrawals/create.blade.php ENDPATH**/ ?>