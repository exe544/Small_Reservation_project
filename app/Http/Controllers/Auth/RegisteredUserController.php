<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\RegistrationInvitation;
use App\Models\User;
use App\Notifications\RegisteredToActivityNotification;
use App\Providers\RouteServiceProvider;
use App\Services\ActivityRegisterService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        $email = null;

        if ($request->has('activity')){
            session()->put('activity', $request->input('activity'));
        }

        if($request->has('invitation_token')){

            $token = $request->input('invitation_token');

            session()->put('invitation_token', $token);

            $invitation = RegistrationInvitation::where('token', $token)->whereNull('registered_at')->firstOrFail();

            $email = $invitation->email;
        }

        return view('auth.register', compact('email'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($request->session()->get('invitation_token')) {
            $invitation = RegistrationInvitation::where('token', $request->session()->get('invitation_token'))
                ->where('email', $request->email)
                ->whereNull('registered_at')
                ->firstOr(fn() => throw ValidationException::withMessages(['invitation' => 'Invitation link does not match email indicated!']));

            $roleFromInvite = $invitation->role_id;
            $company_id = $invitation->company_id;

            $invitation->update(['registered_at' => now()]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $roleFromInvite ?? Role::CUSTOMER->value,
            'company_id' => $company_id ?? null,
        ]);

        event(new Registered($user));

        Auth::login($user);

        if ($request->session()->get('activity')){

            $message = (new ActivityRegisterService($user, Activity::find($request->session()->get('activity'))))->registerOnActivity();

            return redirect()->route('my-activity.show')->with('success', $message);
        }

        return redirect(RouteServiceProvider::HOME);
    }
}
