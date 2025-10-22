<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\User;
use App\Services\CloudflareService;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Website::with('seoerUser');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('seoer', 'like', "%{$search}%")
                  ->orWhereHas('seoerUser', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by seoer (now by user ID)
        if ($request->filled('seoer')) {
            $query->where('seoer_id', $request->seoer);
        }

        // Filter by 301 redirect status
        if ($request->filled('has_301_redirect')) {
            $query->where('has_301_redirect', $request->has_301_redirect);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $websites = $query->orderBy('created_at', 'desc')->paginate(10);
        $users = User::where('is_active', true)
                    ->whereHas('role', function($query) {
                        $query->where('name', 'seoer');
                    })
                    ->orderBy('name')
                    ->get();

        return view('websites.index', compact('websites', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('is_active', true)
                    ->whereHas('role', function($query) {
                        $query->where('name', 'seoer');
                    })
                    ->orderBy('name')
                    ->get();
        return view('websites.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'seoer_id' => 'required|exists:users,id',
            'status' => 'required|in:active,inactive,maintenance',
            'category' => 'nullable|in:brand,phishing,key_nganh,pbn',
            'has_301_redirect' => 'boolean',
            'redirect_to_domain' => 'nullable|string|max:255',
            'cloudflare_zone_id' => 'nullable|string|max:255',
            'delivery_date' => 'nullable|date',
            'purchase_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'bot_open_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['has_301_redirect'] = $request->has('has_301_redirect');
        
        // Set seoer field for backward compatibility
        if (isset($data['seoer_id'])) {
            $seoerUser = User::find($data['seoer_id']);
            $data['seoer'] = $seoerUser ? $seoerUser->name : null;
        }

        $website = Website::create($data);

        // Handle 301 redirect creation if requested and user is IT or Admin
        if ($request->has('has_301_redirect') && $request->filled('redirect_to_domain')) {
            if (auth()->user()->role && in_array(auth()->user()->role->name, ['it', 'admin'])) {
                $result = $website->create301Redirect($request->redirect_to_domain);
                if ($result['success']) {
                    $message = $result['message'] ?? 'Page rule đã được cập nhật';
                    return redirect()->route('websites.index')
                                    ->with('success', "Website đã được tạo thành công. {$message} trên Cloudflare.");
                } else {
                    return redirect()->route('websites.index')
                                    ->with('warning', 'Website đã được tạo nhưng không thể tạo 301 redirect: ' . $result['error']);
                }
            }
        }

        return redirect()->route('websites.index')
                        ->with('success', 'Website đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Website $website)
    {
        return view('websites.show', compact('website'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Website $website)
    {
        $users = User::where('is_active', true)
                    ->whereHas('role', function($query) {
                        $query->where('name', 'seoer');
                    })
                    ->orderBy('name')
                    ->get();
        return view('websites.edit', compact('website', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Website $website)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'seoer_id' => 'required|exists:users,id',
            'status' => 'required|in:active,inactive,maintenance',
            'category' => 'nullable|in:brand,phishing,key_nganh,pbn',
            'has_301_redirect' => 'boolean',
            'redirect_to_domain' => 'nullable|string|max:255',
            'cloudflare_zone_id' => 'nullable|string|max:255',
            'delivery_date' => 'nullable|date',
            'purchase_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'bot_open_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['has_301_redirect'] = $request->has('has_301_redirect');
        
        // Set seoer field for backward compatibility
        if (isset($data['seoer_id'])) {
            $seoerUser = User::find($data['seoer_id']);
            $data['seoer'] = $seoerUser ? $seoerUser->name : null;
        }

        $website->update($data);

        // Handle 301 redirect creation/update if requested and user is IT or Admin
        if ($request->has('has_301_redirect') && $request->filled('redirect_to_domain')) {
            if (auth()->user()->role && in_array(auth()->user()->role->name, ['it', 'admin'])) {
                $result = $website->create301Redirect($request->redirect_to_domain);
                if ($result['success']) {
                    $message = $result['message'] ?? 'Page rule đã được cập nhật';
                    // Clear cache
                    cache()->forget("website_301_status_{$website->id}");
                    return redirect()->route('websites.index')
                                    ->with('success', "Website đã được cập nhật thành công. {$message} trên Cloudflare.");
                } else {
                    return redirect()->route('websites.index')
                                    ->with('warning', 'Website đã được cập nhật nhưng không thể tạo 301 redirect: ' . $result['error']);
                }
            }
        }

        return redirect()->route('websites.index')
                        ->with('success', 'Website đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Website $website)
    {
        $website->delete();

        return redirect()->route('websites.index')
                        ->with('success', 'Website đã được xóa thành công.');
    }

    /**
     * Create 301 redirect for website
     */
    public function create301Redirect(Request $request, Website $website)
    {
        // Only IT and Admin roles can create 301 redirects
        if (!auth()->user()->role || !in_array(auth()->user()->role->name, ['it', 'admin'])) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ có IT và Admin mới được phép tạo 301 redirect'
            ], 403);
        }

        $request->validate([
            'redirect_to_domain' => 'required|url'
        ]);

        $result = $website->create301Redirect($request->redirect_to_domain);

        if ($result['success']) {
            // Update website record
            $website->update([
                'has_301_redirect' => true,
                'redirect_to_domain' => $request->redirect_to_domain
            ]);

            // Clear cache
            cache()->forget("website_301_status_{$website->id}");

            return response()->json([
                'success' => true,
                'message' => '301 redirect đã được tạo thành công'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Không thể tạo 301 redirect'
            ], 400);
        }
    }

    /**
     * Check 301 redirect status via AJAX
     */
    public function check301Status(Website $website)
    {
        $status = $website->checkCloudflare301Status();
        
        return response()->json($status);
    }

    /**
     * Sync all domains from Cloudflare
     */
    public function syncFromCloudflare()
    {
        // Only admin and IT can sync domains
        if (!auth()->user()->role || !in_array(auth()->user()->role->name, ['admin', 'it'])) {
            return response()->json([
                'success' => false,
                'error' => 'Chỉ có Admin và IT mới được phép đồng bộ domain từ Cloudflare'
            ], 403);
        }

        try {
            // Set longer execution time for sync
            set_time_limit(300); // 5 minutes
            
            $cloudflareService = new CloudflareService();
            $results = $cloudflareService->syncDomainsToDatabase();

            if (count($results['errors']) > 0) {
                return response()->json([
                    'success' => true, // Still success but with warnings
                    'message' => 'Đồng bộ hoàn tất với một số cảnh báo',
                    'data' => $results
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Đồng bộ thành công từ Cloudflare',
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Sync from Cloudflare failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Lỗi hệ thống: ' . $e->getMessage(),
                'data' => [
                    'total_cf_domains' => 0,
                    'new_domains' => 0,
                    'updated_domains' => 0,
                    'errors' => [$e->getMessage()]
                ]
            ], 500);
        }
    }

    /**
     * Show Cloudflare sync page
     */
    public function showCloudflareSync()
    {
        \Log::info('showCloudflareSync called');
        
        $user = auth()->user();
        \Log::info('Current user: ' . ($user ? $user->email : 'NULL'));
        
        // Check if user is authenticated
        if (!$user) {
            \Log::warning('User not authenticated');
            abort(401, 'Vui lòng đăng nhập để truy cập trang này');
        }
        
        // Check if user has role
        if (!$user->role) {
            \Log::warning('User has no role: ' . $user->email);
            abort(403, 'Tài khoản của bạn chưa được phân quyền. Vui lòng liên hệ admin.');
        }
        
        \Log::info('User role: ' . $user->role->name);
        
        // Only admin and IT can access sync page
        if (!in_array($user->role->name, ['admin', 'it'])) {
            \Log::warning('User does not have permission. Role: ' . $user->role->name);
            return view('errors.403')->with([
                'message' => 'Chỉ có Admin và IT mới được phép truy cập trang này.',
                'details' => 'Role hiện tại của bạn: ' . $user->role->name
            ]);
        }

        try {
            \Log::info('Rendering cloudflare-sync view');
            return view('websites.cloudflare-sync-simple');
        } catch (\Exception $e) {
            \Log::error('Error rendering cloudflare-sync view: ' . $e->getMessage());
            return response()->view('errors.500', [
                'message' => 'Lỗi tải trang đồng bộ Cloudflare',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
