<?php
// app/Http/Controllers/UserController.php
//namespace App\Http\Controllers;

//use App\Models\User;
//use Illuminate\Http\Request;

//class UserController extends Controller
//{
//    public function index()
//    {
//        $users = User::all();
//        return view('users.index', compact('users'));
//    }
//}




//<?php
// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Role-level guard — current user cannot assign roles >= their own level
    |--------------------------------------------------------------------------
    */
    private function maxAssignableLevel(): int
    {
        $user = auth()->user();
        if ($user->hasRole('superadmin')) return 999;

        return $user->roles->max('level') - 1; // can only assign BELOW own level
    }

    private function assignableRoles(): \Illuminate\Support\Collection
    {
        return Role::where('level', '<=', $this->maxAssignableLevel())
                   ->orderBy('level', 'desc')
                   ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | index — card grid with search + pagination
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $this->authorize('manage users');

        $query = User::with(['roles', 'departments']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name',        'like', "%{$search}%")
                  ->orWhere('middle_name','like', "%{$search}%")
                  ->orWhere('last_name',  'like', "%{$search}%")
                  ->orWhere('email',      'like', "%{$search}%")
                  ->orWhere('ein',        'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Department filter
        if ($deptId = $request->input('department_id')) {
            $query->whereHas('departments', fn($q) => $q->where('departments.id', $deptId));
        }

        // HR sees only their department's users
        if (auth()->user()->hasRole('hr') && !auth()->user()->hasRole(['superadmin','admin'])) {
            $hrDeptIds = auth()->user()->departments->pluck('id');
            $query->whereHas('departments', fn($q) => $q->whereIn('departments.id', $hrDeptIds));
        }

        $users = $query->latest()->paginate(12)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('users.index', compact('users', 'departments'));
    }

    /*
    |--------------------------------------------------------------------------
    | create / store — add new user
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $this->authorize('manage users');

        $roles       = $this->assignableRoles();
        $departments = Department::orderBy('type')->orderBy('name')->get();

        return view('users.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage users');

        $data = $request->validate([
            'ein' => ['nullable','string','max:20','unique:users,ein'],
            'name' => ['required','string','max:100'],
            'middle_name' => ['nullable','string','max:100'],
            'last_name' => ['required','string','max:100'],
            'email' => ['required','email','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
            'phone' => ['nullable','string','max:20'],
            'phone_extension' => ['nullable','string','max:10'],
            'status' => ['required','in:active,inactive,suspended'],
            'role_id' => ['required','exists:roles,id'],
            'department_ids' => ['nullable','array'],
            'department_ids.*' => ['exists:departments,id'],
            'primary_dept' => ['nullable','exists:departments,id'],
            'avatar' => ['nullable','image','max:2048'],
        ]);

        $role = Role::findById($data['role_id']);
        abort_if($role->level > $this->maxAssignableLevel(), 403, 'Cannot assign a role with higher level than your own.');

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create([
            'ein' => $data['ein'] ?? null,
            'name' => $data['name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'phone_extension' => $data['phone_extension'] ?? null,
            'status' => $data['status'],
            'avatar' => $avatarPath,
        ]);

        $user->assignRole($role);

        if (!empty($data['department_ids'])) {
            $primaryId = $data['primary_dept'] ?? $data['department_ids'][0];
            foreach ($data['department_ids'] as $deptId) {
                $user->departments()->attach($deptId, [
                    'is_primary' => ($deptId == $primaryId) ? 1 : 0,
                ]);
            }
        }

        // ✅ LOG USER CREATION
        ActivityLogService::userCreated($user);
        // Or use helper:
        // logUserCreated($user);

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} {$user->last_name} created successfully.");
    }

    /*
    |--------------------------------------------------------------------------
    | edit / update
    |--------------------------------------------------------------------------
    */
    public function edit(User $user)
    {
        $this->authorize('manage users');

        $roles           = $this->assignableRoles();
        $departments     = Department::orderBy('type')->orderBy('name')->get();
        $userDeptIds     = $user->departments->pluck('id')->toArray();
        $primaryDeptId   = $user->departments()->wherePivot('is_primary', 1)->value('departments.id');
        $currentRoleId   = $user->roles->first()?->id;

        return view('users.edit', compact(
            'user', 'roles', 'departments', 'userDeptIds', 'primaryDeptId', 'currentRoleId'
        ));
    }

        public function update(Request $request, User $user)
    {
        $this->authorize('manage users');

        // Store old data for change tracking
        $oldData = $user->toArray();

        $data = $request->validate([
            'ein' => ['nullable','string','max:20', Rule::unique('users','ein')->ignore($user->id)],
            'name' => ['required','string','max:100'],
            'middle_name' => ['nullable','string','max:100'],
            'last_name' => ['required','string','max:100'],
            'email' => ['required','email', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','string','min:8','confirmed'],
            'phone' => ['nullable','string','max:20'],
            'phone_extension' => ['nullable','string','max:10'],
            'status' => ['required','in:active,inactive,suspended'],
            'role_id' => ['required','exists:roles,id'],
            'department_ids' => ['nullable','array'],
            'department_ids.*' => ['exists:departments,id'],
            'primary_dept' => ['nullable','exists:departments,id'],
            'avatar' => ['nullable','image','max:2048'],
            'remove_avatar' => ['nullable','boolean'],
        ]);

        $role = Role::findById($data['role_id']);
        abort_if($role->level > $this->maxAssignableLevel(), 403, 'Cannot assign a role with higher level than your own.');

        // Avatar handling
        if ($request->boolean('remove_avatar') && $user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = null;
        } elseif ($request->hasFile('avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } else {
            unset($data['avatar']);
        }

        $updateData = [
            'ein' => $data['ein'] ?? null,
            'name' => $data['name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'phone_extension' => $data['phone_extension'] ?? null,
            'status' => $data['status'],
        ];
        if (isset($data['avatar'])) $updateData['avatar'] = $data['avatar'];
        if (!empty($data['password'])) $updateData['password'] = Hash::make($data['password']);

        $user->update($updateData);
        $user->syncRoles([$role->name]);

        $deptIds = $data['department_ids'] ?? [];
        $primaryId = $data['primary_dept'] ?? ($deptIds[0] ?? null);
        $syncData = [];
        foreach ($deptIds as $deptId) {
            $syncData[$deptId] = ['is_primary' => ($deptId == $primaryId) ? 1 : 0];
        }
        $user->departments()->sync($syncData);

        // ✅ LOG USER UPDATE - Track what changed
        $changes = array_diff_assoc($user->fresh()->toArray(), $oldData);
        ActivityLogService::userUpdated($user, $changes);
        // Or use helper:
        // logUserUpdated($user, $changes);

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} {$user->last_name} updated successfully.");
    }


    /*
    |--------------------------------------------------------------------------
    | toggle — active ↔ inactive
    |--------------------------------------------------------------------------
    */
    public function toggle(User $user)
    {
        $this->authorize('manage users');

        abort_if($user->id === auth()->id(), 403, 'You cannot deactivate your own account.');

        $targetLevel = $user->roles->max('level') ?? 0;
        abort_if($targetLevel >= $this->maxAssignableLevel() + 1 && !auth()->user()->hasRole('superadmin'),
            403, 'Insufficient level to change this user status.');

        $oldStatus = $user->status;
        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
        ]);

        // ✅ LOG STATUS CHANGE
        ActivityLogService::userStatusChanged($user, $oldStatus, $user->status);
        // Or use helper:
        // logUserStatusChanged($user, $oldStatus, $user->status);

        return back()->with('success',
            "User {$user->name} has been " . ($user->status === 'active' ? 'activated' : 'deactivated') . '.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | show — AJAX business card JSON
    |--------------------------------------------------------------------------
    */
    public function show(User $user)
    {
        $this->authorize('manage users');

        $user->load(['roles', 'departments']);

        return response()->json([
            'id'          => $user->id,
            'ein'         => $user->ein ?? '—',
            'name'        => $user->name . ' ' . $user->last_name,
            'email'       => $user->email,
            'phone'       => $user->phone ?? '—',
            'ext'         => $user->phone_extension ?? '—',
            'status'      => $user->status,
            'avatar'      => $user->avatar ? asset('storage/' . $user->avatar) : null,
            'initials'    => strtoupper(substr($user->name,0,1) . substr($user->last_name,0,1)),
            'roles'       => $user->roles->pluck('name'),
            'departments' => $user->departments->map(fn($d) => [
                'name'       => $d->name,
                'type'       => $d->type,
                'is_primary' => (bool) $d->pivot->is_primary,
            ]),
            'joined'      => $user->created_at?->format('d M Y'),
            'last_login'  => $user->last_login_at?->diffForHumans() ?? 'Never',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete user (just in case we want to implement hard delete, but usually we recommend soft deletes)
    |--------------------------------------------------------------------------
    */
    public function destroy(User $user)
    {
        $this->authorize('manage users');
        abort_if($user->id === auth()->id(), 403, 'You cannot delete your own account.');

        $name = "{$user->name} {$user->last_name}";

        // ✅ LOG USER DELETION (before deleting)
        ActivityLogService::userDeleted($user);
        // Or use helper:
        // logUserDeleted($user);

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return redirect()->route('users.index')
                  ->with('success', "User '{$name}' has been deleted.");
    }

    
     /**
     * View activity logs for a specific user
     */
    public function activityLog(User $user)
    {
        $this->authorize('manage users');
        
        $logs = \App\Models\UserActivityLog::affecting($user->id)->paginate(20);
        
        return view('users.activity', compact('user', 'logs'));
    }

    /**
     * View system-wide activity log
     */
    public function globalActivity(Request $request)
    {
        $this->authorize('manage users');
        
        $query = \App\Models\UserActivityLog::with(['user', 'targetUser']);
        
        if ($action = $request->input('action')) {
            $query->where('action_type', $action);
        }
        
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }
        
        if ($date = $request->input('date')) {
            $query->whereDate('created_at', $date);
        }
        
        $logs = $query->latest()->paginate(30)->withQueryString();
        $users = User::orderBy('name')->get();
        
        return view('users.global-activity', compact('logs', 'users'));
    }



}