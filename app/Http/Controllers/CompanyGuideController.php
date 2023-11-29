<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\Guide\GuideStoreRequest;
use App\Http\Requests\Guide\GuideUpdateRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $data = $request->validated();

        $company->users()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role_id' => Role::GUIDE->value,
        ]);

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
        $guide->delete();

        return to_route('companies.guides.index', compact('company'));
    }
}
