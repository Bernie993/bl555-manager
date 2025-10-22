<?php $__env->startSection('title', 'Quản lý Dịch vụ'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            <i class="fas fa-cogs mr-2"></i>
                            Quản lý Dịch vụ
                        </h3>
                        <?php if(auth()->user()->role && auth()->user()->role->name === 'partner'): ?>
                            <a href="<?php echo e(route('services.create')); ?>" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>Thêm dịch vụ mới
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('services.index')); ?>" class="mb-4">
                        <div class="filter-section">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="type">Loại dịch vụ:</label>
                                        <select name="type" id="type" class="form-control form-control-sm">
                                            <option value="">Tất cả</option>
                                            <?php $__currentLoopData = \App\Models\Service::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($key); ?>" <?php echo e(request('type') == $key ? 'selected' : ''); ?>>
                                                    <?php echo e($value); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>

                                <?php if(!empty($partners)): ?>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="partner_name">Đối tác:</label>
                                        <select name="partner_name" id="partner_name" class="form-control form-control-sm">
                                            <option value="">Tất cả</option>
                                            <?php $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($partner->id); ?>" <?php echo e(request('partner_name') == $partner->id ? 'selected' : ''); ?>>
                                                    <?php echo e($partner->name); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if(auth()->user()->role && in_array(auth()->user()->role->name, ['admin', 'it', 'assistant'])): ?>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="approval_status">Trạng thái duyệt:</label>
                                        <select name="approval_status" id="approval_status" class="form-control form-control-sm">
                                            <option value="">Tất cả</option>
                                            <?php $__currentLoopData = \App\Models\Service::APPROVAL_STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($key); ?>" <?php echo e(request('approval_status') == $key ? 'selected' : ''); ?>>
                                                    <?php echo e($value); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="price_from">Giá từ:</label>
                                        <input type="number" name="price_from" id="price_from" class="form-control form-control-sm" 
                                               placeholder="0" min="0" step="1000"
                                               value="<?php echo e(request('price_from')); ?>">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="price_to">Giá đến:</label>
                                        <input type="number" name="price_to" id="price_to" class="form-control form-control-sm" 
                                               placeholder="1000000" min="0" step="1000"
                                               value="<?php echo e(request('price_to')); ?>">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="sort_by">Sắp xếp:</label>
                                        <select name="sort_by" id="sort_by" class="form-control form-control-sm">
                                            <option value="created_at_desc" <?php echo e(request('sort_by') == 'created_at_desc' ? 'selected' : ''); ?>>
                                                Mới nhất
                                            </option>
                                            <option value="price_asc" <?php echo e(request('sort_by') == 'price_asc' ? 'selected' : ''); ?>>
                                                Giá thấp → cao
                                            </option>
                                            <option value="price_desc" <?php echo e(request('sort_by') == 'price_desc' ? 'selected' : ''); ?>>
                                                Giá cao → thấp
                                            </option>
                                            <option value="name_asc" <?php echo e(request('sort_by') == 'name_asc' ? 'selected' : ''); ?>>
                                                Tên A → Z
                                            </option>
                                            <option value="name_desc" <?php echo e(request('sort_by') == 'name_desc' ? 'selected' : ''); ?>>
                                                Tên Z → A
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Row -->
                            <div class="row mt-2">
                                <div class="col-md-10">
                                    <div class="form-group mb-0">
                                        <input type="text" name="search" id="search" class="form-control form-control-sm" 
                                               placeholder="🔍 Tìm kiếm theo tên dịch vụ, website, lĩnh vực..." 
                                               value="<?php echo e(request('search')); ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-info btn-sm mr-2">
                                            <i class="fas fa-search"></i> Lọc
                                        </button>
                                        <a href="<?php echo e(route('services.index')); ?>" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Info Row -->
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <div class="text-right">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            Tìm thấy <?php echo e($services->total()); ?> dịch vụ
                                        </small>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>

                    <?php if(auth()->user()->role && auth()->user()->role->name === 'seoer'): ?>
                        <!-- Bulk Actions for SEOer -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <button type="button" id="selectAllBtn" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-check-square"></i> Chọn tất cả
                                    </button>
                                    <button type="button" id="deselectAllBtn" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-square"></i> Bỏ chọn tất cả
                                    </button>
                                </div>
                                <div>
                                    <button type="button" id="bulkCreateProposalBtn" class="btn btn-success" disabled>
                                        <i class="fas fa-plus"></i> Tạo đề xuất (<span id="selectedCount">0</span>)
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Services Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <?php if(auth()->user()->role && auth()->user()->role->name === 'seoer'): ?>
                                        <th width="50">
                                            <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                                        </th>
                                    <?php endif; ?>
                                    <th>ID</th>
                                    <th>Tên dịch vụ</th>
                                    <th>Loại</th>
                                    <th>Website</th>
                                    <?php if(auth()->user()->role && auth()->user()->role->name !== 'partner'): ?>
                                        <th>Đối tác</th>
                                    <?php endif; ?>
                                    <th>DR/DA/PA/TF</th>
                                    <th>Lĩnh vực</th>
                                    <th>Giá</th>
                                    <th>Trạng thái</th>
                                    <?php if(auth()->user()->role && in_array(auth()->user()->role->name, ['admin', 'it', 'assistant', 'partner'])): ?>
                                        <th>Duyệt</th>
                                    <?php endif; ?>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <?php if(auth()->user()->role && auth()->user()->role->name === 'seoer'): ?>
                                            <td>
                                                <?php if($service->is_active): ?>
                                                    <input type="checkbox" 
                                                           class="form-check-input service-checkbox" 
                                                           value="<?php echo e($service->id); ?>"
                                                           data-service-name="<?php echo e($service->name); ?>"
                                                           data-partner-id="<?php echo e($service->partner_id); ?>"
                                                           data-partner-name="<?php echo e($service->partner->name ?? 'N/A'); ?>"
                                                           data-website="<?php echo e($service->website); ?>"
                                                           data-price="<?php echo e($service->price); ?>"
                                                           data-keywords="<?php echo e($service->keywords_string); ?>"
                                                           data-supplier-name="<?php echo e($service->partner->name ?? ''); ?>"
                                                           data-supplier-telegram="<?php echo e($service->partner->telegram ?? ''); ?>">
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                        <td><?php echo e($service->id); ?></td>
                                        <td>
                                            <strong><?php echo e($service->name); ?></strong>
                                            <?php if($service->keywords_string): ?>
                                                <br><small class="text-muted"><?php echo e(Str::limit($service->keywords_string, 50)); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($service->type === 'entity'): ?>
                                                <span class="badge badge-primary"><?php echo e($service->getTypeDisplayName()); ?></span>
                                            <?php elseif($service->type === 'backlink'): ?>
                                                <span class="badge badge-info"><?php echo e($service->getTypeDisplayName()); ?></span>
                                            <?php elseif($service->type === 'textlink'): ?>
                                                <span class="badge badge-warning"><?php echo e($service->getTypeDisplayName()); ?></span>
                                            <?php elseif($service->type === 'guest_post'): ?>
                                                <span class="badge badge-success"><?php echo e($service->getTypeDisplayName()); ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><?php echo e($service->getTypeDisplayName()); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo e($service->website); ?>" target="_blank" class="text-decoration-none">
                                                <?php echo e(Str::limit($service->website, 30)); ?>

                                                <i class="fas fa-external-link-alt fa-xs"></i>
                                            </a>
                                        </td>
                                        <?php if(auth()->user()->role && auth()->user()->role->name !== 'partner'): ?>
                                            <td><?php echo e($service->partner->name ?? 'N/A'); ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <small>
                                                <?php if($service->dr): ?> DR: <?php echo e($service->dr); ?> <?php endif; ?>
                                                <?php if($service->da): ?> DA: <?php echo e($service->da); ?> <?php endif; ?>
                                                <?php if($service->pa): ?> PA: <?php echo e($service->pa); ?> <?php endif; ?>
                                                <?php if($service->tf): ?> TF: <?php echo e($service->tf); ?> <?php endif; ?>
                                            </small>
                                        </td>
                                        <td><?php echo e($service->category ?? 'N/A'); ?></td>
                                        <td><strong><?php echo e($service->formatted_price); ?></strong></td>
                                        <td class="service-status-cell">
                                            <?php if($service->is_active): ?>
                                                <span class="badge badge-success text-white">
                                                    <i class="fas fa-check-circle"></i> Hoạt động
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-dark text-white">
                                                    <i class="fas fa-pause-circle"></i> Tạm dừng
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if(auth()->user()->role && in_array(auth()->user()->role->name, ['admin', 'it', 'assistant', 'partner'])): ?>
                                        <td class="approval-status-cell">
                                            <?php if($service->approval_status === 'pending'): ?>
                                                <span class="badge badge-warning text-dark">
                                                    <i class="fas fa-clock"></i> <?php echo e($service->getApprovalStatusDisplayName()); ?>

                                                </span>
                                            <?php elseif($service->approval_status === 'approved'): ?>
                                                <span class="badge badge-success text-white">
                                                    <i class="fas fa-check"></i> <?php echo e($service->getApprovalStatusDisplayName()); ?>

                                                </span>
                                                <?php if($service->approvedBy): ?>
                                                    <br><small class="text-success"><strong><?php echo e($service->approvedBy->name); ?></strong></small>
                                                <?php endif; ?>
                                            <?php elseif($service->approval_status === 'rejected'): ?>
                                                <span class="badge badge-danger text-white">
                                                    <i class="fas fa-times"></i> <?php echo e($service->getApprovalStatusDisplayName()); ?>

                                                </span>
                                                <?php if($service->rejection_reason): ?>
                                                    <br><small class="text-danger" title="<?php echo e($service->rejection_reason); ?>"><?php echo e(Str::limit($service->rejection_reason, 30)); ?></small>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?php echo e(route('services.show', $service)); ?>" 
                                                   class="btn btn-sm btn-info" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <?php if($service->canBeManageBy(auth()->user())): ?>
                                                    <a href="<?php echo e(route('services.edit', $service)); ?>" 
                                                       class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <form method="POST" action="<?php echo e(route('services.destroy', $service)); ?>" 
                                                          style="display: inline;" 
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa dịch vụ này?')">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if($service->canBeApprovedBy(auth()->user())): ?>
                                                    <form method="POST" action="<?php echo e(route('services.approve', $service)); ?>" 
                                                          style="display: inline;" 
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn duyệt dịch vụ này?')">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-sm btn-success" title="Duyệt">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <button type="button" class="btn btn-sm btn-danger" title="Từ chối"
                                                            onclick="showRejectModal(<?php echo e($service->id); ?>)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if(auth()->user()->role && auth()->user()->role->name === 'seoer' && $service->is_active && $service->isApproved()): ?>
                                                    <a href="<?php echo e(route('services.create-proposal', $service)); ?>" 
                                                       class="btn btn-sm btn-success" title="Tạo đề xuất">
                                                        <i class="fas fa-plus"></i> Đề xuất
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="<?php echo e(auth()->user()->role && auth()->user()->role->name === 'partner' ? '10' : (auth()->user()->role && auth()->user()->role->name === 'seoer' ? '11' : '11')); ?>" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">Chưa có dịch vụ nào</h5>
                                                <?php if(auth()->user()->role && auth()->user()->role->name === 'partner'): ?>
                                                    <p class="text-muted">Bạn chưa tạo dịch vụ nào.</p>
                                                    <a href="<?php echo e(route('services.create')); ?>" class="btn btn-primary">
                                                        <i class="fas fa-plus mr-2"></i>Tạo dịch vụ đầu tiên
                                                    </a>
                                                <?php else: ?>
                                                    <p class="text-muted">Không có dịch vụ nào phù hợp với bộ lọc.</p>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if($services->hasPages()): ?>
                        <div class="pagination-container">
                            <div class="pagination-info">
                                Hiển thị <?php echo e($services->firstItem()); ?> - <?php echo e($services->lastItem()); ?> 
                                trong tổng số <?php echo e($services->total()); ?> dịch vụ
                            </div>
                            <?php echo e($services->appends(request()->query())->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<style>
/* Custom badge styles for better visibility */
.badge {
    font-size: 0.85em;
    padding: 0.4em 0.8em;
    border-radius: 0.375rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.3em;
}

.badge-primary {
    background-color: #007bff !important;
    color: white !important;
}

.badge-info {
    background-color: #17a2b8 !important;
    color: white !important;
}

.badge-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

.badge-success {
    background-color: #28a745 !important;
    color: white !important;
}

.badge-danger {
    background-color: #dc3545 !important;
    color: white !important;
}

.badge-dark {
    background-color: #343a40 !important;
    color: white !important;
}

.badge-secondary {
    background-color: #6c757d !important;
    color: white !important;
}

/* Service table improvements */
.table td {
    vertical-align: middle;
}

.service-status-cell {
    min-width: 120px;
}

.approval-status-cell {
    min-width: 150px;
}

/* Filter improvements */
.form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    border: none;
    border-radius: 0.375rem;
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    border: none;
    border-radius: 0.375rem;
}

