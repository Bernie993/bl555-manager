@extends('layouts.app')

@section('title', 'Quản lý Ngân sách')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-wallet me-2"></i>
                        Quản lý Ngân sách
                    </h5>
                    @can('budgets.create')
                    <a href="{{ route('budgets.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Thêm Ngân sách
                    </a>
                    @endcan
                </div>

                <!-- Filters -->
                <div class="card-body">
                    <form method="GET" action="{{ route('budgets.index') }}" class="mb-4">
                        <div class="row">
                            <!-- Search -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="search">Tìm kiếm:</label>
                                    <input type="text" name="search" id="search" class="form-control" 
                                           placeholder="Seoer, mô tả..." value="{{ request('search') }}">
                                </div>
                            </div>

                            <!-- Date Range Filter -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_range">Khoảng thời gian:</label>
                                    <input type="text" name="date_range" id="date_range" class="form-control" 
                                           placeholder="Chọn khoảng thời gian" 
                                           value="{{ request('date_range') }}" readonly>
                                    <input type="hidden" name="start_date" id="start_date" value="{{ request('start_date') }}">
                                    <input type="hidden" name="end_date" id="end_date" value="{{ request('end_date') }}">
                                </div>
                            </div>

                            <!-- Filter by Domain -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="domain">Domain:</label>
                                    <input type="text" 
                                           name="domain" 
                                           id="domain" 
                                           class="form-control" 
                                           placeholder="Nhập domain..." 
                                           value="{{ request('domain') }}"
                                           autocomplete="off">
                                    <div id="domain-suggestions" class="dropdown-menu" style="display: none; position: absolute; z-index: 1000; width: 100%;"></div>
                                </div>
                            </div>

                            <!-- Filter by Service Type -->
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="service_type">Loại dịch vụ:</label>
                                    <select name="service_type" id="service_type" class="form-control">
                                        <option value="">Tất cả</option>
                                        @foreach(\App\Models\Service::TYPES as $key => $value)
                                            <option value="{{ $key }}" {{ request('service_type') == $key ? 'selected' : '' }}>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Filter by Seoer (for non-seoer roles) -->
                            @if(auth()->user()->role && auth()->user()->role->name !== 'seoer')
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="seoer">Seoer:</label>
                                    <select name="seoer" id="seoer" class="form-control">
                                        <option value="">Tất cả</option>
                                        @if(isset($seoers))
                                            @foreach($seoers as $seoer)
                                                <option value="{{ $seoer }}" {{ request('seoer') == $seoer ? 'selected' : '' }}>
                                                    {{ $seoer }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>
                                    Lọc
                                </button>
                                <a href="{{ route('budgets.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>
                                    Xóa bộ lọc
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Results Summary -->
                @if($budgets->total() > 0)
                <div class="card-body border-top">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">Tổng số ngân sách</h6>
                                <h4 class="text-primary">{{ $budgets->total() }}</h4>
                            </div>
                        </div>
                        @if(isset($totalBudgetAmount))
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">Tổng ngân sách</h6>
                                <h4 class="text-info">{{ number_format($totalBudgetAmount, 0, ',', '.') }} VNĐ</h4>
                            </div>
                        </div>
                        @endif
                        @if(isset($totalSpentAmount))
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">Đã chi tiêu</h6>
                                <h4 class="text-danger">{{ number_format($totalSpentAmount, 0, ',', '.') }} VNĐ</h4>
                            </div>
                        </div>
                        @endif
                        @if(isset($totalRemainingAmount))
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">Còn lại</h6>
                                <h4 class="text-success">{{ number_format($totalRemainingAmount, 0, ',', '.') }} VNĐ</h4>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Budget List -->
                <div class="card-body">
                    @if($budgets->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Seoer</th>
                                        <th>Tổng ngân sách</th>
                                        <th>Đã chi tiêu</th>
                                        <th>Còn lại</th>
                                        <th>Tiến độ</th>
                                        <th>Kỳ hạn</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($budgets as $budget)
                                    <tr>
                                        <td>
                                            <strong>{{ $budget->seoer }}</strong>
                                            @if($budget->description)
                                                <br>
                                                <small class="text-muted">{{ Str::limit($budget->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">{{ $budget->formatted_total_budget }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-danger">{{ $budget->formatted_spent_amount }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">{{ $budget->formatted_remaining_amount }}</span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 1.25rem;">
                                                <div class="progress-bar 
                                                    @if($budget->spending_percentage >= 90) bg-danger
                                                    @elseif($budget->spending_percentage >= 70) bg-warning
                                                    @else bg-success
                                                    @endif" 
                                                    role="progressbar" 
                                                    style="width: {{ min($budget->spending_percentage, 100) }}%"
                                                    aria-valuenow="{{ $budget->spending_percentage }}" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100">
                                                    <span class="fw-bold">{{ number_format($budget->spending_percentage, 1) }}%</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($budget->period_start && $budget->period_end)
                                                <small>
                                                    {{ $budget->period_start->format('d/m/Y') }}<br>
                                                    {{ $budget->period_end->format('d/m/Y') }}
                                                </small>
                                            @elseif($budget->period_start)
                                                <small>Từ {{ $budget->period_start->format('d/m/Y') }}</small>
                                            @elseif($budget->period_end)
                                                <small>Đến {{ $budget->period_end->format('d/m/Y') }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $budget->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('budgets.read')
                                                <a href="{{ route('budgets.show', $budget) }}" 
                                                   class="btn btn-sm btn-outline-info hover-lift" 
                                                   title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @endcan
                                                
                                                @can('budgets.update')
                                                <a href="{{ route('budgets.edit', $budget) }}" 
                                                   class="btn btn-sm btn-outline-warning hover-lift" 
                                                   title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endcan
                                                
                                                @can('budgets.delete')
                                                <form action="{{ route('budgets.destroy', $budget) }}" 
                                                      method="POST" 
                                                      style="display: inline-block;"
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa ngân sách này?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-danger hover-lift" 
                                                            title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $budgets->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-wallet fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Không có ngân sách nào</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['search', 'month', 'year', 'domain', 'service_type', 'seoer']))
                                    Không tìm thấy ngân sách nào phù hợp với bộ lọc.
                                @else
                                    Chưa có ngân sách nào được tạo.
                                @endif
                            </p>
                            @can('budgets.create')
                            <a href="{{ route('budgets.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                Tạo ngân sách đầu tiên
                            </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Date Range Picker CSS -->
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.css" />
<style>
.daterangepicker {
    z-index: 9999 !important;
}
.daterangepicker .ranges li {
    color: #333;
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 8px;
    cursor: pointer;
    padding: 8px 12px;
}
.daterangepicker .ranges li:hover {
    background-color: #e6e6e6;
    border-color: #adadad;
}
.daterangepicker .ranges li.active {
    background-color: #667eea;
    border-color: #667eea;
    color: white;
}
.daterangepicker .calendar-table {
    border: 1px solid #ddd;
    border-radius: 4px;
}
.daterangepicker td.active, .daterangepicker td.active:hover {
    background-color: #667eea;
    border-color: #667eea;
    color: #fff;
}
.daterangepicker .drp-buttons .btn {
    margin-right: 8px;
}
.daterangepicker .drp-buttons .btn.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

/* Domain autocomplete styles */
#domain-suggestions {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

#domain-suggestions .dropdown-item {
    padding: 8px 12px;
    cursor: pointer;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    color: #333;
}

#domain-suggestions .dropdown-item:hover,
#domain-suggestions .dropdown-item.active {
    background-color: #667eea;
    color: white;
}

#domain-suggestions .dropdown-item:not(:last-child) {
    border-bottom: 1px solid #eee;
}

.form-group {
    position: relative;
}
</style>
@endpush

@push('scripts')
<!-- Date Range Picker JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>

<script>
$(document).ready(function() {
    console.log('Document ready...');
    console.log('jQuery version:', $.fn.jquery);
    console.log('Moment available:', typeof moment !== 'undefined');
    console.log('DateRangePicker available:', typeof $.fn.daterangepicker !== 'undefined');
    
    // Đợi một chút để đảm bảo tất cả thư viện đã load
    setTimeout(function() {
        console.log('Initializing date range picker...');
        
        try {
            $('#date_range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Cancel',
                    applyLabel: 'Apply',
                    format: 'DD/MM/YYYY',
                    separator: ' - ',
                    customRangeLabel: 'Custom Range',
                    daysOfWeek: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
                    monthNames: [
                        'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
                        'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
                    ],
                    firstDay: 1
                },
                ranges: {
                   'Hôm nay': [moment(), moment()],
                   'Hôm qua': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                   '7 Ngày trước': [moment().subtract(6, 'days'), moment()],
                   '30 ngày trước': [moment().subtract(29, 'days'), moment()],
                   'Tháng này': [moment().startOf('month'), moment().endOf('month')],
                   'Tháng trước': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                opens: 'left',
                drops: 'down',
                buttonClasses: 'btn btn-sm',
                applyClass: 'btn-success',
                cancelClass: 'btn-secondary'
            });
            
            console.log('DateRangePicker initialized successfully!');
            
            // Handle events
            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                console.log('Apply clicked');
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
                $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
                $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
                $(this).closest('form').submit();
            });

            $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
                console.log('Cancel clicked');
                $(this).val('');
                $('#start_date').val('');
                $('#end_date').val('');
                $(this).closest('form').submit();
            });
            
            // Set initial value if dates are provided
            @if(request('start_date') && request('end_date'))
                $('#date_range').val('{{ \Carbon\Carbon::parse(request("start_date"))->format("d/m/Y") }}' + ' - ' + '{{ \Carbon\Carbon::parse(request("end_date"))->format("d/m/Y") }}');
            @endif
            
        } catch (error) {
            console.error('Error initializing date range picker:', error);
        }
    }, 500);

    // Auto-submit form when other filters change  
    $('#service_type, #seoer').change(function() {
        $(this).closest('form').submit();
    });
    
    // Domain autocomplete functionality
    let domainTimeout;
    let currentSuggestionIndex = -1;
    const domainSuggestionsUrl = '{{ url("/api/budgets/domain-suggestions") }}';
    
    $('#domain').on('input', function() {
        const query = $(this).val().trim();
        const suggestionsDiv = $('#domain-suggestions');
        
        clearTimeout(domainTimeout);
        
        if (query.length < 2) {
            suggestionsDiv.hide().empty();
            return;
        }
        
        domainTimeout = setTimeout(function() {
            console.log('Making AJAX request to:', domainSuggestionsUrl, 'with query:', query);
            $.ajax({
                url: domainSuggestionsUrl,
                method: 'GET',
                data: { q: query },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(domains) {
                    console.log('Received domains:', domains);
                    suggestionsDiv.empty();
                    currentSuggestionIndex = -1;
                    
                    if (domains.length > 0) {
                        domains.forEach(function(domain, index) {
                            const item = $('<button type="button" class="dropdown-item">' + domain + '</button>');
                            item.on('click', function() {
                                $('#domain').val(domain);
                                suggestionsDiv.hide();
                                $('#domain').closest('form').submit();
                            });
                            suggestionsDiv.append(item);
                        });
                        suggestionsDiv.show();
                    } else {
                        suggestionsDiv.hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching domain suggestions:', error);
                    suggestionsDiv.hide();
                }
            });
        }, 300);
    });
    
    // Handle keyboard navigation for suggestions
    $('#domain').on('keydown', function(e) {
        const suggestionsDiv = $('#domain-suggestions');
        const items = suggestionsDiv.find('.dropdown-item');
        
        if (!suggestionsDiv.is(':visible') || items.length === 0) {
            return;
        }
        
        switch(e.keyCode) {
            case 38: // Arrow Up
                e.preventDefault();
                currentSuggestionIndex = Math.max(0, currentSuggestionIndex - 1);
                updateSuggestionHighlight(items);
                break;
                
            case 40: // Arrow Down
                e.preventDefault();
                currentSuggestionIndex = Math.min(items.length - 1, currentSuggestionIndex + 1);
                updateSuggestionHighlight(items);
                break;
                
            case 13: // Enter
                e.preventDefault();
                if (currentSuggestionIndex >= 0) {
                    items.eq(currentSuggestionIndex).click();
                } else {
                    $(this).closest('form').submit();
                }
                break;
                
            case 27: // Escape
                e.preventDefault();
                suggestionsDiv.hide();
                currentSuggestionIndex = -1;
                break;
        }
    });
    
    function updateSuggestionHighlight(items) {
        items.removeClass('active');
        if (currentSuggestionIndex >= 0) {
            items.eq(currentSuggestionIndex).addClass('active');
        }
    }
    
    // Hide suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#domain, #domain-suggestions').length) {
            $('#domain-suggestions').hide();
        }
    });
    
    // Submit form when domain input loses focus (if value changed)
    let lastDomainValue = $('#domain').val();
    $('#domain').on('blur', function() {
        const currentValue = $(this).val();
        if (currentValue !== lastDomainValue) {
            lastDomainValue = currentValue;
            if (currentValue.trim() === '' || currentValue.length >= 2) {
                setTimeout(() => {
                    if (!$('#domain-suggestions').is(':hover')) {
                        $(this).closest('form').submit();
                    }
                }, 200);
            }
        }
    });
});
</script>
@endpush
