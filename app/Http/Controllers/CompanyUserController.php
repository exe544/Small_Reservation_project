<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyUserController extends Controller
{
    public function index(Company $company): View
    {
        $this->authorize('viewAny', $company);

        $users = $company->users()->where('role_id', Role::COMPANY_OWNER->value)->get();

        return view('companies.users.index', compact(['users', 'company']));

    }

    public function create(Company $company): View
    {
        $this->authorize('create', $company);

        return view('companies.users.create', compact('company'));
    }

    public function store(UserStoreRequest $request, Company $company): RedirectResponse
    {
        $this->authorize('create', $company);

        $validatedData = $request->validated();
        $newUser = $company->users()->create([
            'name' => $validatedData['name'],
            'email'=> $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'role_id' => Role::COMPANY_OWNER->value,
        ]);

        return to_route('companies.users.index', compact('company'));
    }

    public function edit(Company $company, User $user): View
    {
        $this->authorize('update', $company);

        return view('companies.users.edit', compact('company', 'user'));
    }

    public function update(UserUpdateRequest $request, Company $company, User $user): RedirectResponse
    {
        $this->authorize('update', $company);

        $user->update($request->validated());

        return to_route('companies.users.index', compact('company'));
    }

    public function destroy(Company $company, User $user): RedirectResponse
    {
        $this->authorize('delete', $company);
        $user->delete();

        return to_route('companies.users.index', compact('company'));
    }

//    public function show(string $id)
//    {
//        //
//    }
}
