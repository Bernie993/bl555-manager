<?php

namespace App\Http\Controllers;

use App\Models\ServiceProposal;
use App\Models\Budget;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceProposalController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Send status change notification
     */
    private function sendStatusChangeNotification(ServiceProposal $proposal, string $oldStatus): void
    {
        $this->notificationService->sendProposalStatusNotification($proposal, $oldStatus, auth()->user());
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ServiceProposal::with(['creator', 'approver', 'budget', 'service']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('service_name', 'like', "%{$search}%")
                    ->orWhere('supplier_name', 'like', "%{$search}%");
            });
        }

        // Filter by service
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        // Filter by partner (via service relationship)
        if ($request->filled('partner_id')) {
            $query->whereHas('service', function($q) use ($request) {
                $q->where('partner_id', $request->partner_id);
            });
        }

        // Filter by target domain
        if ($request->filled('target_domain')) {
            $query->where('target_domain', $request->target_domain);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by creator based on role
        $user = auth()->user();
        $userRole = $user->role->name ?? '';

        // Filter based on role
        if ($userRole === 'seoer') {
            // Seoer sees their own proposals
            $query->where('created_by', auth()->id());
        } elseif ($userRole === 'partner') {
            // Partner sees only proposals using their services
            $partnerServiceIds = $user->services()->pluck('id');
            $query->whereIn('service_id', $partnerServiceIds);
        }
        // Admin, IT, Assistant can see all proposals

        $proposals = $query->orderBy('created_at', 'desc')->paginate(10);

        // Calculate total amount - always show total
        $totalQuery = ServiceProposal::query();
        
        // Apply the same filters as the main query
        if ($request->filled('search')) {
            $search = $request->search;
            $totalQuery->where(function($q) use ($search) {
                $q->where('service_name', 'like', "%{$search}%")
                    ->orWhere('supplier_name', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('service_id')) {
            $totalQuery->where('service_id', $request->service_id);
        }
        
        if ($request->filled('partner_id')) {
            $totalQuery->whereHas('service', function($q) use ($request) {
                $q->where('partner_id', $request->partner_id);
            });
        }
        
        if ($request->filled('target_domain')) {
            $totalQuery->where('target_domain', $request->target_domain);
        }
        
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $totalQuery->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }
        
        if ($request->filled('status')) {
            $totalQuery->where('status', $request->status);
        }
        
        // Apply role-based filtering
        if ($userRole === 'seoer') {
            $totalQuery->where('created_by', auth()->id());
        } elseif ($userRole === 'partner') {
            $partnerServiceIds = $user->services()->pluck('id');
            $totalQuery->whereIn('service_id', $partnerServiceIds);
        }
        
        $totalAmount = $totalQuery->sum('amount');
        
        // Debug log
        \Log::info('Total amount calculation:', [
            'target_domain' => $request->target_domain,
            'total_amount' => $totalAmount,
            'query_sql' => $totalQuery->toSql(),
            'query_bindings' => $totalQuery->getBindings()
        ]);

        return view('service-proposals.index', compact('proposals', 'totalAmount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = auth()->user();

        // Get user's budgets (for Seoer, get their own budgets)
        if ($user->role && $user->role->name === 'seoer') {
            $budgets = $user->budgets()->get();
        } else {
            // For other roles, get all budgets
            $budgets = Budget::all();
        }

        // Get websites for domain selection - only show websites assigned to current seoer
        $websites = collect();
        if ($user->role && $user->role->name === 'seoer') {
            // For seoer, only show websites assigned to them
            $websites = \App\Models\Website::where('seoer_id', $user->id)->orderBy('name')->get();
        } else {
            // For other roles (admin, it), show all websites
            $websites = \App\Models\Website::orderBy('name')->get();
        }

        // Handle bulk services data
        $bulkServices = null;
        if ($request->has('bulk_services')) {
            $rawData = $request->bulk_services;
            $decodedData = base64_decode($rawData);
            $bulkServices = json_decode($decodedData, true);

            \Log::info('Bulk services received:', [
                'raw_data' => $rawData,
                'decoded_data' => $decodedData,
                'bulkServices' => $bulkServices,
                'is_array' => is_array($bulkServices),
                'count' => is_array($bulkServices) ? count($bulkServices) : 0
            ]);
        }

        \Log::info('ServiceProposal create method called', [
            'has_bulk_services' => $request->has('bulk_services'),
            'bulkServices' => $bulkServices,
            'request_all' => $request->all(),
            'bulk_services_raw' => $request->get('bulk_services')
        ]);

        // Get pre-filled data from service if coming from services page (single service)
        $serviceData = $request->only([
            'service_id',
            'service_name',
            'partner_website',
            'unit_price',
            'keywords',
            'supplier_name',
            'supplier_telegram'
        ]);

        return view('service-proposals.create', compact('budgets', 'serviceData', 'bulkServices', 'websites'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // For Seoer, check if they have budgets
        if ($user->role && $user->role->name === 'seoer') {
            $userBudgets = $user->budgets()->get();
            if ($userBudgets->isEmpty()) {
                return redirect()->route('service-proposals.create')
                    ->with('error', 'Bạn chưa có ngân sách nào. Vui lòng liên hệ quản lý để tạo ngân sách.')
                    ->withInput();
            }
        }

        // Debug: Log all request data
        \Log::info('Service Proposal Store Request:', $request->all());

        // Handle bulk proposal creation (new method)
        if ($request->has('is_bulk_all') && $request->is_bulk_all) {
            return $this->storeBulkAllProposals($request);
        }

        // Handle bulk proposal creation (old method)
        if ($request->has('is_bulk') && $request->is_bulk) {
            return $this->storeBulkProposals($request);
        }

        // Single proposal validation and creation
        $request->validate([
            'service_id' => 'nullable|exists:services,id',
            'service_name' => 'required|string|max:255',
            'target_domain' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'supplier_name' => 'nullable|string|max:255',
            'supplier_phone' => 'nullable|string|max:20', // Temporary for backward compatibility
            'supplier_telegram' => 'nullable|string|max:255',
            'proposal_link' => 'required|url|max:500',
            'amount' => 'required|numeric|min:0',
            'budget_id' => 'required|exists:budgets,id',
            'notes' => 'nullable|string',
            'partner_website' => 'nullable|url|max:500',
            'keywords' => 'nullable|string',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $data = $request->only([
            'service_id',
            'service_name',
            'target_domain',
            'quantity',
            'supplier_name',
            'supplier_phone', // Temporary for backward compatibility
            'supplier_telegram',
            'proposal_link',
            'amount',
            'unit_price',
            'budget_id',
            'notes',
            'partner_website',
            'keywords'
        ]);
        $data['created_by'] = auth()->id();
        $data['status'] = 'pending';

        \Log::info('Service Proposal Data to Create:', $data);

        $proposal = ServiceProposal::create($data);

        // Send notification
        $this->notificationService->sendProposalCreatedNotification($proposal);

        return redirect()->route('service-proposals.index')
            ->with('success', 'Đề xuất dịch vụ đã được tạo thành công và đang chờ duyệt.');
    }

    /**
     * Store bulk service proposals
     */
    private function storeBulkProposals(Request $request)
    {
        $request->validate([
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.service_name' => 'required|string|max:255',
            'services.*.target_domain' => 'nullable|string|max:255',
            'services.*.quantity' => 'required|integer|min:1',
            'services.*.unit_price' => 'required|numeric|min:0',
            'services.*.amount' => 'required|numeric|min:0',
            'supplier_name' => 'required|string|max:255',
            'supplier_telegram' => 'nullable|string|max:255',
            'proposal_link' => 'required|url|max:500',
            'budget_id' => 'required|exists:budgets,id',
            'notes' => 'nullable|string',
        ]);

        $createdProposals = [];
        $totalAmount = 0;

        // Create individual proposals for each service
        foreach ($request->services as $serviceData) {
            $proposalData = [
                'service_id' => $serviceData['service_id'],
                'service_name' => $serviceData['service_name'],
                'target_domain' => $serviceData['target_domain'] ?? null,
                'quantity' => $serviceData['quantity'],
                'unit_price' => $serviceData['unit_price'],
                'amount' => $serviceData['amount'],
                'supplier_name' => $request->supplier_name,
                'supplier_telegram' => $request->supplier_telegram,
                'proposal_link' => $request->proposal_link,
                'budget_id' => $request->budget_id,
                'notes' => $request->notes,
                'keywords' => $serviceData['keywords'] ?? null,
                'created_by' => auth()->id(),
                'status' => 'pending',
            ];

            $proposal = ServiceProposal::create($proposalData);
            $createdProposals[] = $proposal;
            $totalAmount += $serviceData['amount'];

            // Send notification for each proposal
            $this->notificationService->sendProposalCreatedNotification($proposal);
        }

        $proposalCount = count($createdProposals);
        $partnerName = $request->supplier_name;

        return redirect()->route('service-proposals.index')
            ->with('success', "Đã tạo thành công {$proposalCount} đề xuất dịch vụ cho đối tác {$partnerName} với tổng giá trị " . number_format($totalAmount) . " VNĐ.");
    }

    /**
     * Store bulk service proposals (new method for all partners at once)
     */
    private function storeBulkAllProposals(Request $request)
    {
        \Log::info('StoreBulkAllProposals called with data:', $request->all());

        try {
            $request->validate([
                'proposals' => 'required|json',
                'budget_id' => 'required|exists:budgets,id',
            ]);
        } catch (\Exception $e) {
            \Log::error('Validation failed:', ['error' => $e->getMessage(), 'data' => $request->all()]);
            return redirect()->route('service-proposals.create')
                ->with('error', 'Dữ liệu không hợp lệ: ' . $e->getMessage());
        }

        $proposals = json_decode($request->proposals, true);
        \Log::info('Decoded proposals:', $proposals);

        if (empty($proposals)) {
            \Log::warning('No proposals to create');
            return redirect()->route('service-proposals.index')
                ->with('error', 'Không có đề xuất nào để tạo.');
        }

        $createdProposals = [];
        $totalAmount = 0;
        $totalCount = 0;

        try {
            foreach ($proposals as $proposalIndex => $proposalData) {
                \Log::info("Processing proposal {$proposalIndex}:", $proposalData);
                
                $partnerId = $proposalData['partner_id'];
                $services = $proposalData['services'];

                foreach ($services as $serviceIndex => $serviceData) {
                    \Log::info("Processing service {$serviceIndex} for partner {$partnerId}:", $serviceData);
                    
                    try {
                        $proposalRecord = [
                            'service_id' => $serviceData['service_id'],
                            'service_name' => $serviceData['service_name'],
                            'target_domain' => $serviceData['target_domain'] ?? null,
                            'quantity' => $serviceData['quantity'],
                            'unit_price' => $serviceData['unit_price'],
                            'amount' => $serviceData['amount'],
                            'supplier_name' => $proposalData['supplier_name'],
                            'supplier_telegram' => $proposalData['supplier_telegram'],
                            'proposal_link' => $proposalData['proposal_link'],
                            'budget_id' => $request->budget_id,
                            'notes' => $proposalData['notes'],
                            'keywords' => $serviceData['keywords'],
                            'created_by' => auth()->id() ?? 1,
                            'status' => 'pending',
                        ];

                        \Log::info("Creating proposal record:", $proposalRecord);
                        
                        $proposal = ServiceProposal::create($proposalRecord);
                        $createdProposals[] = $proposal;
                        $totalAmount += $serviceData['amount'];
                        $totalCount++;

                        \Log::info("Proposal created successfully with ID: {$proposal->id}");

                        // Send notification for each proposal
                        try {
                            $this->notificationService->sendProposalCreatedNotification($proposal);
                            \Log::info("Notification sent for proposal ID: {$proposal->id}");
                        } catch (\Exception $e) {
                            \Log::error("Failed to send notification for proposal ID {$proposal->id}:", ['error' => $e->getMessage()]);
                        }
                    } catch (\Exception $e) {
                        \Log::error("Failed to create proposal for service {$serviceIndex} in partner {$partnerId}:", [
                            'error' => $e->getMessage(),
                            'serviceData' => $serviceData,
                            'proposalData' => $proposalData
                        ]);
                        throw $e; // Re-throw to stop the process
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error creating bulk proposals:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'createdCount' => $totalCount
            ]);
            
            return redirect()->route('service-proposals.create')
                ->with('error', 'Có lỗi xảy ra khi tạo đề xuất: ' . $e->getMessage());
        }

        $partnerCount = count($proposals);

        \Log::info('Bulk proposals created successfully:', [
            'totalCount' => $totalCount,
            'partnerCount' => $partnerCount,
            'totalAmount' => $totalAmount,
            'createdProposals' => count($createdProposals)
        ]);

        return redirect()->route('service-proposals.index')
            ->with('success', "Đã tạo thành công {$totalCount} đề xuất dịch vụ cho {$partnerCount} đối tác với tổng giá trị " . number_format($totalAmount) . " VNĐ.");
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceProposal $serviceProposal)
    {
        $serviceProposal->load(['creator', 'approver', 'budget']);
        return view('service-proposals.show', compact('serviceProposal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceProposal $serviceProposal)
    {
        $user = auth()->user();
        $userRole = $user->role->name ?? '';

        // Admin and IT can edit any proposal
        // Seoer can only edit their own proposals when status is pending
        if (!in_array($userRole, ['admin', 'it'])) {
            if ($serviceProposal->created_by !== auth()->id() || $serviceProposal->status !== 'pending') {
                abort(403, 'Bạn không thể chỉnh sửa đề xuất này.');
            }
        }

        $budgets = Budget::all();

        // Get websites for domain selection - only show websites assigned to current seoer
        $websites = collect();
        if ($user->role && $user->role->name === 'seoer') {
            // For seoer, only show websites assigned to them
            $websites = \App\Models\Website::where('seoer_id', $user->id)->orderBy('name')->get();
        } else {
            // For other roles (admin, it), show all websites
            $websites = \App\Models\Website::orderBy('name')->get();
        }

        return view('service-proposals.edit', compact('serviceProposal', 'budgets', 'websites'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceProposal $serviceProposal)
    {
        $user = auth()->user();
        $userRole = $user->role->name ?? '';

        // Admin and IT can edit any proposal
        // Seoer can only edit their own proposals when status is pending
        if (!in_array($userRole, ['admin', 'it'])) {
            if ($serviceProposal->created_by !== auth()->id() || $serviceProposal->status !== 'pending') {
                abort(403, 'Bạn không thể chỉnh sửa đề xuất này.');
            }
        }

        $request->validate([
            'service_name' => 'required|string|max:255',
            'target_domain' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'supplier_name' => 'required|string|max:255',
            'supplier_telegram' => 'nullable|string|max:255',
            'proposal_link' => 'nullable|url|max:500',
            'unit_price' => 'required|numeric|min:0',
            'amount' => 'required|numeric|min:0',
            'budget_id' => 'nullable|exists:budgets,id',
            'notes' => 'nullable|string',
        ]);

        $serviceProposal->update($request->all());

        return redirect()->route('service-proposals.index')
            ->with('success', 'Đề xuất dịch vụ đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceProposal $serviceProposal)
    {
        $user = auth()->user();
        $userRole = $user->role->name ?? '';

        // Check if deletion is allowed based on status
        $allowedStatuses = ['pending', 'approved'];
        if (!in_array($serviceProposal->status, $allowedStatuses)) {
            abort(403, 'Không thể xóa đề xuất ở trạng thái này. Chỉ có thể xóa khi đang chờ duyệt hoặc đã duyệt.');
        }

        // Admin and IT can delete any proposal (with allowed status)
        // Seoer can only delete their own proposals when status is pending
        if (!in_array($userRole, ['admin', 'it'])) {
            if ($serviceProposal->created_by !== auth()->id() || $serviceProposal->status !== 'pending') {
                abort(403, 'Bạn không thể xóa đề xuất này.');
            }
        }

        $serviceProposal->delete();

        return redirect()->route('service-proposals.index')
            ->with('success', 'Đề xuất dịch vụ đã được xóa thành công.');
    }

    /**
     * Approve a service proposal (Admin only)
     */
    public function approve(ServiceProposal $serviceProposal)
    {
        if (!$serviceProposal->canBeApprovedBy(auth()->user())) {
            abort(403, 'Bạn không có quyền duyệt đề xuất này.');
        }

        $oldStatus = $serviceProposal->status;

        DB::transaction(function () use ($serviceProposal) {
            $serviceProposal->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        // Send notification
        $this->sendStatusChangeNotification($serviceProposal, $oldStatus);

        return redirect()->back()->with('success', 'Đề xuất dịch vụ đã được duyệt thành công.');
    }

    /**
     * Reject a service proposal (Admin only)
     */
    public function reject(ServiceProposal $serviceProposal)
    {
        if (!$serviceProposal->canBeApprovedBy(auth()->user())) {
            abort(403, 'Bạn không có quyền từ chối đề xuất này.');
        }

        $oldStatus = $serviceProposal->status;

        $serviceProposal->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Send notification
        $this->sendStatusChangeNotification($serviceProposal, $oldStatus);

        return redirect()->back()->with('success', 'Đề xuất dịch vụ đã bị từ chối.');
    }

    /**
     * Confirm a service proposal (Admin or Creator)
     */
    public function confirm(ServiceProposal $serviceProposal)
    {
        if (!$serviceProposal->canBeConfirmedBy(auth()->user())) {
            abort(403, 'Bạn không có quyền xác nhận đề xuất này.');
        }

        $serviceProposal->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Đơn hàng đã được xác nhận thành công.');
    }

    /**
     * Partner confirm proposal
     */
    public function partnerConfirm(ServiceProposal $serviceProposal)
    {
        if (!$serviceProposal->canBePartnerConfirmedBy(auth()->user())) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        $oldStatus = $serviceProposal->status;

        $serviceProposal->update([
            'status' => 'partner_confirmed',
            'partner_confirmed_by' => auth()->id(),
            'partner_confirmed_at' => now(),
        ]);

        // Send notification
        $this->sendStatusChangeNotification($serviceProposal, $oldStatus);

        return redirect()->back()->with('success', 'Đối tác đã xác nhận đơn hàng thành công.');
    }

    /**
     * Partner complete proposal
     */
    public function partnerComplete(Request $request, ServiceProposal $serviceProposal)
    {
        if (!$serviceProposal->canBePartnerCompletedBy(auth()->user())) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        // Validate result link
        $request->validate([
            'result_link' => 'required|url|max:500',
        ], [
            'result_link.required' => 'Vui lòng nhập link kết quả.',
            'result_link.url' => 'Link kết quả không hợp lệ.',
            'result_link.max' => 'Link kết quả không được quá 500 ký tự.',
        ]);

        $oldStatus = $serviceProposal->status;

        $serviceProposal->update([
            'status' => 'partner_completed',
            'partner_completed_by' => auth()->id(),
            'partner_completed_at' => now(),
            'result_link' => $request->result_link,
        ]);

        // Send notification
        $this->sendStatusChangeNotification($serviceProposal, $oldStatus);

        return redirect()->back()->with('success', 'Đối tác đã xác nhận hoàn thành thành công.');
    }

    /**
     * Seoer confirm proposal
     */
    public function seoerConfirm(ServiceProposal $serviceProposal)
    {
        if (!$serviceProposal->canBeSeoerConfirmedBy(auth()->user())) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        $oldStatus = $serviceProposal->status;

        $serviceProposal->update([
            'status' => 'seoer_confirmed',
            'seoer_confirmed_by' => auth()->id(),
            'seoer_confirmed_at' => now(),
        ]);

        // Send notification
        $this->sendStatusChangeNotification($serviceProposal, $oldStatus);

        return redirect()->back()->with('success', 'Seoer đã xác nhận thành công.');
    }

    /**
     * Admin complete proposal
     */
    public function adminComplete(ServiceProposal $serviceProposal)
    {
        if (!$serviceProposal->canBeAdminCompletedBy(auth()->user())) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        $oldStatus = $serviceProposal->status;

        $serviceProposal->update([
            'status' => 'admin_completed',
            'admin_completed_by' => auth()->id(),
            'admin_completed_at' => now(),
        ]);

        // Send notification
        $this->sendStatusChangeNotification($serviceProposal, $oldStatus);

        return redirect()->back()->with('success', 'Quản lý đã xác nhận hoàn thành thành công.');
    }

    /**
     * Payment confirm proposal
     */
    public function paymentConfirm(ServiceProposal $serviceProposal)
    {
        if (!$serviceProposal->canBePaymentConfirmedBy(auth()->user())) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        $oldStatus = $serviceProposal->status;

        DB::transaction(function () use ($serviceProposal) {
            $serviceProposal->update([
                'status' => 'completed',
                'payment_confirmed_by' => auth()->id(),
                'payment_confirmed_at' => now(),
            ]);

            // Recalculate budget spent amount if linked to a budget
            if ($serviceProposal->budget) {
                $serviceProposal->budget->recalculateSpentAmount();
            }
        });

        // Send notification
        $this->sendStatusChangeNotification($serviceProposal, $oldStatus);

        return redirect()->back()->with('success', 'Đã xác nhận thanh toán thành công và ngân sách đã được cập nhật.');
    }

    /**
     * Complete a service proposal (Admin only)
     */
    public function complete(ServiceProposal $serviceProposal)
    {
        if (!$serviceProposal->canBeCompletedBy(auth()->user())) {
            abort(403, 'Bạn không có quyền hoàn thành đề xuất này.');
        }

        DB::transaction(function () use ($serviceProposal) {
            $serviceProposal->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Recalculate budget spent amount if linked to a budget
            if ($serviceProposal->budget) {
                $serviceProposal->budget->recalculateSpentAmount();
            }
        });

        return redirect()->back()->with('success', 'Thanh toán đã được hoàn thành và ngân sách đã được cập nhật.');
    }
}