.filter-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1rem 1.5rem;
    border-radius: 0.75rem;
    margin-bottom: 1rem;
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.form-control-sm {
    height: calc(1.8125rem + 2px);
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    transition: all 0.15s ease-in-out;
}

.form-control-sm:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
    font-weight: 500;
}

.form-group label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.3rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-section .row {
    align-items: end;
}

.service-count {
    font-size: 0.75rem;
    color: #6c757d;
    font-weight: 500;
}
</style>
<script>
console.log('Script is loading...');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    
    // Format price inputs
    const priceInputs = document.querySelectorAll('#price_from, #price_to');
    priceInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Remove non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        input.addEventListener('blur', function() {
            if (this.value) {
                // Format number with thousands separator for display
                const value = parseInt(this.value);
                if (!isNaN(value) && value > 0) {
                    this.title = new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                }
            }
        });
    });

    // Auto submit form when filters change
    const autoSubmitElements = document.querySelectorAll('#type, #partner_name, #approval_status, #sort_by');
    autoSubmitElements.forEach(element => {
        element.addEventListener('change', function() {
            // Add a small delay to improve UX
            setTimeout(() => {
                this.form.submit();
            }, 100);
        });
    });

    // Enhanced search with Enter key
    const searchInput = document.querySelector('#search');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.form.submit();
            }
        });
    }
    
    <?php if(auth()->user()->role && auth()->user()->role->name === 'seoer'): ?>
    console.log('SEOer detected, initializing bulk functionality');
    
    // Simple function to update count and button
    function updateBulkActions() {
        var checkedBoxes = document.querySelectorAll('.service-checkbox:checked');
        var count = checkedBoxes.length;
        
        console.log('Checked boxes count:', count);
        
        // Update count display
        var countSpan = document.getElementById('selectedCount');
        if (countSpan) {
            countSpan.textContent = count;
        }
        
        // Update button state
        var bulkBtn = document.getElementById('bulkCreateProposalBtn');
        if (bulkBtn) {
            bulkBtn.disabled = (count === 0);
        }
    }
    
    // Listen for checkbox changes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('service-checkbox')) {
            console.log('Checkbox changed');
            updateBulkActions();
        }
    });
    
    // Select all button
    var selectAllBtn = document.getElementById('selectAllBtn');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Select all clicked');
            var checkboxes = document.querySelectorAll('.service-checkbox');
            checkboxes.forEach(function(cb) {
                cb.checked = true;
            });
            updateBulkActions();
        });
    }
    
    // Deselect all button
    var deselectAllBtn = document.getElementById('deselectAllBtn');
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Deselect all clicked');
            var checkboxes = document.querySelectorAll('.service-checkbox');
            checkboxes.forEach(function(cb) {
                cb.checked = false;
            });
            updateBulkActions();
        });
    }
    
    // Bulk create proposal button
    var bulkBtn = document.getElementById('bulkCreateProposalBtn');
    if (bulkBtn) {
        bulkBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Bulk create clicked');
            
            var checkedBoxes = document.querySelectorAll('.service-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Vui lòng chọn ít nhất một dịch vụ');
                return;
            }
            
            // Collect service data
            var services = [];
            checkedBoxes.forEach(function(cb) {
                console.log('Processing checkbox:', cb.value);
                console.log('Partner ID:', cb.getAttribute('data-partner-id'));
                console.log('Partner Name:', cb.getAttribute('data-partner-name'));
                
                services.push({
                    id: cb.value,
                    name: cb.getAttribute('data-service-name'),
                    partner_id: cb.getAttribute('data-partner-id'),
                    partner_name: cb.getAttribute('data-partner-name'),
                    website: cb.getAttribute('data-website'),
                    price: cb.getAttribute('data-price'),
                    keywords: cb.getAttribute('data-keywords') || '',
                    supplier_name: cb.getAttribute('data-supplier-name') || '',
                    supplier_telegram: cb.getAttribute('data-supplier-telegram') || ''
                });
            });
            
            console.log('Services to submit:', services);
            console.log('Services JSON:', JSON.stringify(services));
            
            // Create and submit form
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo e(route("services.bulk-create-proposals")); ?>';
            
            var csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '<?php echo e(csrf_token()); ?>';
            form.appendChild(csrfInput);
            
            var servicesInput = document.createElement('input');
            servicesInput.type = 'hidden';
            servicesInput.name = 'services';
            servicesInput.value = JSON.stringify(services);
            form.appendChild(servicesInput);
            
            document.body.appendChild(form);
            form.submit();
        });
    }
    
    // Initial update
    updateBulkActions();
    <?php endif; ?>
});

// Reject service modal
function showRejectModal(serviceId) {
    const modal = `
        <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Từ chối dịch vụ</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form method="POST" action="/services/${serviceId}/reject">
                        <div class="modal-body">
                            <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
                            <div class="form-group">
                                <label for="rejection_reason">Lý do từ chối:</label>
                                <textarea name="rejection_reason" id="rejection_reason" 
                                          class="form-control" rows="4" 
                                          placeholder="Nhập lý do từ chối dịch vụ..." required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-danger">Từ chối</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#rejectModal').remove();
    
    // Add modal to body
    $('body').append(modal);
    
    // Show modal
    $('#rejectModal').modal('show');
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/bl555pm.com/resources/views/services/index.blade.php ENDPATH**/ ?>