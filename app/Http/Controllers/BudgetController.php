<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Website;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BudgetController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Budget::with(['serviceProposals.service']);
        
        $user = auth()->user();
        $userRole = $user->role->name ?? '';
        
        // Seoer can only see their own budgets
        if ($userRole === 'seoer') {
            $query->where('seoer', $user->name);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('seoer', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by seoer (only for non-seoer roles)
        if ($request->filled('seoer') && $userRole !== 'seoer') {
            $query->where('seoer', $request->seoer);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            
            $query->where(function($q) use ($startDate, $endDate) {
                // Filter by period dates or created date
                $q->where(function($subQ) use ($startDate, $endDate) {
                    // Budget period overlaps with selected range
                    $subQ->where(function($periodQ) use ($startDate, $endDate) {
                        $periodQ->where('period_start', '<=', $endDate)
                               ->where('period_end', '>=', $startDate);
                    })
                    // Or created date is within range
                    ->orWhereBetween('created_at', [$startDate, $endDate]);
                });
            });
        }

        // Filter by domain (through service proposals)
        if ($request->filled('domain')) {
            $query->whereHas('serviceProposals.service', function($q) use ($request) {
                $q->where('website', 'like', "%{$request->domain}%");
            });
        }

        // Filter by service type (through service proposals)
        if ($request->filled('service_type')) {
            $query->whereHas('serviceProposals.service', function($q) use ($request) {
                $q->where('type', $request->service_type);
            });
        }

        $budgets = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get filter options for dropdowns
        $domains = collect();
        $seoers = collect();
        
        if ($userRole !== 'seoer') {
            // Get all unique domains from websites
            $domains = Website::distinct()
                ->whereNotNull('name')
                ->pluck('name')
                ->map(function($domain) {
                    // Clean domain name - remove http/https and www
                    $domain = strtolower(trim($domain));
                    $domain = preg_replace('/^https?:\/\//', '', $domain);
                    $domain = preg_replace('/^www\./', '', $domain);
                    return $domain;
                })
                ->filter() // Remove empty values
                ->unique()
                ->sort()
                ->values();

            // Get all unique seoers from budgets
            $seoers = Budget::distinct()
                ->whereNotNull('seoer')
                ->pluck('seoer')
                ->sort()
                ->values();
        }

        // Calculate summary statistics
        $totalBudgetAmount = $budgets->sum('total_budget');
        $totalSpentAmount = $budgets->sum('spent_amount');
        $totalRemainingAmount = $budgets->sum('remaining_amount');

        return view('budgets.index', compact(
            'budgets', 
            'domains', 
            'seoers',
            'totalBudgetAmount',
            'totalSpentAmount', 
            'totalRemainingAmount'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all users with seoer role
        $seoers = User::whereHas('role', function($query) {
            $query->where('name', 'seoer');
        })->where('is_active', true)->orderBy('name')->get();

        return view('budgets.create', compact('seoers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'total_budget' => 'required|numeric|min:0',
            'seoer' => 'required|string|max:255',
            'description' => 'nullable|string',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
        ]);

        $data = $request->all();
        // spent_amount will be automatically calculated by the model
        $data['spent_amount'] = 0; // Initial value, will be recalculated

        Budget::create($data);

        return redirect()->route('budgets.index')
                        ->with('success', 'Ngân sách đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Budget $budget)
    {
        // Load service proposals with their related data
        $budget->load([
            'serviceProposals' => function($query) {
                $query->with(['service', 'creator', 'approver'])
                      ->orderBy('created_at', 'desc');
            }
        ]);
        
        return view('budgets.show', compact('budget'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Budget $budget)
    {
        // Get all users with seoer role
        $seoers = User::whereHas('role', function($query) {
            $query->where('name', 'seoer');
        })->where('is_active', true)->orderBy('name')->get();

        return view('budgets.edit', compact('budget', 'seoers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Budget $budget)
    {
        $request->validate([
            'total_budget' => 'required|numeric|min:0',
            'seoer' => 'required|string|max:255',
            'description' => 'nullable|string',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after_or_equal:period_start',
        ]);

        $data = $request->all();
        // Don't update spent_amount - it's automatically calculated
        unset($data['spent_amount']);

        $budget->update($data);

        return redirect()->route('budgets.index')
                        ->with('success', 'Ngân sách đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Budget $budget)
    {
        $budget->delete();

        return redirect()->route('budgets.index')
                        ->with('success', 'Ngân sách đã được xóa thành công.');
    }

    /**
     * Get domain suggestions for autocomplete
     */
    public function getDomainSuggestions(Request $request)
    {
        $search = $request->get('q', '');
        
        $domains = Website::where('name', 'like', "%{$search}%")
            ->distinct()
            ->pluck('name')
            ->map(function($domain) {
                // Clean domain name - remove http/https and www
                $domain = strtolower(trim($domain));
                $domain = preg_replace('/^https?:\/\//', '', $domain);
                $domain = preg_replace('/^www\./', '', $domain);
                return $domain;
            })
            ->filter() // Remove empty values
            ->unique()
            ->sort()
            ->values()
            ->take(10); // Limit to 10 suggestions

        return response()->json($domains);
    }
}
