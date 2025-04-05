<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TrelloService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    /**
     * The Trello service instance.
     *
     * @var \App\Services\TrelloService
     */
    protected $trelloService;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\TrelloService $trelloService
     * @return void
     */
    public function __construct(TrelloService $trelloService)
    {
        $this->trelloService = $trelloService;
    }

    /**
     * Display a listing of the users.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Use eager loading to reduce database queries
        $users = User::where('is_approved', true)->orderBy('created_at', 'desc')->paginate(10);
        $pendingUsers = User::where('is_approved', false)->orderBy('created_at', 'desc')->get();
        
        // Get Trello user teams data if credentials are configured
        $userTeamsMap = [];
        
        if ($this->trelloService->hasValidCredentials()) {
            try {
                // Get all boards with members using our cached service
                $options = [
                    'members' => true,
                    'member_fields' => 'fullName'
                ];
                
                $boards = $this->trelloService->getBoards(['id', 'name'], $options);
                
                // Create a mapping of users to their teams
                foreach ($users as $user) {
                    $userTeamsMap[$user->id] = [];
                    
                    // For each board, check if user is a member
                    foreach ($boards as $board) {
                        if (isset($board['members'])) {
                            foreach ($board['members'] as $member) {
                                if (isset($member['fullName']) && $member['fullName'] === $user->name) {
                                    $userTeamsMap[$user->id][] = [
                                        'id' => $board['id'],
                                        'name' => $board['name']
                                    ];
                                    break;
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Just log the error but don't fail - the view will show empty team data
                \Log::error('Error fetching Trello teams: ' . $e->getMessage());
            }
        }
        
        return view('users.index', compact('users', 'pendingUsers', 'userTeamsMap'));
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', 'in:admin,tester,dev'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        // Prevent self deletion
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete yourself.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    public function approve(User $user)
    {
        $user->update(['is_approved' => true]);
        return redirect()->route('users.index')
            ->with('success', 'User has been approved successfully.');
    }

    public function reject(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')
            ->with('success', 'User has been rejected and removed.');
    }
}