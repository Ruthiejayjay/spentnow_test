<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Retrieve all users (Admin Only).
     */
    public function index()
    {
        $this->authorizeAdmin();
        return response()->json(User::all(), 200);
    }

    /**
     * Create a user (Admin Only).
     */
    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        return response()->json(['message' => 'User created successfully.', 'user' => $user], 201);
    }

    /**
     * Retrieve a single user's profile (Accessible by all authenticated users).
     */
    public function show($id)
    {
        $user = $this->findUser($id);
        $this->authorizeUserOrAdmin($user);

        return response()->json($user, 200);
    }

    /**
     * Update user information (Accessible by all authenticated users, excluding passwords).
     */
    public function update(Request $request, $id)
    {
        $user = $this->findUser($id);
        $this->authorizeUserOrAdmin($user);

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ]);

        $user->update($validatedData);

        return response()->json(['message' => 'User updated successfully.', 'user' => $user], 200);
    }

    /**
     * Delete a user (Admin Only).
     */

    public function destroy($id)
    {
        $this->authorizeAdmin();

        $user = $this->findUser($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }

    /**
     * Update a user's role (Admin Only).
     */
    public function updateRole(Request $request, $id)
    {
        $this->authorizeAdmin();

        $validatedData = $request->validate([
            'role' => 'required|string|in:admin,user',
        ]);

        $user = $this->findUser($id);
        $user->role = $validatedData['role'];
        $user->save();

        return response()->json(['message' => 'User role updated successfully.', 'user' => $user], 200);
    }

    /**
     * Helper: Authorize the request for admin users.
     */
    private function authorizeAdmin()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized.');
        }
    }

    /**
     * Helper: Authorize the request for the user or admin.
     */
    private function authorizeUserOrAdmin(User $user)
    {
        if (auth()->user()->role !== 'admin' && auth()->id() !== $user->id) {
            abort(403, 'Unauthorized.');
        }
    }

    /**
     * Helper: Find a user by ID or fail with a 404 response.
     */
    private function findUser($id): User
    {
        $user = User::find($id);

        if (!$user) {
            abort(404, 'User not found.');
        }

        return $user;
    }
}
