@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Chi tiết lệnh 301 #{{ $redirect301->id }}</h3>
                    <div class="card-tools">
                        @can('redirects.update')
                            <a href="{{ route('redirects-301.edit', $redirect301) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                        @endcan
                        <a href="{{ route('redirects-301.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Thông tin cơ bản</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>{{ $redirect301->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>URL đích:</strong></td>
                                    <td>
                                        <a href="{{ $redirect301->target_url }}" target="_blank" class="text-primary">
                                            {{ $redirect301->target_url }}
                                            <i class="fas fa-external-link-alt fa-xs"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Bao gồm www:</strong></td>
                                    <td>
                                        @if($redirect301->include_www)
                                            <span class="badge badge-success">Có</span>
                                        @else
                                            <span class="badge badge-secondary">Không</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Trạng thái:</strong></td>
                                    <td>
                                        <span class="badge {{ $redirect301->status_badge_class }}">
                                            {{ $redirect301->status_display_name }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Người tạo:</strong></td>
                                    <td>{{ $redirect301->creator->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày tạo:</strong></td>
                                    <td>{{ $redirect301->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cập nhật lần cuối:</strong></td>
                                    <td>{{ $redirect301->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5>Danh sách Domain</h5>
                            <div class="border p-3 rounded">
                                @foreach($redirect301->domain_list_array as $domain)
                                    <span class="badge badge-light mr-1 mb-1">{{ $domain }}</span>
                                @endforeach
                            </div>
                            
                            <small class="text-muted">
                                Tổng cộng: {{ count($redirect301->domain_list_array) }} domain(s)
                            </small>
                        </div>
                    </div>

                    @if($redirect301->cloudflare_rules)
                        <hr>
                        <h5>Cloudflare Page Rules</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Position</th>
                                        <th>URL/Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($redirect301->cloudflare_rules as $index => $rule)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-grip-vertical text-muted mr-2"></i>
                                                    <i class="fas fa-arrow-up text-muted mr-1"></i>
                                                    <i class="fas fa-arrow-down text-muted mr-2"></i>
                                                    {{ $index + 1 }}
                                                </div>
                                            </td>
                                            <td>
                                                <strong>{{ $rule['url_pattern'] }}</strong><br>
                                                <small class="text-muted">
                                                    Forwarding URL (Status Code: {{ $rule['status_code'] }} - Permanent Redirect, 
                                                    Url: {{ $rule['target_url'] }})
                                                </small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="custom-control custom-switch mr-2">
                                                        <input type="checkbox" class="custom-control-input" 
                                                               id="rule-{{ $index }}" checked disabled>
                                                        <label class="custom-control-label" for="rule-{{ $index }}"></label>
                                                    </div>
                                                    <button class="btn btn-sm btn-outline-secondary mr-1" disabled>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" disabled>
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Lưu ý:</strong> Đây là preview các Page Rules sẽ được tạo trên Cloudflare. 
                            Để tích hợp thực tế với Cloudflare API, cần cấu hình thêm API credentials.
                        </div>
                    @endif
                </div>

                <div class="card-footer">
                    @can('redirects.update')
                        <a href="{{ route('redirects-301.edit', $redirect301) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                    @endcan
                    
                    @can('redirects.delete')
                        <form action="{{ route('redirects-301.destroy', $redirect301) }}" 
                              method="POST" 
                              style="display: inline-block;"
                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa redirect này? Tất cả Page Rules liên quan sẽ bị xóa khỏi Cloudflare.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        </form>
                    @endcan
                    
                    <a href="{{ route('redirects-301.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
