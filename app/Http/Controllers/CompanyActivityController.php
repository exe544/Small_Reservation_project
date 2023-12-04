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
use Intervention\Image\Facades\Image;

class CompanyActivityController extends Controller
{

    public function index(Company $company): View
    {
        $this->authorize('viewAny', $company);

        $company = $company->load('activities');

        return view('companies.activities.index', compact('company'));
    }

    public function create(Company $company): View
    {
        $this->authorize('create', $company);

        $guides = $company->guides();

        return view('companies.activities.create', compact('company', 'guides'));
    }

    public function store(ActivityStoreRequest $request, Company $company): RedirectResponse
    {
        $this->authorize('create', $company);

        $validatedData = $request->validated();

        $fileName = $this->uploadImage($validatedData);

        Activity::create($validatedData + [
            'company_id' => $company->id,
            'photo' => $fileName,
            ]);

        return to_route('companies.activities.index', $company);
    }

    public function edit(Company $company, Activity $activity): View
    {
        $this->authorize('update', $activity);
        $guides = $company->guides();

        return view('companies.activities.edit', compact('guides', 'activity', 'company'));
    }

    public function update(ActivityUpdateRequest $request, Company $company, Activity $activity): RedirectResponse
    {
        $this->authorize('update', $activity);
        $validatedData = $request->validated();

        $fileName = $this->uploadImage($validatedData);

        if($fileName && $activity->photo) {
             Storage::disk('activities')->delete($activity->photo);
        }

        $activity->update($validatedData + [
            'photo' => $fileName ?? $activity->photo,
            ]);

        return to_route('companies.activities.index', $company);
    }

    public function destroy(Company $company, Activity $activity): RedirectResponse
    {
        $this->authorize('delete', $activity);

        $activity->delete();

        return to_route('companies.activities.index', $company);
    }

    private function uploadImage(array $validatedData): string|null
    {
        if (!isset($validatedData['image'])) {
           return null;
        }

        $fileName = $validatedData['image']->store(options: 'activities');

        $img = Image::make(Storage::disk('activities')->get($fileName))
            ->resize(270, 270, function ($constraint) {
                $constraint->aspectRatio();
            });

        Storage::disk('activities')->put('thumbs/' . $validatedData['image']->hashName(), $img->stream());

        return $fileName;
    }
}
