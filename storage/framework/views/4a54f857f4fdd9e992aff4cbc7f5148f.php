<?php $__env->startSection('title', 'Sửa Ngân sách'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('budgets.index')); ?>">Quản lý Ngân sách</a></li>
    <li class="breadcrumb-item active">Sửa Ngân sách</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-edit me-2"></i>
                Sửa Ngân sách: <?php echo e($budget->seoer); ?>

            </h1>
            <a href="<?php echo e(route('budgets.index')); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Quay lại
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="<?php echo e(route('budgets.update', $budget)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="seoer" class="form-label">Seoer <span class="text-danger">*</span></label>
                            <select class="form-select <?php $__errorArgs = ['seoer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                    id="seoer" 
                                    name="seoer" 
                                    required>
                                <option value="">Chọn Seoer</option>
                                <?php
                                    $seoers = \App\Models\User::whereHas('role', function($q) {
                                        $q->where('name', 'seoer');
                                    })->get();
                                ?>
                                <?php $__currentLoopData = $seoers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seoerUser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($seoerUser->name); ?>" <?php echo e((old('seoer', $budget->seoer) === $seoerUser->name) ? 'selected' : ''); ?>>
                                        <?php echo e($seoerUser->name); ?> (<?php echo e(ucfirst($seoerUser->role->name)); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['seoer'];
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
                        
                        <div class="col-md-6 mb-3">
                            <label for="total_budget" class="form-label">Tổng ngân sách (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control <?php $__errorArgs = ['total_budget'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="total_budget" 
                                   name="total_budget" 
                                   value="<?php echo e(old('total_budget', $budget->total_budget)); ?>" 
                                   required
                                   min="0"
                                   step="1000">
                            <?php $__errorArgs = ['total_budget'];
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
                    </div>
                    
                    <!-- Current Budget Status -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>
                                Trạng thái Ngân sách Hiện tại
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                        <h5 class="text-primary mb-1"><?php echo e($budget->formatted_total_budget); ?></h5>
                                        <small class="text-muted">Tổng ngân sách</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                                        <h5 class="text-warning mb-1"><?php echo e($budget->formatted_spent_amount); ?></h5>
                                        <small class="text-muted">Đã chi tiêu (tự động)</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                        <h5 class="text-success mb-1"><?php echo e($budget->formatted_remaining_amount); ?></h5>
                                        <small class="text-muted">Còn lại</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Tự động cập nhật
                                    </h6>
                                    <p class="mb-0">
                                        <strong>Số tiền đã chi tiêu</strong> được tự động tính từ tổng các 
                                        <strong>Đề xuất dịch vụ</strong> đã hoàn thành thanh toán và liên kết với ngân sách này.
                                        <br><small class="text-muted">
                                            Hiện có <?php echo e($budget->serviceProposals()->where('status', 'completed')->count()); ?> đề xuất đã hoàn thành.
                                        </small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="period_start" class="form-label">Ngày bắt đầu kỳ</label>
                            <input type="date" 
                                   class="form-control <?php $__errorArgs = ['period_start'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="period_start" 
                                   name="period_start" 
                                   value="<?php echo e(old('period_start', $budget->period_start?->format('Y-m-d'))); ?>">
                            <?php $__errorArgs = ['period_start'];
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
                        
                        <div class="col-md-6 mb-3">
                            <label for="period_end" class="form-label">Ngày kết thúc kỳ</label>
                            <input type="date" 
                                   class="form-control <?php $__errorArgs = ['period_end'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="period_end" 
                                   name="period_end" 
                                   value="<?php echo e(old('period_end', $budget->period_end?->format('Y-m-d'))); ?>">
                            <?php $__errorArgs = ['period_end'];
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
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                  id="description" 
                                  name="description" 
                                  rows="4"><?php echo e(old('description', $budget->description)); ?></textarea>
                        <?php $__errorArgs = ['description'];
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
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?php echo e(route('budgets.index')); ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Thống kê hiện tại
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <?php
                        $percentage = $budget->spending_percentage;
                        $progressClass = $percentage > 80 ? 'bg-danger' : ($percentage > 60 ? 'bg-warning' : 'bg-success');
                    ?>
                    <div class="progress-circle-small mb-3" data-percentage="<?php echo e($percentage); ?>">
                        <span class="progress-value"><?php echo e(number_format($percentage, 1)); ?>%</span>
                    </div>
                </div>
                
                <div class="row text-center small">
                    <div class="col-12 mb-2">
                        <div class="border-bottom pb-2">
                            <div class="text-muted">Tổng ngân sách</div>
                            <div class="h6 text-primary"><?php echo e($budget->formatted_total_budget); ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">Đã chi</div>
                        <div class="h6 text-danger"><?php echo e($budget->formatted_spent_amount); ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">Còn lại</div>
                        <div class="h6 text-success"><?php echo e($budget->formatted_remaining_amount); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-calculator me-2"></i>
                    Tính toán tự động
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <div class="row">
                        <div class="col-6">
                            <strong>Tổng ngân sách:</strong>
                        </div>
                        <div class="col-6" id="display-total">
                            <?php echo e($budget->formatted_total_budget); ?>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Đã chi tiêu:</strong>
                        </div>
                        <div class="col-6" id="display-spent">
                            <?php echo e($budget->formatted_spent_amount); ?>

                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <strong>Còn lại:</strong>
                        </div>
                        <div class="col-6" id="display-remaining">
                            <strong><?php echo e($budget->formatted_remaining_amount); ?></strong>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-6">
                            <strong>Tiến độ:</strong>
                        </div>
                        <div class="col-6" id="display-percentage">
                            <strong><?php echo e(number_format($percentage, 1)); ?>%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Thông tin thời gian
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <div class="row mb-2">
                        <div class="col-6">
                            <strong>Tạo lúc:</strong>
                        </div>
                        <div class="col-6">
                            <?php echo e($budget->created_at->format('d/m/Y H:i')); ?>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Cập nhật:</strong>
                        </div>
                        <div class="col-6">
                            <?php echo e($budget->updated_at->format('d/m/Y H:i')); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<style>
.progress-circle-small {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: conic-gradient(#007bff 0deg, #007bff <?php echo e($percentage * 3.6); ?>deg, #e9ecef <?php echo e($percentage * 3.6); ?>deg, #e9ecef 360deg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    position: relative;
}

.progress-circle-small::before {
    content: '';
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: white;
    position: absolute;
}

.progress-value {
    position: relative;
    z-index: 1;
    font-weight: bold;
    font-size: 12px;
    color: #007bff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalInput = document.getElementById('total_budget');
    const spentInput = document.getElementById('spent_amount');
    
    function updateCalculation() {
        const total = parseFloat(totalInput.value) || 0;
        const spent = parseFloat(spentInput.value) || 0;
        const remaining = total - spent;
        const percentage = total > 0 ? (spent / total) * 100 : 0;
        
        document.getElementById('display-total').textContent = formatCurrency(total);
        document.getElementById('display-spent').textContent = formatCurrency(spent);
        document.getElementById('display-remaining').textContent = formatCurrency(remaining);
        document.getElementById('display-percentage').innerHTML = '<strong>' + percentage.toFixed(1) + '%</strong>';
    }
    
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + ' VNĐ';
    }
    
    totalInput.addEventListener('input', updateCalculation);
    spentInput.addEventListener('input', updateCalculation);
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/bl555pm.com/resources/views/budgets/edit.blade.php ENDPATH**/ ?>