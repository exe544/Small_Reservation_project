<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\Role;
use Illuminate\Support\Collection;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'company_id', 'id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'company_id', 'id');
    }

    public function guides(): Collection
    {
        return $this->users()->where('role_id', Role::GUIDE->value)->pluck('name', 'id');
    }
}
