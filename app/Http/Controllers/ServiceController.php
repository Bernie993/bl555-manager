<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Service::with('partner');

        // Filter by role
        if ($user->role && $user->role->name === 'partner') {
            // Partners can only see their own services
            $query->where('partner_id', $user->id);
        } elseif ($user->role && $user->role->name === 'seoer') {
            // Seoers can see all active and approved services
            $query->active()->approved();
        } elseif ($user->role && $user->role->name === 'assistant') {
            // Assistants (TL) can see all services to manage approval
            // No additional filter - they see all services
        } else {
            // Admin, IT can see all services
            // No additional filter
        }

        // Search filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('website', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->filled('partner_name')) {
            $query->where('partner_id', $request->partner_name);
        }

        if ($request->filled('price_from')) {
            $query->where('price', '>=', $request->price_from);
        }

        if ($request->filled('price_to')) {
            $query->where('price', '<=', $request->price_to);
        }

        // DR filters
        if ($request->filled('dr_from')) {
            $query->where('dr', '>=', $request->dr_from);
        }

        if ($request->filled('dr_to')) {
            $query->where('dr', '<=', $request->dr_to);
        }

        // Ref domain filter
        if ($request->filled('ref_domain_search')) {
            $refDomainSearch = $request->ref_domain_search;
            $query->where('ref_domain', 'like', "%{$refDomainSearch}%");
        }

        // Traffic filters
        if ($request->filled('traffic_from')) {
            $query->where('traffic', '>=', $request->traffic_from);
        }

        if ($request->filled('traffic_to')) {
            $query->where('traffic', '<=', $request->traffic_to);
        }

        // Handle sorting
        $sortBy = $request->get('sort_by', 'created_at_desc');
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'dr_asc':
                $query->orderBy('dr', 'asc');
                break;
            case 'dr_desc':
                $query->orderBy('dr', 'desc');
                break;
            case 'created_at_desc':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $services = $query->paginate(15);

        // Get partners for filter dropdown
        $partners = [];
        if ($user->role && in_array($user->role->name, ['seoer', 'admin', 'it', 'assistant'])) {
            $partners = User::whereHas('role', function($q) {
                $q->where('name', 'partner');
            })->where('is_active', true)->orderBy('name')->get();
        }

        return view('services.index', compact('services', 'partners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Only partners can create services
        if (!$user->role || $user->role->name !== 'partner') {
            abort(403, 'Chỉ đối tác mới có thể tạo dịch vụ.');
        }

        return view('services.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Only partners can create services
        if (!$user->role || $user->role->name !== 'partner') {
            abort(403, 'Chỉ đối tác mới có thể tạo dịch vụ.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(array_keys(Service::TYPES))],
            'website' => 'required|url|max:255',
            'dr' => 'nullable|integer|min:0|max:100',
            'da' => 'nullable|integer|min:0|max:100',
            'pa' => 'nullable|integer|min:0|max:100',
            'tf' => 'nullable|integer|min:0|max:100',
            'ip' => 'nullable|ip',
            'keywords' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'quote_file' => 'nullable|url|max:255',
            'demo_file' => 'nullable|url|max:255',
            'ref_domain' => 'nullable|url|max:255',
            'traffic' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Process keywords
        if ($validated['keywords']) {
            $keywords = array_map('trim', explode(',', $validated['keywords']));
            $validated['keywords'] = array_filter($keywords);
        }

        $validated['partner_id'] = $user->id;
        $validated['is_active'] = $request->has('is_active');
        $validated['approval_status'] = 'pending'; // Mặc định là chờ duyệt

        $service = Service::create($validated);

        // Send notification
        $this->notificationService->sendServiceNotification('created', $service, $user);

        return redirect()->route('services.index')
            ->with('success', 'Dịch vụ đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        $service->load(['partner', 'approvedBy', 'serviceProposals.user']);
        
        return view('services.show', compact('service'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        $user = Auth::user();
        
        if (!$service->canBeManageBy($user)) {
            abort(403, 'Bạn không có quyền chỉnh sửa dịch vụ này.');
        }

        return view('services.edit', compact('service'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $user = Auth::user();
        
        if (!$service->canBeManageBy($user)) {
            abort(403, 'Bạn không có quyền chỉnh sửa dịch vụ này.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(array_keys(Service::TYPES))],
            'website' => 'required|url|max:255',
            'dr' => 'nullable|integer|min:0|max:100',
            'da' => 'nullable|integer|min:0|max:100',
            'pa' => 'nullable|integer|min:0|max:100',
            'tf' => 'nullable|integer|min:0|max:100',
            'ip' => 'nullable|ip',
            'keywords' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'quote_file' => 'nullable|url|max:255',
            'demo_file' => 'nullable|url|max:255',
            'ref_domain' => 'nullable|url|max:255',
            'traffic' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Process keywords
        if ($validated['keywords']) {
            $keywords = array_map('trim', explode(',', $validated['keywords']));
            $validated['keywords'] = array_filter($keywords);
        }

        $validated['is_active'] = $request->has('is_active');
        
        // Nếu partner sửa dịch vụ đã duyệt, chuyển về trạng thái chờ duyệt
        if ($service->approval_status === 'approved') {
            $validated['approval_status'] = 'pending';
            $validated['approved_by'] = null;
            $validated['approved_at'] = null;
            $validated['rejection_reason'] = null;
        }

        $service->update($validated);

        // Send notification
        $this->notificationService->sendServiceNotification('updated', $service, $user);

        return redirect()->route('services.index')
            ->with('success', 'Dịch vụ đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $user = Auth::user();
        
        if (!$service->canBeManageBy($user)) {
            abort(403, 'Bạn không có quyền xóa dịch vụ này.');
        }

        // Check if service has any proposals
        if ($service->serviceProposals()->count() > 0) {
            return redirect()->route('services.index')
                ->with('error', 'Không thể xóa dịch vụ đã có đề xuất.');
        }

        // Send notification before deleting
        $this->notificationService->sendServiceNotification('deleted', $service, $user);

        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Dịch vụ đã được xóa thành công.');
    }

    /**
     * Create service proposal from service
     */
    public function createProposal(Service $service)
    {
        $user = Auth::user();
        
        // Only seoers can create proposals
        if (!$user->role || $user->role->name !== 'seoer') {
            abort(403, 'Chỉ Seoer mới có thể tạo đề xuất.');
        }

        // Redirect to service-proposals create with service data pre-filled
        return redirect()->route('service-proposals.create', [
            'service_id' => $service->id,
            'service_name' => $service->name,
            'partner_website' => $service->website,
            'unit_price' => $service->price,
            'keywords' => $service->keywords_string,
            'supplier_name' => $service->partner->name ?? '',
            'supplier_telegram' => $service->partner->telegram ?? '',
        ]);
    }

    /**
     * Create multiple service proposals from selected services
     */
    public function bulkCreateProposals(Request $request)
    {
        $user = Auth::user();
        
        // Only seoers can create proposals
        if (!$user->role || $user->role->name !== 'seoer') {
            abort(403, 'Chỉ Seoer mới có thể tạo đề xuất.');
        }

        $request->validate([
            'services' => 'required|json'
        ]);

        $services = json_decode($request->services, true);
        
        if (empty($services)) {
            return redirect()->route('services.index')
                ->with('error', 'Vui lòng chọn ít nhất một dịch vụ.');
        }

        // Group services by partner to create separate proposals
        $servicesByPartner = [];
        foreach ($services as $serviceData) {
            $partnerId = $serviceData['partner_id'];
            if (!isset($servicesByPartner[$partnerId])) {
                $servicesByPartner[$partnerId] = [];
            }
            $servicesByPartner[$partnerId][] = $serviceData;
        }

        \Log::info('Bulk create proposals - services grouped by partner:', [
            'servicesByPartner' => $servicesByPartner,
            'encoded_data' => base64_encode(json_encode($servicesByPartner))
        ]);

        // Redirect to service-proposals create with bulk data
        return redirect()->route('service-proposals.create', [
            'bulk_services' => base64_encode(json_encode($servicesByPartner))
        ]);
    }

    /**
     * Approve service (Assistant only)
     */
    public function approve(Service $service)
    {
        $user = Auth::user();
        
        if (!$service->canBeApprovedBy($user)) {
            abort(403, 'Bạn không có quyền duyệt dịch vụ này.');
        }

        $service->update([
            'approval_status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        // Send notification
        $this->notificationService->sendServiceNotification('approved', $service, $user);

        return redirect()->route('services.index')
            ->with('success', 'Dịch vụ đã được duyệt thành công.');
    }

    /**
     * Reject service (Assistant only)
     */
    public function reject(Request $request, Service $service)
    {
        $user = Auth::user();
        
        if (!$service->canBeApprovedBy($user)) {
            abort(403, 'Bạn không có quyền từ chối dịch vụ này.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        $service->update([
            'approval_status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Send notification
        $this->notificationService->sendServiceNotification('rejected', $service, $user);

        return redirect()->route('services.index')
            ->with('success', 'Dịch vụ đã được từ chối.');
    }
}