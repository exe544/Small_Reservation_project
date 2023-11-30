<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Activity\ActivityStoreRequest;
use App\Http\Requests\Activity\ActivityUpdateRequest;
use App\Models\Activity;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompanyActivityController extends Controller
{

    public function index(Company $company): View
    {
        $company = $company->load('activities');

        return view('companies.activities.index', compact('company'));
    }

    public function create(Company $company): View
    {
        $guides = $company->guides();

        return view('companies.activities.create', compact('company', 'guides'));
    }

    public function store(ActivityStoreRequest $request, Company $company): RedirectResponse
    {
        if ($request->hasFile('image')) {
                $path = $request->file('image')->store('activities', 'public');
        }

        Activity::create($request->validated() + ['company_id' => $company->id, 'photo' => $path ?? null]);

        return to_route('companies.activities.index', $company);
    }

    public function edit(Company $company, Activity $activity): View
    {
        $guides = $company->guides();

        return view('companies.activities.edit', compact('guides', 'activity', 'company'));
    }

    public function update(ActivityUpdateRequest $request, Company $company, Activity $activity)
    {
        $validatedData = $request->validated();

        if (isset($validatedData['image'])) {
           $path = $validatedData['image']->store('activities', 'public');
           if($activity->photo){
             Storage::disk('public')->delete($activity->photo);
           }
        }

        $activity->update($validatedData + [
            'photo' => $path ?? $activity->photo,
            ]);

        return to_route('companies.activities.index', $company);
    }

    public function destroy(Company $company, Activity $activity): RedirectResponse
    {
        $activity->delete();

        return to_route('companies.activities.index', $company);
    }
}
