<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;
use App\Notifications\RegisteredToActivityNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ActivityRegisterService
{
    protected User $user;
    protected Activity $activity;
    public function __construct(User $user, Activity $activity)
    {
        $this->user = $user;
        $this->activity = $activity;
    }

    public function registerOnActivity(): string
    {
        abort_if($this->user->activities()->where('id', $this->activity->id)->exists(), Response::HTTP_CONFLICT);
        DB::beginTransaction();
        try {
            $this->user->activities()->attach($this->activity->id);

            $this->user->notify(new RegisteredToActivityNotification($this->activity));
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            Log::info($exception);
            return 'Sorry, you have not registered to activity. Please try later.';
        }

        return 'You have successfully registered.';
    }
}
