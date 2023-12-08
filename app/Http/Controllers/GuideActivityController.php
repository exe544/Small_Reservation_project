<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Activity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class GuideActivityController extends Controller
{
    public function show(): View
    {
        abort_if(Auth::user()->role_id !== Role::GUIDE->value, Response::HTTP_FORBIDDEN);

        $activities = Activity::where('guide_id', Auth::id())->orderBy('start_date')->paginate(9);

        return view('activities.guide-activities', compact('activities'));
    }

    public function export(Activity $activity)
    {
        abort_if(Auth::user()->role_id !== Role::GUIDE->value, Response::HTTP_FORBIDDEN);

        $data = $activity->load(['participants' => function ($query) {
            $query->orderByPivot('created_at');
        }]);

        return Pdf::loadView('activities.pdf', ['data' => $data])->download("{$activity->name}.pdf");
    }
}
