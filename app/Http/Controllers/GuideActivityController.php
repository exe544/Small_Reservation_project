<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class GuideActivityController extends Controller
{
    public function show(): View
    {
        abort_if(Auth::user()->role_id !== Role::GUIDE->value, Response::HTTP_FORBIDDEN );

        $activities = Activity::where('guide_id', Auth::id())->orderBy('start_date')->paginate(9);

        return view('activities.guide-activities', compact('activities'));
    }
}
