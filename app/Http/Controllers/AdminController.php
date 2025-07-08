<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_profiles' => UserProfile::count(),
            'recent_users' => User::latest()->take(5)->get(),
            'admin_users' => User::where('role', 'admin')->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    }

    public function users()
    {
        $users = User::with('profile')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => request()->only(['search']),
        ]);
    }
}
