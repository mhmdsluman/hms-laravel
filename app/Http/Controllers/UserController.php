<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): Response
    {
        $allowed = ['name', 'email', 'role', 'created_at', 'speciality'];
        $sort = $request->get('sort', 'created_at');
        if (! in_array($sort, $allowed)) {
            $sort = 'created_at';
        }

        $direction = $request->get('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $perPage = (int) $request->get('perPage', 10);

        $query = User::query();

        if ($q = $request->get('q')) {
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('speciality', 'like', "%{$q}%");
            });
        }

        $users = $query->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => [
                'sort' => $sort,
                'direction' => $direction,
                'perPage' => $perPage,
                'q' => $request->get('q', ''),
            ],
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        return Inertia::render('Users/Create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:'.User::class,
            'role' => ['required', Rule::in(['admin', 'clinician', 'clerk', 'lab', 'pharmacy', 'radiology', 'patient', 'nurse', 'ot_manager'])],
            'speciality' => 'nullable|string|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'photo' => 'nullable|image|max:2048', // <-- ADDED
        ]);

        // Handle File Upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('user_photos', 'public');
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'speciality' => $request->speciality,
            'password' => Hash::make($request->password),
            'photo_path' => $photoPath, // <-- ADDED
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user (view).
     */
    public function show(User $user): Response
    {
        // Optional authorization (uncomment if you have policies)
        // $this->authorize('view', $user);

        // Return a safe, minimal payload to the frontend
        return Inertia::render('Users/Show', [
            'user' => $user->only(['id', 'name', 'email', 'role', 'speciality', 'created_at', 'updated_at']),
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        // Note: You may want to add photo upload logic here as well
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'clinician', 'clerk', 'lab', 'pharmacy', 'radiology', 'patient', 'nurse', 'ot_manager'])],
            'speciality' => 'nullable|string|max:255',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->speciality = $request->speciality;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
