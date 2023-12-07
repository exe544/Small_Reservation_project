<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyActivityController extends Controller
{
    public function show(): View
    {
        $activities = Auth::user()->activities()->orderBy('start_date')->paginate(9);
        return view('activities.my-activities', compact('activities'));
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        abort_if(! Auth::user()->activities->contains($activity), 403);
        Auth::user()->activities()->detach($activity);

        return to_route('my-activity.show')->with('success', 'Activity removed!');
    }
}
