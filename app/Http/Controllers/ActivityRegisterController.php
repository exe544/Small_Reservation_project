<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Notifications\RegisteredToActivityNotification;
use App\Services\ActivityRegisterService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ActivityRegisterController extends Controller
{
    public function store(Activity $activity)
    {
      if (!Auth::check()){
          return to_route('register', ['activity' => $activity->id]);
      }

      $message = (new ActivityRegisterService(Auth::user(), $activity))->registerOnActivity();

      return to_route('my-activity.show')->with('success', $message);
    }
}
