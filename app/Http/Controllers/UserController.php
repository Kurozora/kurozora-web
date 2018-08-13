<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function register(Request $request) {
        // Check if the required parameters were set
        if(
            !$request->has('username') ||
            !$request->has('email') ||
            !$request->has('password')
        ) 
    }
}
