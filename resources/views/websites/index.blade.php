@extends('layouts.app')

@section('title', 'Quản lý Website')

@section('breadcrumb')
    <li class="breadcrumb-item active">Quản lý Website</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-globe me-2"></i>
                Quản lý Website
            </h1>
            <div class="btn-group">
                @if(auth()->user()->hasPermission('websites.create'))
                <a href="{{ route('websites.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Thêm Website
                </a>
                @endif

                @if(auth()->user()->role && in_array(auth()->user()->role->name, ['admin', 'it']))
                <a href="{{ route('websites.cloudflare-sync') }}" class="btn btn-info">
                    <i class="fab fa-cloudflare me-2"></i>
                    Đồng bộ Cloudflare
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('websites.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Tìm kiếm</label>
                    <input type="text" class="form-control" id="search" name="search"
                           value="{{ request('search') }}" placeholder="Tên website hoặc seoer...">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="301" {{ request('status') === '301' ? 'selected' : '' }}>301</option>
                        <option value="not_configured" {{ request('status') === 'not_configured' ? 'selected' : '' }}>Site chưa cấu hình</option>
                        <option value="new_build" {{ request('status') === 'new_build' ? 'selected' : '' }}>Site mới dựng</option>
                        <option value="stop_seo" {{ request('status') === 'stop_seo' ? 'selected' : '' }}>Dừng seo</option>
                        <option value="recovered" {{ request('status') === 'recovered' ? 'selected' : '' }}>Thu hồi</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="seoer" class="form-label">Seoer</label>
                    <select class="form-select" id="seoer" name="seoer">
                        <option value="">Tất cả Seoer</option>
                        @php
                            $seoers = \App\Models\User::whereHas('role', function($q) {
                                $q->where('name', 'seoer');
                            })->get();
                        @endphp
                        @foreach($seoers as $seoerUser)
                            <option value="{{ $seoerUser->id }}" {{ request('seoer') == $seoerUser->id ? 'selected' : '' }}>
                                {{ $seoerUser->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="category" class="form-label">Phân loại</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">Tất cả phân loại</option>
                        <option value="brand" {{ request('category') === 'brand' ? 'selected' : '' }}>Brand</option>
                        <option value="phishing" {{ request('category') === 'phishing' ? 'selected' : '' }}>Phishing</option>
                        <option value="key_nganh" {{ request('category') === 'key_nganh' ? 'selected' : '' }}>Key ngành</option>
                        <option value="pbn" {{ request('category') === 'pbn' ? 'selected' : '' }}>PBN</option>
                    </select>
                </div>
                <div class="col-md-1">
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

<!-- Websites Table -->
<div class="card">
    <div class="card-body">
        @if($websites->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tên Website</th>
                            <th>Seoer</th>
                            <th>Phân loại</th>
                            <th>Trạng thái</th>
                            <th>301 Redirect</th>
                            <th>Ngày giao</th>
                            <th>Ngày hết hạn</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($websites as $website)
                        <tr>
                            <td>
                                <strong>{{ $website->name }}</strong>
                                @if($website->notes)
                                    <br><small class="text-muted">{{ Str::limit($website->notes, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($website->seoerUser)
                                    {{ $website->seoerUser->name }}
                                @else
                                    {{ $website->seoer ?? 'Chưa phân công' }}
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $website->getCategoryBadgeClass() }}" style="color: #000;">
                                    {{ $website->getCategoryDisplayName() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $website->getStatusBadgeClass() }}" style="color: #000;">
                                    {{ $website->getStatusDisplayName() }}
                                </span>
                            </td>
                            <td>
                                <div class="redirect-status-{{ $website->id }}">
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-spinner fa-spin me-1"></i>
                                        Kiểm tra...
                                    </span>
                                </div>
                            </td>
                            <td>
                                {{ $website->delivery_date ? $website->delivery_date->format('d/m/Y') : '-' }}
                            </td>
                            <td>
                                @if($website->expiry_date)
                                    {{ $website->expiry_date->format('d/m/Y') }}
                                    @if($website->expiry_date->isPast())
                                        <span class="badge bg-danger ms-1">Hết hạn</span>
                                    @elseif($website->expiry_date->diffInDays() <= 30)
                                        <span class="badge bg-warning ms-1">Sắp hết hạn</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('websites.show', $website) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(auth()->user()->hasPermission('websites.update'))
                                    <a href="{{ route('websites.edit', $website) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('websites.delete'))
                                    <form action="{{ route('websites.destroy', $website) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $websites->withQueryString()->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-globe fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Không có website nào</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['search', 'status', 'seoer']))
                        Không tìm thấy website phù hợp với bộ lọc.
                    @else
                        Hãy thêm website đầu tiên của bạn.
                    @endif
                </p>
                @if(auth()->user()->hasPermission('websites.create'))
                <a href="{{ route('websites.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Thêm Website
                </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check 301 status for all websites
    @foreach($websites as $website)
    check301StatusForWebsite({{ $website->id }});
    @endforeach
});

function check301StatusForWebsite(websiteId) {
    const statusDiv = document.querySelector(`.redirect-status-${websiteId}`);

    if (!statusDiv) {
        console.error(`Status div not found for website ${websiteId}`);
        return;
    }

    console.log(`Checking 301 status for website ${websiteId}`);

    fetch(`/websites/${websiteId}/301-status`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log(`Response status for website ${websiteId}:`, response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log(`301 status data for website ${websiteId}:`, data);

            // Check if response has error (like unauthenticated)
            if (data.message === 'Unauthenticated.') {
                statusDiv.innerHTML = `
                    <span class="badge bg-danger" title="Cần đăng nhập để xem">
                        <i class="fas fa-lock me-1"></i>
                        Cần đăng nhập
                    </span>
                `;
                return;
            }

            if (data.has_redirect) {
                const redirectUrl = data.redirect_to || 'N/A';
                const displayUrl = redirectUrl.length > 30 ? redirectUrl.substring(0, 30) + '...' : redirectUrl;
                statusDiv.innerHTML = `
                    <span class="badge bg-success" title="Redirect đến: ${redirectUrl}">
                        <i class="fas fa-external-link-alt me-1"></i>
                        ${displayUrl}
                    </span>
                `;
            } else {
                statusDiv.innerHTML = `
                    <span class="badge bg-warning text-dark" title="Không có 301 redirect">
                        <i class="fas fa-times me-1"></i>
                        Không có
                    </span>
                `;
            }

            if (data.error) {
                statusDiv.innerHTML = `
                    <span class="badge bg-danger" title="${data.error}">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Lỗi
                    </span>
                `;
            }
        })
        .catch(error => {
            console.error(`Error checking 301 status for website ${websiteId}:`, error);
            statusDiv.innerHTML = `
                <span class="badge bg-secondary" title="Không thể kiểm tra: ${error.message}">
                    <i class="fas fa-question me-1"></i>
                    N/A
                </span>
            `;
        });
}
</script>
@endsection

