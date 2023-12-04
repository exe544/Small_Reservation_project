<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\Guide\GuideStoreRequest;
use App\Http\Requests\Guide\GuideUpdateRequest;
use App\Mail\RegistrationInviteMail;
use App\Models\Company;
use App\Models\RegistrationInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CompanyGuideController extends Controller
{

    public function index(Company $company): View
    {
        $this->authorize('viewAny', $company);

        $guides = $company->users()->where('role_id', Role::GUIDE->value)->get();

        return view('companies.guides.index', compact(['guides', 'company']));
    }

    public function create(Company $company): View
    {
        $this->authorize('create', $company);

        return view('companies.guides.create', compact('company'));
    }

    public function store(GuideStoreRequest $request, Company $company): RedirectResponse
    {
        $this->authorize('create', $company);

        $email = $request->validated('email');

        $invitation = RegistrationInvitation::create([
            'email' => $email,
            'token' => Str::uuid(),
            'company_id' => $company->id,
            'role_id' => Role::GUIDE->value,
        ]);

        Mail::to($email)->send(new RegistrationInviteMail($invitation));

       return to_route('companies.guides.index', $company);
    }

    public function edit(Company $company, User $guide): View
    {
        $this->authorize('update', $company);

        return view('companies.guides.edit', compact(['company', 'guide',]));
    }

    public function update(GuideUpdateRequest $request, Company $company, User $guide): RedirectResponse
    {
        $this->authorize('update', $company);

        $newData = $request->validated();
        $guide->update($newData);

        return to_route('companies.guides.index', compact('company'));
    }

    public function destroy(Company $company, User $guide): RedirectResponse
    {
        $this->authorize('delete', $company);

        $guide->delete();

        return to_route('companies.guides.index', compact('company'));
    }
}
