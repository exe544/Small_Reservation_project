<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $activities = Activity::where('start_date', '>', now())
            ->orderBy('start_date')
            ->paginate(9);

        return view('home', compact('activities'));
    }
}
