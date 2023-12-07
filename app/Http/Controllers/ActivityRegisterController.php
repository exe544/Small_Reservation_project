<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ActivityRegisterController extends Controller
{
    public function store(Activity $activity)
    {
      if (!Auth::check()){
          return to_route('register', ['activity' => $activity->id]);
      }
      $user = Auth::user();

      abort_if($user->activities()->where('id', $activity->id)->exists(), Response::HTTP_CONFLICT);

      $user->activities()->attach($activity->id);

      return to_route('my-activity.show')->with('success', 'You have successfully registered.');
    }
}
