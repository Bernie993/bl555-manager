<?php $__env->startSection('title', 'Chi tiết Người dùng'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('users.index')); ?>">Quản lý Người dùng</a></li>
    <li class="breadcrumb-item active">Chi tiết Người dùng</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-user me-2"></i>
                Chi tiết Người dùng: <?php echo e($user->name); ?>

            </h1>
            <div class="btn-group">
                <a href="<?php echo e(route('users.index')); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Quay lại
                </a>
                <?php if(auth()->user()->hasPermission('users.update')): ?>
                <a href="<?php echo e(route('users.edit', $user)); ?>" class="btn btn-primary">
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
                    Thông tin Người dùng
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Tên:</strong>
                    </div>
                    <div class="col-sm-9">
                        <?php echo e($user->name); ?>

                        <?php if($user->id === auth()->id()): ?>
                            <span class="badge bg-info ms-2">Bạn</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Email:</strong>
                    </div>
                    <div class="col-sm-9">
                        <?php echo e($user->email); ?>

                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Vai trò:</strong>
                    </div>
                    <div class="col-sm-9">
                        <?php if($user->role): ?>
                            <span class="badge bg-primary fs-6"><?php echo e($user->role->display_name); ?></span>
                            <div class="mt-2 text-muted small"><?php echo e($user->role->description); ?></div>
                        <?php else: ?>
                            <span class="badge bg-secondary">Chưa có vai trò</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Trạng thái:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="badge bg-<?php echo e($user->is_active ? 'success' : 'danger'); ?> fs-6">
                            <?php echo e($user->is_active ? 'Hoạt động' : 'Vô hiệu hóa'); ?>

                        </span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Ngày tạo:</strong>
                    </div>
                    <div class="col-sm-9">
                        <?php echo e($user->created_at->format('d/m/Y H:i')); ?>

                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Cập nhật lần cuối:</strong>
                    </div>
                    <div class="col-sm-9">
                        <?php echo e($user->updated_at->format('d/m/Y H:i')); ?>

                    </div>
                </div>
            </div>
        </div>
        
        <!-- Employment Information Card -->
        <?php if($user->hire_date || $user->permanent_date || $user->resignation_date): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Thông tin Công việc
                </h5>
            </div>
            <div class="card-body">
                <?php if($user->hire_date): ?>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Ngày nhận việc:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="badge bg-info fs-6"><?php echo e($user->hire_date->format('d/m/Y')); ?></span>
                        <div class="mt-1 text-muted small">
                            <i class="fas fa-clock me-1"></i>
                            <?php echo e($user->hire_date->diffForHumans()); ?>

                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if($user->permanent_date): ?>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Ngày chuyển chính:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="badge bg-success fs-6"><?php echo e($user->permanent_date->format('d/m/Y')); ?></span>
                        <div class="mt-1 text-muted small">
                            <i class="fas fa-clock me-1"></i>
                            <?php echo e($user->permanent_date->diffForHumans()); ?>

                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if($user->resignation_date): ?>
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <strong>Ngày nghỉ việc:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="badge bg-danger fs-6"><?php echo e($user->resignation_date->format('d/m/Y')); ?></span>
                        <div class="mt-1 text-muted small">
                            <i class="fas fa-clock me-1"></i>
                            <?php echo e($user->resignation_date->diffForHumans()); ?>

                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Work Duration Calculation -->
                <?php if($user->hire_date): ?>
                <div class="row">
                    <div class="col-sm-3">
                        <strong>Thời gian làm việc:</strong>
                    </div>
                    <div class="col-sm-9">
                        <?php
                            $endDate = $user->resignation_date ?? now();
                            $workDuration = $user->hire_date->diff($endDate);
                            $years = $workDuration->y;
                            $months = $workDuration->m;
                            $days = $workDuration->d;
                        ?>
                        
                        <div class="d-flex flex-wrap gap-2">
                            <?php if($years > 0): ?>
                                <span class="badge bg-primary"><?php echo e($years); ?> năm</span>
                            <?php endif; ?>
                            <?php if($months > 0): ?>
                                <span class="badge bg-primary"><?php echo e($months); ?> tháng</span>
                            <?php endif; ?>
                            <?php if($days > 0 && $years == 0): ?>
                                <span class="badge bg-primary"><?php echo e($days); ?> ngày</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if($user->resignation_date): ?>
                            <div class="mt-1 text-danger small">
                                <i class="fas fa-info-circle me-1"></i>
                                Đã nghỉ việc
                            </div>
                        <?php else: ?>
                            <div class="mt-1 text-success small">
                                <i class="fas fa-check-circle me-1"></i>
                                Đang làm việc
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if($user->role && $user->role->permissions->count() > 0): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-key me-2"></i>
                    Quyền hạn
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                        $groupedPermissions = $user->role->permissions->groupBy('module');
                    ?>
                    <?php $__currentLoopData = $groupedPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $permissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-4 mb-3">
                        <h6 class="text-primary">
                            <?php switch($module):
                                case ('websites'): ?>
                                    <i class="fas fa-globe me-2"></i>Website
                                    <?php break; ?>
                                <?php case ('budgets'): ?>
                                    <i class="fas fa-wallet me-2"></i>Ngân sách
                                    <?php break; ?>
                                <?php case ('users'): ?>
                                    <i class="fas fa-users me-2"></i>Người dùng
                                    <?php break; ?>
                                <?php default: ?>
                                    <?php echo e(ucfirst($module)); ?>

                            <?php endswitch; ?>
                        </h6>
                        <ul class="list-unstyled ms-3">
                            <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="mb-1">
                                <i class="fas fa-check text-success me-2"></i>
                                <?php echo e($permission->display_name); ?>

                            </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user-circle me-2"></i>
                    Avatar
                </h6>
            </div>
            <div class="card-body text-center">
                <div class="avatar-large mb-3">
                    <?php echo e(strtoupper(substr($user->name, 0, 2))); ?>

                </div>
                <h5><?php echo e($user->name); ?></h5>
                <p class="text-muted"><?php echo e($user->email); ?></p>
            </div>
        </div>
        
        <?php if(auth()->user()->hasPermission('users.update') || (auth()->user()->hasPermission('users.delete') && $user->id !== auth()->id())): ?>
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>
                    Thao tác
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if(auth()->user()->hasPermission('users.update')): ?>
                    <a href="<?php echo e(route('users.edit', $user)); ?>" class="btn btn-warning">
                        <i class="fas fa-edit me-2"></i>
                        Chỉnh sửa
                    </a>
                    <?php endif; ?>
                    
                    <?php if(auth()->user()->hasPermission('users.delete') && $user->id !== auth()->id()): ?>
                    <form action="<?php echo e(route('users.destroy', $user)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger w-100 btn-delete">
                            <i class="fas fa-trash me-2"></i>
                            Xóa Người dùng
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
.avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 24px;
    margin: 0 auto;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/bl555pm.com/resources/views/users/show.blade.php ENDPATH**/ ?>