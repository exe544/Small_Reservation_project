<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Company\CompanyStoreRequest;
use App\Http\Requests\Company\CompanyUpdateRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{

    public function index(): View
    {
        $companies = Company::all();

        return view('companies.index', compact('companies'));
    }

    public function create(): View
    {
        return view('companies.create');
    }

    public function store(CompanyStoreRequest $request): RedirectResponse
    {
       $newCompany = Company::create($request->validated());

       return to_route('companies.index');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(Company $company): View
    {
        return view('companies.edit', compact('company'));

    }
    
    public function update(CompanyUpdateRequest $request, Company $company): RedirectResponse
    {
        return to_route('companies.index');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return to_route('companies.index');
    }
}
