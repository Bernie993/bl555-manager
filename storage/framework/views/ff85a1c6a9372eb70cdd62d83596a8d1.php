<?php $__env->startSection('title', 'Sửa Người dùng'); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('users.index')); ?>">Quản lý Người dùng</a></li>
    <li class="breadcrumb-item active">Sửa Người dùng</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-edit me-2"></i>
                Sửa Người dùng: <?php echo e($user->name); ?>

            </h1>
            <a href="<?php echo e(route('users.index')); ?>" class="btn btn-outline-secondary">
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
                <form action="<?php echo e(route('users.update', $user)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Tên người dùng <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="name" 
                                   name="name" 
                                   value="<?php echo e(old('name', $user->name)); ?>" 
                                   required>
                            <?php $__errorArgs = ['name'];
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
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" 
                                   class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="email" 
                                   name="email" 
                                   value="<?php echo e(old('email', $user->email)); ?>" 
                                   required>
                            <?php $__errorArgs = ['email'];
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
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Mật khẩu mới</label>
                            <input type="password" 
                                   class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="password" 
                                   name="password"
                                   placeholder="Để trống nếu không đổi mật khẩu">
                            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <div class="form-text">Để trống nếu không muốn thay đổi mật khẩu</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation"
                                   placeholder="Nhập lại mật khẩu mới">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="role_id" class="form-label">Vai trò <span class="text-danger">*</span></label>
                            <select class="form-select <?php $__errorArgs = ['role_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="role_id" name="role_id" required>
                                <option value="">Chọn vai trò</option>
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($role->id); ?>" <?php echo e(old('role_id', $user->role_id) == $role->id ? 'selected' : ''); ?>>
                                        <?php echo e($role->display_name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['role_id'];
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
                            <label class="form-label">Trạng thái</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?php echo e(old('is_active', $user->is_active) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="is_active">
                                    Kích hoạt tài khoản
                                </label>
                            </div>
                            <?php if($user->id === auth()->id()): ?>
                                <div class="form-text text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Bạn không thể vô hiệu hóa tài khoản của chính mình
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="text" 
                                   class="form-control <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?php echo e(old('phone', $user->phone)); ?>" 
                                   placeholder="Nhập số điện thoại">
                            <?php $__errorArgs = ['phone'];
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
                            <label for="telegram" class="form-label">Telegram</label>
                            <input type="text" 
                                   class="form-control <?php $__errorArgs = ['telegram'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="telegram" 
                                   name="telegram" 
                                   value="<?php echo e(old('telegram', $user->telegram)); ?>" 
                                   placeholder="@username hoặc link Telegram">
                            <?php $__errorArgs = ['telegram'];
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
                    
                    <!-- Employment Dates Section -->
                    <div class="card mb-3" id="employment-section">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                <span id="section-title">Thông tin công việc</span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <!-- Employment dates for non-partner roles -->
                            <div id="employment-dates">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="hire_date" class="form-label">Ngày nhận việc</label>
                                        <input type="date" 
                                               class="form-control <?php $__errorArgs = ['hire_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                               id="hire_date" 
                                               name="hire_date" 
                                               value="<?php echo e(old('hire_date', $user->hire_date?->format('Y-m-d'))); ?>">
                                        <?php $__errorArgs = ['hire_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <div class="form-text">Ngày bắt đầu làm việc</div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="permanent_date" class="form-label">Ngày chuyển chính</label>
                                        <input type="date" 
                                               class="form-control <?php $__errorArgs = ['permanent_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                               id="permanent_date" 
                                               name="permanent_date" 
                                               value="<?php echo e(old('permanent_date', $user->permanent_date?->format('Y-m-d'))); ?>">
                                        <?php $__errorArgs = ['permanent_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <div class="form-text">Ngày chuyển thành nhân viên chính thức</div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label for="resignation_date" class="form-label">Ngày nghỉ việc</label>
                                        <input type="date" 
                                               class="form-control <?php $__errorArgs = ['resignation_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                               id="resignation_date" 
                                               name="resignation_date" 
                                               value="<?php echo e(old('resignation_date', $user->resignation_date?->format('Y-m-d'))); ?>">
                                        <?php $__errorArgs = ['resignation_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                        <div class="form-text">Ngày kết thúc làm việc (nếu có)</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment info for partner role -->
                            <div id="payment-info" style="display: none;">
                                <div class="mb-3">
                                    <label for="payment_info" class="form-label">Thông tin nhận thanh toán</label>
                                    <textarea class="form-control <?php $__errorArgs = ['payment_info'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                              id="payment_info" 
                                              name="payment_info" 
                                              rows="4"
                                              placeholder="Nhập thông tin tài khoản ngân hàng, ví điện tử hoặc thông tin thanh toán khác..."><?php echo e(old('payment_info', $user->payment_info)); ?></textarea>
                                    <?php $__errorArgs = ['payment_info'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    <div class="form-text">Thông tin chi tiết về cách thức nhận thanh toán</div>
                                </div>
                            </div>
                            
                            <?php if($user->hire_date || $user->permanent_date || $user->resignation_date): ?>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Thông tin hiện tại:
                                        </h6>
                                        <div class="row">
                                            <?php if($user->hire_date): ?>
                                            <div class="col-md-4">
                                                <strong>Ngày nhận việc:</strong><br>
                                                <?php echo e($user->hire_date->format('d/m/Y')); ?>

                                                <small class="text-muted d-block">
                                                    (<?php echo e($user->hire_date->diffForHumans()); ?>)
                                                </small>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if($user->permanent_date): ?>
                                            <div class="col-md-4">
                                                <strong>Ngày chuyển chính:</strong><br>
                                                <?php echo e($user->permanent_date->format('d/m/Y')); ?>

                                                <small class="text-muted d-block">
                                                    (<?php echo e($user->permanent_date->diffForHumans()); ?>)
                                                </small>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if($user->resignation_date): ?>
                                            <div class="col-md-4">
                                                <strong>Ngày nghỉ việc:</strong><br>
                                                <span class="text-danger"><?php echo e($user->resignation_date->format('d/m/Y')); ?></span>
                                                <small class="text-muted d-block">
                                                    (<?php echo e($user->resignation_date->diffForHumans()); ?>)
                                                </small>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?php echo e(route('users.index')); ?>" class="btn btn-outline-secondary">
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
                    <i class="fas fa-user-circle me-2"></i>
                    Thông tin hiện tại
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-large mb-3">
                        <?php echo e(strtoupper(substr($user->name, 0, 2))); ?>

                    </div>
                    <h6><?php echo e($user->name); ?></h6>
                    <p class="text-muted small"><?php echo e($user->email); ?></p>
                    <span class="badge bg-<?php echo e($user->is_active ? 'success' : 'danger'); ?>">
                        <?php echo e($user->is_active ? 'Hoạt động' : 'Vô hiệu hóa'); ?>

                    </span>
                </div>
                
                <div class="small">
                    <div class="row mb-2">
                        <div class="col-6">
                            <strong>Tạo lúc:</strong>
                        </div>
                        <div class="col-6">
                            <?php echo e($user->created_at->format('d/m/Y')); ?>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <strong>Cập nhật:</strong>
                        </div>
                        <div class="col-6">
                            <?php echo e($user->updated_at->format('d/m/Y')); ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-key me-2"></i>
                    Quyền hạn hiện tại
                </h6>
            </div>
            <div class="card-body">
                <?php if($user->role && $user->role->permissions->count() > 0): ?>
                    <div class="small">
                        <p><strong><?php echo e($user->role->display_name); ?>:</strong></p>
                        <ul class="mb-0">
                            <?php $__currentLoopData = $user->role->permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="mb-1">
                                <i class="fas fa-check text-success me-2"></i>
                                <?php echo e($permission->display_name); ?>

                            </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php else: ?>
                    <p class="text-muted small">Chưa có quyền nào</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Lưu ý
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <ul class="mb-0">
                        <li>Email phải là duy nhất trong hệ thống</li>
                        <li>Để trống mật khẩu nếu không muốn thay đổi</li>
                        <li>Thay đổi vai trò sẽ ảnh hưởng đến quyền truy cập</li>
                        <?php if($user->id === auth()->id()): ?>
                        <li class="text-warning">Bạn không thể vô hiệu hóa tài khoản của chính mình</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<style>
.avatar-large {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 18px;
    margin: 0 auto;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Role-based form sections
    const roleSelect = document.getElementById('role_id');
    const sectionTitle = document.getElementById('section-title');
    const employmentDates = document.getElementById('employment-dates');
    const paymentInfo = document.getElementById('payment-info');
    
    const roleData = {
        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo e($role->id); ?>: '<?php echo e($role->name); ?>',
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    };
    
    function updateFormSections() {
        const roleId = roleSelect.value;
        const roleName = roleData[roleId];
        
        if (roleName === 'partner') {
            sectionTitle.textContent = 'Thông tin nhận thanh toán';
            employmentDates.style.display = 'none';
            paymentInfo.style.display = 'block';
        } else {
            sectionTitle.textContent = 'Thông tin công việc';
            employmentDates.style.display = 'block';
            paymentInfo.style.display = 'none';
        }
    }
    
    roleSelect.addEventListener('change', updateFormSections);
    
    // Initialize form sections on page load
    updateFormSections();
    
    // Prevent current user from disabling their own account
    <?php if($user->id === auth()->id()): ?>
    const isActiveCheckbox = document.getElementById('is_active');
    isActiveCheckbox.addEventListener('change', function() {
        if (!this.checked) {
            this.checked = true;
            alert('Bạn không thể vô hiệu hóa tài khoản của chính mình!');
        }
    });
    <?php endif; ?>
    
    // Password confirmation validation
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    
    function validatePasswordMatch() {
        if (password.value && passwordConfirmation.value) {
            if (password.value !== passwordConfirmation.value) {
                passwordConfirmation.setCustomValidity('Mật khẩu xác nhận không khớp');
            } else {
                passwordConfirmation.setCustomValidity('');
            }
        } else {
            passwordConfirmation.setCustomValidity('');
        }
    }
    
    password.addEventListener('input', validatePasswordMatch);
    passwordConfirmation.addEventListener('input', validatePasswordMatch);
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/bl555pm.com/resources/views/users/edit.blade.php ENDPATH**/ ?>