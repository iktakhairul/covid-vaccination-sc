<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Display the vaccination status based on the user's NID.
     *
     * @param Request $request The incoming HTTP request containing user input.
     * @return Factory|View The view displaying the vaccination status and user details.
     */
    public function index(Request $request)
    {
        $user = [];
        // Fetch user and related vaccine center details
        if (!empty($request->user_nid)) {
            $user = User::with('vaccineCenter')->where('nid', $request->user_nid)->first();
        }

        return view('frontend.search', compact('user'));
    }
}

