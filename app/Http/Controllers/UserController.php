<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {   
        $users = \App\Models\User::paginate(5)->onEachSide(3);
        
        return view('users', ['users' => $users]);
    }
}
