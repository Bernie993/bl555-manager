<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Website;
use App\Models\Budget;
use App\Models\User;
use App\Models\Service;
use App\Models\ServiceProposal;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        
        if ($user->hasRole('partner')) {
            return $this->partnerDashboard($user);
        }
        
        if ($user->hasRole('seoer')) {
            return $this->seoerDashboard($user);
        }
        
        return $this->defaultDashboard($user);
    }
    
    private function partnerDashboard($user)
    {
        // Partner statistics
        $partnerStats = [
            'total_services' => $user->services()->count(),
            'total_proposals' => $user->getProposalsCount(),
            'total_withdrawn' => $user->getTotalWithdrawnAmount(),
            'total_pending' => $user->getTotalPendingAmount(),
        ];

        // Recent services
        $recentServices = $user->services()->latest()->take(5)->get();
        
        return view('dashboard.index', compact('partnerStats', 'recentServices'));
    }
    
    private function defaultDashboard($user)
    {
        // Default statistics
        $stats = [
            'total_websites' => Website::count(),
            'active_websites' => Website::where('status', 'active')->count(),
            'total_budgets' => Budget::count(),
            'total_users' => User::where('is_active', true)->count(),
        ];

        // Recent websites
        $recentWebsites = Website::latest()->take(5)->get();
        
        // Budget summary
        $budgetSummary = Budget::selectRaw('
            SUM(total_budget) as total_budget,
            SUM(spent_amount) as total_spent,
            SUM(remaining_amount) as total_remaining
        ')->first();

        return view('dashboard.index', compact('stats', 'recentWebsites', 'budgetSummary'));
    }
    
    private function seoerDashboard($user)
    {
        // Seoer statistics
        $seoerStats = [
            // 1. Số đơn đã xác nhận (các trạng thái từ partner_confirmed trở lên)
            'confirmed_orders' => $user->serviceProposals()
                ->whereIn('status', ['partner_confirmed', 'partner_completed', 'seoer_confirmed', 'admin_completed', 'completed'])
                ->count(),
            
            // 2. Số đơn đã hoàn thành 
            'completed_orders' => $user->serviceProposals()
                ->where('status', 'completed')
                ->count(),
            
            // 3. Số đơn đang chờ thanh toán (admin đã xác nhận hoàn thành nhưng chưa thanh toán)
            'pending_payment_orders' => $user->serviceProposals()
                ->where('status', 'admin_completed')
                ->count(),
        ];

        // 4. & 5. Ngân sách đã tiêu và còn lại
        $budgetSummary = Budget::where('seoer', $user->name)
            ->selectRaw('
                SUM(total_budget) as total_budget,
                SUM(spent_amount) as total_spent,
                SUM(remaining_amount) as total_remaining
            ')->first();

        $seoerStats['budget_spent'] = $budgetSummary->total_spent ?? 0;
        $seoerStats['budget_remaining'] = $budgetSummary->total_remaining ?? 0;

        // 6. Danh sách website được phân công cho seoer này
        $assignedWebsites = Website::where(function($query) use ($user) {
            $query->where('seoer_id', $user->id)
                  ->orWhere('seoer', $user->name);
        })->latest()->take(10)->get();

        return view('dashboard.index', compact('seoerStats', 'assignedWebsites'));
    }
    
    /**
     * Show confirmed orders for seoer
     */
    public function seoerConfirmedOrders()
    {
        $user = auth()->user();
        
        if (!$user->hasRole('seoer')) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
        
        $confirmedOrders = $user->serviceProposals()
            ->whereIn('status', ['partner_confirmed', 'partner_completed', 'seoer_confirmed', 'admin_completed', 'completed'])
            ->with(['budget', 'service'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('dashboard.seoer.confirmed-orders', compact('confirmedOrders'));
    }
    
    /**
     * Show completed orders for seoer
     */
    public function seoerCompletedOrders()
    {
        $user = auth()->user();
        
        if (!$user->hasRole('seoer')) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
        
        $completedOrders = $user->serviceProposals()
            ->where('status', 'completed')
            ->with(['budget', 'service'])
            ->orderBy('completed_at', 'desc')
            ->paginate(20);
            
        return view('dashboard.seoer.completed-orders', compact('completedOrders'));
    }
    
    /**
     * Show pending payment orders for seoer
     */
    public function seoerPendingPaymentOrders()
    {
        $user = auth()->user();
        
        if (!$user->hasRole('seoer')) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
        
        $pendingPaymentOrders = $user->serviceProposals()
            ->where('status', 'admin_completed')
            ->with(['budget', 'service'])
            ->orderBy('admin_completed_at', 'desc')
            ->paginate(20);
            
        return view('dashboard.seoer.pending-payment-orders', compact('pendingPaymentOrders'));
    }
    
    /**
     * Show budget spent details for seoer
     */
    public function seoerBudgetSpent()
    {
        $user = auth()->user();
        
        if (!$user->hasRole('seoer')) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
        
        // Get all completed orders (where money has been spent)
        $spentOrders = $user->serviceProposals()
            ->whereIn('status', ['completed', 'payment_confirmed'])
            ->with(['budget', 'service'])
            ->orderBy('payment_confirmed_at', 'desc')
            ->paginate(20);
            
        // Calculate total spent
        $totalSpent = $spentOrders->sum('amount');
        
        return view('dashboard.seoer.budget-spent', compact('spentOrders', 'totalSpent'));
    }
    
    /**
     * Show budget remaining details for seoer
     */
    public function seoerBudgetRemaining()
    {
        $user = auth()->user();
        
        if (!$user->hasRole('seoer')) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }
        
        // Get budget summary
        $budgetSummary = Budget::where('seoer', $user->name)
            ->selectRaw('
                SUM(total_budget) as total_budget,
                SUM(spent_amount) as total_spent,
                SUM(remaining_amount) as total_remaining
            ')->first();
            
        // Get all budgets for this seoer
        $budgets = $user->budgets()->orderBy('created_at', 'desc')->get();
        
        return view('dashboard.seoer.budget-remaining', compact('budgetSummary', 'budgets'));
    }
}
