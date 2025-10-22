<?php $__env->startSection('title', 'Chi tiết dịch vụ'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-eye mr-2"></i>
                        Chi tiết dịch vụ: <?php echo e($service->name); ?>

                    </h3>
                    <div class="card-tools">
                        <a href="<?php echo e(route('services.index')); ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i>Quay lại
                        </a>
                        <?php if($service->canBeManageBy(auth()->user())): ?>
                            <a href="<?php echo e(route('services.edit', $service)); ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit mr-1"></i>Chỉnh sửa
                            </a>
                        <?php endif; ?>
                        <?php if(auth()->user()->role && auth()->user()->role->name === 'seoer' && $service->is_active): ?>
                            <a href="<?php echo e(route('services.create-proposal', $service)); ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-plus mr-1"></i>Tạo đề xuất
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle mr-2"></i>Thông tin cơ bản
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>ID:</strong></td>
                                            <td><?php echo e($service->id); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tên dịch vụ:</strong></td>
                                            <td><?php echo e($service->name); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Loại dịch vụ:</strong></td>
                                            <td>
                                                <span class="badge badge-info"><?php echo e($service->getTypeDisplayName()); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Website:</strong></td>
                                            <td>
                                                <a href="<?php echo e($service->website); ?>" target="_blank" class="text-decoration-none">
                                                    <?php echo e($service->website); ?>

                                                    <i class="fas fa-external-link-alt fa-xs"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Đối tác:</strong></td>
                                            <td><?php echo e($service->partner->name ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Giá dịch vụ:</strong></td>
                                            <td><strong class="text-success"><?php echo e($service->formatted_price); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Lĩnh vực:</strong></td>
                                            <td><?php echo e($service->category ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Trạng thái:</strong></td>
                                            <td>
                                                <?php if($service->is_active): ?>
                                                    <span class="badge badge-success">Hoạt động</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Tạm dừng</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- SEO Metrics -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-line mr-2"></i>Chỉ số SEO
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php if($service->dr): ?>
                                            <div class="col-6 mb-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-info">DR</span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Domain Rating</span>
                                                        <span class="info-box-number"><?php echo e($service->dr); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if($service->da): ?>
                                            <div class="col-6 mb-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-success">DA</span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Domain Authority</span>
                                                        <span class="info-box-number"><?php echo e($service->da); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if($service->pa): ?>
                                            <div class="col-6 mb-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-warning">PA</span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Page Authority</span>
                                                        <span class="info-box-number"><?php echo e($service->pa); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if($service->tf): ?>
                                            <div class="col-6 mb-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-danger">TF</span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text">Trust Flow</span>
                                                        <span class="info-box-number"><?php echo e($service->tf); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if($service->ip): ?>
                                        <div class="mt-3">
                                            <strong>IP Address:</strong> 
                                            <code><?php echo e($service->ip); ?></code>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Keywords -->
                    <?php if($service->keywords_string): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-tags mr-2"></i>Keywords
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <?php
                                            $keywords = is_array($service->keywords) ? $service->keywords : explode(', ', $service->keywords_string);
                                        ?>
                                        <?php $__currentLoopData = $keywords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $keyword): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="badge badge-light mr-1 mb-1"><?php echo e(trim($keyword)); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Description -->
                    <?php if($service->description): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-file-alt mr-2"></i>Mô tả dịch vụ
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0"><?php echo nl2br(e($service->description)); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Service Proposals -->
                    <?php if($service->serviceProposals->count() > 0): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-clipboard-list mr-2"></i>
                                            Đề xuất từ dịch vụ này (<?php echo e($service->serviceProposals->count()); ?>)
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>ID</th>
                                                        <th>Seoer</th>
                                                        <th>Số lượng</th>
                                                        <th>Tổng tiền</th>
                                                        <th>Trạng thái</th>
                                                        <th>Ngày tạo</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $__currentLoopData = $service->serviceProposals->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $proposal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr>
                                                            <td>
                                                                <a href="<?php echo e(route('service-proposals.show', $proposal)); ?>">
                                                                    #<?php echo e($proposal->id); ?>

                                                                </a>
                                                            </td>
                                                            <td><?php echo e($proposal->user->name ?? 'N/A'); ?></td>
                                                            <td><?php echo e($proposal->quantity); ?></td>
                                                            <td><?php echo e(number_format($proposal->amount, 0, ',', '.')); ?> VNĐ</td>
                                                            <td>
                                                                <span class="badge <?php echo e($proposal->getStatusBadgeClass()); ?>">
                                                                    <?php echo e($proposal->getStatusDisplayName()); ?>

                                                                </span>
                                                            </td>
                                                            <td><?php echo e($proposal->created_at->format('d/m/Y')); ?></td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php if($service->serviceProposals->count() > 5): ?>
                                            <div class="text-center">
                                                <small class="text-muted">
                                                    Và <?php echo e($service->serviceProposals->count() - 5); ?> đề xuất khác...
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Timestamps -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <small class="text-muted">
                                <i class="fas fa-clock mr-1"></i>
                                Tạo lúc: <?php echo e($service->created_at->format('d/m/Y H:i')); ?>

                                <?php if($service->updated_at != $service->created_at): ?>
                                    | Cập nhật: <?php echo e($service->updated_at->format('d/m/Y H:i')); ?>

                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/bl555pm.com/resources/views/services/show.blade.php ENDPATH**/ ?>