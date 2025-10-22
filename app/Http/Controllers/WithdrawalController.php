<?php

namespace App\Http\Controllers;

use App\Models\Withdrawal;
use App\Models\ServiceProposal;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class WithdrawalController extends Controller
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
        $user = auth()->user();
        
        // Kiểm tra Admin không được phép truy cập
        if ($user->role && $user->role->name === 'admin') {
            abort(403, 'Admin không được phép truy cập phần quản lý rút tiền.');
        }
        
        $query = Withdrawal::with(['partner', 'assistantProcessor', 'partnerConfirmer', 'serviceProposals']);

        // Filter by user role
        if ($user->role && $user->role->name === 'partner') {
            // Partner chỉ thấy withdrawal của mình
            $query->where('partner_id', $user->id);
        }
        // Assistant và IT thấy tất cả

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by partner (for Admin/IT/Assistant)
        if ($request->filled('partner_id') && (!$user->role || $user->role->name !== 'partner')) {
            $query->where('partner_id', $request->partner_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                  ->orWhere('assistant_note', 'like', "%{$search}%")
                  ->orWhereHas('partner', function ($partnerQuery) use ($search) {
                      $partnerQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get partners for filter dropdown
        $partnerRole = \App\Models\Role::where('name', 'partner')->first();
        $partners = $partnerRole ? \App\Models\User::where('role_id', $partnerRole->id)->get() : collect();

        return view('withdrawals.index', compact('withdrawals', 'partners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        
        // Kiểm tra Admin không được phép truy cập
        if ($user->role && $user->role->name === 'admin') {
            abort(403, 'Admin không được phép truy cập phần quản lý rút tiền.');
        }
        
        if (!$user->role || $user->role->name !== 'partner') {
            abort(403, 'Chỉ Partner mới có thể tạo yêu cầu rút tiền.');
        }

        // Lấy các service proposals đã hoàn thành thanh toán cho partner này
        $partnerServiceIds = $user->services()->pluck('id');
        
        $completedProposals = ServiceProposal::whereIn('status', ['payment_confirmed', 'completed'])
            ->whereIn('service_id', $partnerServiceIds) // Chỉ lấy proposals sử dụng dịch vụ của partner
            ->with(['budget', 'withdrawals'])
            ->orderBy('created_at', 'desc') // Sắp xếp theo thứ tự mới nhất
            ->get()
            ->map(function ($proposal) {
                // Force refresh to get latest data
                $proposal->refresh();
                
                // Add withdrawal tracking info
                $proposal->total_withdrawn = $proposal->getTotalWithdrawnAmount();
                $proposal->remaining_amount = $proposal->getRemainingWithdrawableAmount();
                $proposal->is_fully_withdrawn = $proposal->isFullyWithdrawn();
                
                // Debug log
                \Log::channel('single')->info('Proposal withdrawal info', [
                    'proposal_id' => $proposal->id,
                    'service_name' => $proposal->service_name,
                    'total_amount' => $proposal->amount,
                    'total_withdrawn' => $proposal->total_withdrawn,
                    'remaining_amount' => $proposal->remaining_amount,
                    'is_fully_withdrawn' => $proposal->is_fully_withdrawn
                ]);
                
                return $proposal;
            });

        if ($completedProposals->isEmpty()) {
            return redirect()->route('withdrawals.index')
                ->with('warning', 'Bạn chưa có đề xuất nào đã hoàn thành thanh toán để rút tiền.');
        }

        return view('withdrawals.create', ['serviceProposals' => $completedProposals]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Force log to file
        \Log::channel('single')->info('=== WITHDRAWAL STORE START ===');
        \Log::channel('single')->info('User: ' . (auth()->check() ? auth()->user()->name : 'Not logged in'));
        \Log::channel('single')->info('Request data: ' . json_encode($request->all()));
        \Log::channel('single')->info('Request method: ' . $request->method());
        \Log::channel('single')->info('Request URL: ' . $request->fullUrl());
        
        $user = auth()->user();
        
        // Kiểm tra Admin không được phép truy cập
        if ($user->role && $user->role->name === 'admin') {
            abort(403, 'Admin không được phép truy cập phần quản lý rút tiền.');
        }
        
        if (!$user->role || $user->role->name !== 'partner') {
            abort(403, 'Chỉ Partner mới có thể tạo yêu cầu rút tiền.');
        }

        // DEBUG: Log request data
        \Log::channel('single')->info('Request validation data:', [
            'service_proposals' => $request->service_proposals,
            'amounts' => $request->amounts,
            'note' => $request->note
        ]);

        $request->validate([
            'note' => 'nullable|string|max:1000',
            'service_proposals' => 'required|array|min:1',
            'service_proposals.*' => 'required|exists:service_proposals,id',
            'amounts' => 'required|array|min:1',
            'amounts.*' => 'required|numeric|min:1',
        ], [
            'service_proposals.required' => 'Vui lòng chọn ít nhất một đề xuất.',
            'amounts.required' => 'Vui lòng nhập số tiền cho các đề xuất.',
            'amounts.*.required' => 'Vui lòng nhập số tiền cho mỗi đề xuất.',
            'amounts.*.numeric' => 'Số tiền phải là số.',
            'amounts.*.min' => 'Số tiền phải lớn hơn 0.',
        ]);

        // Additional validation: ensure partner can only withdraw from their own service proposals
        $partnerServiceIds = $user->services()->pluck('id');
        $selectedProposalIds = collect($request->service_proposals)->flatten()->values();
        
        $invalidProposals = ServiceProposal::whereIn('id', $selectedProposalIds)
            ->whereNotIn('service_id', $partnerServiceIds)
            ->exists();
            
        if ($invalidProposals) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['service_proposals' => 'Bạn chỉ có thể rút tiền từ các đề xuất sử dụng dịch vụ của mình.']);
        }

        try {
            DB::beginTransaction();

            // Tính tổng số tiền từ amounts array - chỉ lấy proposals được chọn
            $totalAmount = 0;
            $selectedAmounts = [];
            
            \Log::channel('single')->info('Processing amounts', [
                'service_proposals' => $request->service_proposals,
                'amounts' => $request->amounts,
                'amounts_type' => gettype($request->amounts)
            ]);
            
            if ($request->amounts && is_array($request->amounts)) {
                \Log::channel('single')->info('Processing amounts - detailed debug', [
                    'service_proposals_raw' => $request->service_proposals,
                    'amounts_raw' => $request->amounts,
                    'amounts_keys' => array_keys($request->amounts)
                ]);
                
                // Extract selected proposal IDs from service_proposals array
                $selectedProposalIds = [];
                if ($request->service_proposals && is_array($request->service_proposals)) {
                    foreach ($request->service_proposals as $key => $value) {
                        if (is_array($value) && isset($value['id'])) {
                            // Handle service_proposals[X][id] format
                            $selectedProposalIds[] = (string) $value['id'];
                        } elseif (is_string($value) || is_numeric($value)) {
                            // Handle service_proposals[] format
                            $selectedProposalIds[] = (string) $value;
                        }
                    }
                }
                
                \Log::channel('single')->info('Extracted selected proposal IDs', [
                    'selected_ids' => $selectedProposalIds
                ]);
                
                // Xử lý trực tiếp từ amounts array
                foreach ($request->amounts as $proposalId => $amount) {
                    $proposalId = (string) $proposalId;
                    $amount = (float) $amount;
                    
                    $isSelected = in_array($proposalId, $selectedProposalIds);
                    
                    \Log::channel('single')->info('Processing amount', [
                        'proposal_id' => $proposalId,
                        'amount' => $amount,
                        'is_selected' => $isSelected
                    ]);
                    
                    // Chỉ tính những proposal được chọn và có amount > 0
                    if ($isSelected && $amount > 0) {
                        $totalAmount += $amount;
                        $selectedAmounts[$proposalId] = $amount;
                        \Log::channel('single')->info('Added to selected amounts', [
                            'proposal_id' => $proposalId,
                            'amount' => $amount,
                            'total_so_far' => $totalAmount
                        ]);
                    }
                }
            }

            // Kiểm tra có proposals và amount hợp lệ
            if (empty($selectedAmounts) || $totalAmount <= 0) {
                DB::rollBack();
                return back()->withInput()
                    ->with('error', 'Vui lòng chọn ít nhất một đề xuất và nhập số tiền hợp lệ.');
            }

            // Kiểm tra số tiền rút không vượt quá số tiền còn lại của mỗi proposal
            foreach ($selectedAmounts as $proposalId => $requestedAmount) {
                $proposal = ServiceProposal::find($proposalId);
                if (!$proposal) {
                    DB::rollBack();
                    return back()->withInput()
                        ->with('error', 'Đề xuất không tồn tại.');
                }

                $remainingAmount = $proposal->getRemainingWithdrawableAmount();
                if ($requestedAmount > $remainingAmount) {
                    DB::rollBack();
                    return back()->withInput()
                        ->with('error', "Đề xuất '{$proposal->title}' chỉ còn lại " . number_format($remainingAmount) . " VND có thể rút.");
                }
            }

            // Tạo withdrawal
            $withdrawal = Withdrawal::create([
                'partner_id' => $user->id,
                'amount' => $totalAmount,
                'note' => $request->note,
                'status' => 'pending',
            ]);

            // Attach service proposals với số tiền tương ứng - chỉ attach proposals có amount
            foreach ($selectedAmounts as $proposalId => $amount) {
                \Log::channel('single')->info('Attaching proposal', [
                    'proposal_id' => $proposalId,
                    'amount' => $amount
                ]);
                
                $withdrawal->serviceProposals()->attach($proposalId, [
                    'amount' => $amount
                ]);
            }

            DB::commit();

            Log::info('Withdrawal created', [
                'withdrawal_id' => $withdrawal->id,
                'partner_id' => $user->id,
                'amount' => $totalAmount,
                'proposals_count' => count($request->service_proposals)
            ]);

            // Send notification
            $this->notificationService->sendWithdrawalNotification('created', $withdrawal, $user);

            return redirect()->route('withdrawals.index')
                ->with('success', 'Yêu cầu rút tiền đã được tạo thành công. Chờ Trợ lý xử lý.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating withdrawal', [
                'error' => $e->getMessage(),
                'partner_id' => $user->id
            ]);

            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo yêu cầu rút tiền. Vui lòng thử lại.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Withdrawal $withdrawal)
    {
        $user = auth()->user();
        
        // Kiểm tra Admin không được phép truy cập
        if ($user->role && $user->role->name === 'admin') {
            abort(403, 'Admin không được phép truy cập phần quản lý rút tiền.');
        }
        
        // Check permission - Partner chỉ xem withdrawal của mình, các role khác cần permission
        if ($user->role && $user->role->name === 'partner') {
            if ($withdrawal->partner_id != $user->id) {
                abort(403, 'Bạn chỉ có thể xem yêu cầu rút tiền của mình.');
            }
        } else {
            // Các role khác cần có permission withdrawals.read
            if (!$user->hasPermission('withdrawals.read')) {
                abort(403, 'Không có quyền truy cập.');
            }
        }

        $withdrawal->load(['partner', 'assistantProcessor', 'partnerConfirmer', 'serviceProposals.budget']);

        return view('withdrawals.show', compact('withdrawal'));
    }

    /**
     * Assistant process withdrawal
     */
    public function assistantProcess(Request $request, Withdrawal $withdrawal)
    {
        $user = auth()->user();
        
        if (!$user->role || $user->role->name !== 'assistant') {
            abort(403, 'Chỉ Trợ lý mới có thể xử lý thanh toán.');
        }

        if (!$withdrawal->canBeProcessedByAssistant($user)) {
            abort(403, 'Yêu cầu rút tiền này không thể xử lý.');
        }

        $request->validate([
            'assistant_note' => 'nullable|string|max:1000',
            'payment_proof_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
        ], [
            'payment_proof_image.required' => 'Vui lòng upload ảnh bill chuyển khoản.',
            'payment_proof_image.image' => 'File phải là ảnh.',
            'payment_proof_image.max' => 'Ảnh không được quá 5MB.',
        ]);

        try {
            // Upload image
            $imagePath = null;
            if ($request->hasFile('payment_proof_image')) {
                $imagePath = $request->file('payment_proof_image')->store('withdrawal_proofs', 'public');
            }

            $oldStatus = $withdrawal->status;

            $withdrawal->update([
                'status' => 'assistant_completed',
                'assistant_processed_by' => $user->id,
                'assistant_processed_at' => now(),
                'assistant_note' => $request->assistant_note,
                'payment_proof_image' => $imagePath,
            ]);

            // Send notification
            $this->notificationService->sendWithdrawalStatusChangeNotification($withdrawal, $oldStatus, $user);

            Log::info('Withdrawal processed by assistant', [
                'withdrawal_id' => $withdrawal->id,
                'assistant_id' => $user->id
            ]);

            return redirect()->back()
                ->with('success', 'Đã xác nhận thanh toán thành công. Đối tác sẽ nhận được thông báo.');

        } catch (\Exception $e) {
            Log::error('Error processing withdrawal', [
                'withdrawal_id' => $withdrawal->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Có lỗi xảy ra khi xử lý thanh toán. Vui lòng thử lại.');
        }
    }

    /**
     * Partner confirm withdrawal
     */
    public function partnerConfirm(Request $request, Withdrawal $withdrawal)
    {
        $user = auth()->user();
        
        if (!$withdrawal->canBeConfirmedByPartner($user)) {
            abort(403, 'Bạn không thể xác nhận yêu cầu rút tiền này.');
        }

        $request->validate([
            'partner_confirmation_note' => 'nullable|string|max:1000',
        ]);

        try {
            $oldStatus = $withdrawal->status;

            $withdrawal->update([
                'status' => 'partner_confirmed',
                'partner_confirmed_by' => $user->id,
                'partner_confirmed_at' => now(),
                'partner_confirmation_note' => $request->partner_confirmation_note,
            ]);

            // Send notification
            $this->notificationService->sendWithdrawalStatusChangeNotification($withdrawal, $oldStatus, $user);

            Log::info('Withdrawal confirmed by partner', [
                'withdrawal_id' => $withdrawal->id,
                'partner_id' => $user->id
            ]);

            return redirect()->back()
                ->with('success', 'Đã xác nhận nhận tiền thành công.');

        } catch (\Exception $e) {
            Log::error('Error confirming withdrawal', [
                'withdrawal_id' => $withdrawal->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Có lỗi xảy ra khi xác nhận. Vui lòng thử lại.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Withdrawal $withdrawal)
    {
        $user = auth()->user();
        
        // Kiểm tra Admin không được phép truy cập
        if ($user->role && $user->role->name === 'admin') {
            abort(403, 'Admin không được phép truy cập phần quản lý rút tiền.');
        }
        
        // Chỉ Partner tạo và status pending mới được xóa
        if (!$user->role || $user->role->name !== 'partner' || 
            $withdrawal->partner_id !== $user->id || 
            $withdrawal->status !== 'pending') {
            abort(403, 'Bạn không thể xóa yêu cầu rút tiền này.');
        }

        try {
            // Send notification before deleting
            $this->notificationService->sendWithdrawalNotification('deleted', $withdrawal, $user);
            
            $withdrawal->delete();

            Log::info('Withdrawal deleted', [
                'withdrawal_id' => $withdrawal->id,
                'partner_id' => $user->id
            ]);

            return redirect()->route('withdrawals.index')
                ->with('success', 'Đã xóa yêu cầu rút tiền thành công.');

        } catch (\Exception $e) {
            Log::error('Error deleting withdrawal', [
                'withdrawal_id' => $withdrawal->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Có lỗi xảy ra khi xóa. Vui lòng thử lại.');
        }
    }
}