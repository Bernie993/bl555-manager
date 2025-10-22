<?php $__env->startSection('title', 'Quản lý Người dùng'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item active">Quản lý Người dùng</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-users me-2"></i>
                Quản lý Người dùng
            </h1>
            <?php if(auth()->user()->hasPermission('users.create')): ?>
            <a href="<?php echo e(route('users.create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>
                Thêm Người dùng
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('users.index')); ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Tìm kiếm</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="<?php echo e(request('search')); ?>" placeholder="Tên hoặc email...">
                </div>
                <div class="col-md-3">
                    <label for="role_id" class="form-label">Vai trò</label>
                    <select class="form-select" id="role_id" name="role_id">
                        <option value="">Tất cả vai trò</option>
                        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($role->id); ?>" <?php echo e(request('role_id') == $role->id ? 'selected' : ''); ?>>
                                <?php echo e($role->display_name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="is_active" class="form-label">Trạng thái</label>
                    <select class="form-select" id="is_active" name="is_active">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" <?php echo e(request('is_active') === '1' ? 'selected' : ''); ?>>Hoạt động</option>
                        <option value="0" <?php echo e(request('is_active') === '0' ? 'selected' : ''); ?>>Vô hiệu hóa</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search me-2"></i>
                            Lọc
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <?php if($users->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Người dùng</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Thông tin công việc</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3">
                                        <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                                    </div>
                                    <div>
                                        <strong><?php echo e($user->name); ?></strong>
                                        <?php if($user->id === auth()->id()): ?>
                                            <span class="badge bg-info ms-1">Bạn</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo e($user->email); ?></td>
                            <td>
                                <?php if($user->role): ?>
                                    <span class="badge bg-primary"><?php echo e($user->role->display_name); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Chưa có vai trò</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo e($user->is_active ? 'success' : 'danger'); ?>">
                                    <?php echo e($user->is_active ? 'Hoạt động' : 'Vô hiệu hóa'); ?>

                                </span>
                                <?php if($user->resignation_date): ?>
                                    <br><small class="text-danger">Đã nghỉ việc</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($user->hire_date || $user->permanent_date || $user->resignation_date): ?>
                                    <div class="small">
                                        <?php if($user->hire_date): ?>
                                            <div class="text-info">
                                                <i class="fas fa-calendar-plus me-1"></i>
                                                Nhận việc: <?php echo e($user->hire_date->format('d/m/Y')); ?>

                                            </div>
                                        <?php endif; ?>
                                        <?php if($user->permanent_date): ?>
                                            <div class="text-success">
                                                <i class="fas fa-calendar-check me-1"></i>
                                                Chuyển chính: <?php echo e($user->permanent_date->format('d/m/Y')); ?>

                                            </div>
                                        <?php endif; ?>
                                        <?php if($user->resignation_date): ?>
                                            <div class="text-danger">
                                                <i class="fas fa-calendar-times me-1"></i>
                                                Nghỉ việc: <?php echo e($user->resignation_date->format('d/m/Y')); ?>

                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">Chưa có thông tin</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($user->created_at->format('d/m/Y')); ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?php echo e(route('users.show', $user)); ?>" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if(auth()->user()->hasPermission('users.update')): ?>
                                    <a href="<?php echo e(route('users.edit', $user)); ?>" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if(auth()->user()->hasPermission('users.delete') && $user->id !== auth()->id()): ?>
                                    <form action="<?php echo e(route('users.destroy', $user)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($users->withQueryString()->links()); ?>

            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Không có người dùng nào</h5>
                <p class="text-muted">
                    <?php if(request()->hasAny(['search', 'role_id', 'is_active'])): ?>
                        Không tìm thấy người dùng phù hợp với bộ lọc.
                    <?php else: ?>
                        Hãy thêm người dùng đầu tiên của bạn.
                    <?php endif; ?>
                </p>
                <?php if(auth()->user()->hasPermission('users.create')): ?>
                <a href="<?php echo e(route('users.create')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Thêm Người dùng
                </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 14px;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/bl555pm.com/resources/views/users/index.blade.php ENDPATH**/ ?>