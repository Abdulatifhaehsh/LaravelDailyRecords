<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query();

        if ($request->has('search')) {
            $users->where('name', 'like', '%' . $request->search . '%');
        }

        return view('users.index', ['users' => $users->paginate(10)]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        // Update Redis counts
        $gender = $user->gender;
        Redis::decr("{$gender}_count");

        return redirect()->route('users.index');
    }
}
