<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with('role');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy('module');
        
        return view('users.create', compact('roles', 'permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
            'phone' => 'nullable|string|max:20',
            'telegram' => 'nullable|string|max:255',
            'payment_info' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'hire_date' => 'nullable|date',
            'permanent_date' => 'nullable|date|after_or_equal:hire_date',
            'resignation_date' => 'nullable|date|after_or_equal:hire_date',
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($request->password);
        $data['is_active'] = $request->has('is_active');

        $user = User::create($data);

        // Handle custom permissions if provided
        if ($request->has('permissions')) {
            $role = $user->role;
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('users.index')
                        ->with('success', 'Người dùng đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('role.permissions');
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all()->groupBy('module');
        $user->load('role.permissions');
        
        return view('users.edit', compact('user', 'roles', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
            'phone' => 'nullable|string|max:20',
            'telegram' => 'nullable|string|max:255',
            'payment_info' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'hire_date' => 'nullable|date',
            'permanent_date' => 'nullable|date|after_or_equal:hire_date',
            'resignation_date' => 'nullable|date|after_or_equal:hire_date',
        ]);

        $data = $request->all();
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }
        
        $data['is_active'] = $request->has('is_active');

        $user->update($data);

        // Handle custom permissions if provided
        if ($request->has('permissions')) {
            $role = $user->role;
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('users.index')
                        ->with('success', 'Người dùng đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting the current user
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                            ->with('error', 'Bạn không thể xóa tài khoản của chính mình.');
        }

        $user->delete();

        return redirect()->route('users.index')
                        ->with('success', 'Người dùng đã được xóa thành công.');
    }

    /**
     * Show the profile form for the authenticated user.
     */
    public function profile()
    {
        $user = auth()->user();
        $roles = Role::all(); // For reference, but user cannot change their role
        
        return view('users.profile', compact('user', 'roles'));
    }

    /**
     * Update the authenticated user's profile.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'telegram' => 'nullable|string|max:255',
            'payment_info' => 'nullable|string',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'telegram', 'payment_info']);
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('profile')
                        ->with('success', 'Thông tin cá nhân đã được cập nhật thành công.');
    }
}
