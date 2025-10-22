<?php $__env->startSection('title', 'Chi tiết Ngân sách'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('budgets.index')); ?>">Quản lý Ngân sách</a></li>
    <li class="breadcrumb-item active">Chi tiết Ngân sách</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-eye me-2"></i>
                Chi tiết Ngân sách: <?php echo e($budget->seoer); ?>

            </h1>
            <div class="btn-group">
                <a href="<?php echo e(route('budgets.index')); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Quay lại
                </a>
                <?php if(auth()->user()->hasPermission('budgets.update')): ?>
                <a href="<?php echo e(route('budgets.edit', $budget)); ?>" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>
                    Chỉnh sửa
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Thông tin Ngân sách
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Seoer:</strong>
                    </div>
                    <div class="col-sm-9">
                        <?php echo e($budget->seoer); ?>

                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Tổng ngân sách:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="h5 text-primary"><?php echo e($budget->formatted_total_budget); ?></span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Đã chi tiêu:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="h5 text-danger"><?php echo e($budget->formatted_spent_amount); ?></span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Còn lại:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="h5 text-success"><?php echo e($budget->formatted_remaining_amount); ?></span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Tiến độ chi tiêu:</strong>
                    </div>
                    <div class="col-sm-9">
                        <?php
                            $percentage = $budget->spending_percentage;
                            $progressClass = $percentage > 80 ? 'bg-danger' : ($percentage > 60 ? 'bg-warning' : 'bg-success');
                        ?>
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar <?php echo e($progressClass); ?>" 
                                 role="progressbar" 
                                 style="width: <?php echo e($percentage); ?>%">
                                <?php echo e(number_format($percentage, 1)); ?>%
                            </div>
                        </div>
                        <small class="text-muted">
                            Đã sử dụng <?php echo e(number_format($percentage, 1)); ?>% ngân sách
                        </small>
                    </div>
                </div>
                
                <?php if($budget->period_start || $budget->period_end): ?>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Kỳ hạn:</strong>
                    </div>
                    <div class="col-sm-9">
                        <?php if($budget->period_start && $budget->period_end): ?>
                            <?php echo e($budget->period_start->format('d/m/Y')); ?> - <?php echo e($budget->period_end->format('d/m/Y')); ?>

                            <br>
                            <small class="text-muted">
                                (<?php echo e($budget->period_start->diffInDays($budget->period_end)); ?> ngày)
                            </small>
                        <?php elseif($budget->period_start): ?>
                            Từ <?php echo e($budget->period_start->format('d/m/Y')); ?>

                        <?php elseif($budget->period_end): ?>
                            Đến <?php echo e($budget->period_end->format('d/m/Y')); ?>

                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if($budget->description): ?>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Mô tả:</strong>
                    </div>
                    <div class="col-sm-9">
                        <div class="bg-light p-3 rounded">
                            <?php echo nl2br(e($budget->description)); ?>

                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Thống kê
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="progress-circle" data-percentage="<?php echo e($percentage); ?>">
                        <span class="progress-value"><?php echo e(number_format($percentage, 1)); ?>%</span>
                    </div>
                </div>
                
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <div class="border-bottom pb-2">
                            <div class="h6 mb-1 text-primary">Tổng ngân sách</div>
                            <div class="h5 mb-0"><?php echo e($budget->formatted_total_budget); ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="h6 mb-1 text-danger">Đã chi</div>
                        <div class="h6 mb-0"><?php echo e($budget->formatted_spent_amount); ?></div>
                    </div>
                    <div class="col-6">
                        <div class="h6 mb-1 text-success">Còn lại</div>
                        <div class="h6 mb-0"><?php echo e($budget->formatted_remaining_amount); ?></div>
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
        
        <?php if(auth()->user()->hasPermission('budgets.update') || auth()->user()->hasPermission('budgets.delete')): ?>
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Thao tác
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if(auth()->user()->hasPermission('budgets.update')): ?>
                    <a href="<?php echo e(route('budgets.edit', $budget)); ?>" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>
                        Chỉnh sửa
                    </a>
                    <?php endif; ?>
                    
                    <?php if(auth()->user()->hasPermission('budgets.delete')): ?>
                    <form action="<?php echo e(route('budgets.destroy', $budget)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger w-100 btn-delete">
                            <i class="fas fa-trash me-2"></i>
                            Xóa Ngân sách
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<style>
.progress-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: conic-gradient(#007bff 0deg, #007bff <?php echo e($percentage * 3.6); ?>deg, #e9ecef <?php echo e($percentage * 3.6); ?>deg, #e9ecef 360deg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    position: relative;
}

.progress-circle::before {
    content: '';
    width: 90px;
    height: 90px;
    border-radius: 50%;
    background: white;
    position: absolute;
}

.progress-value {
    position: relative;
    z-index: 1;
    font-weight: bold;
    font-size: 16px;
    color: #007bff;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/bl555pm.com/resources/views/budgets/show.blade.php ENDPATH**/ ?>