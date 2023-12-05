<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function show(Activity $activity): View
    {
        $guide = User::find($activity->guide_id);
        return view('activities.show', compact(['activity', 'guide']));
    }
}
