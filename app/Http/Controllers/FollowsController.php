<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FollowsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(User $user)
    {
        // Grab the authenticated user, invoke the following function 
        // and attach or detach the relationship between these two
        return auth()->user()->following()->toggle($user->profile);
    }
}
