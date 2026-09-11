<?php

namespace App\Models;

use Database\Factories\MaintenanceProviderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string|null $contact
 * @property string|null $specialty
 */
#[Fillable(['name', 'contact', 'specialty'])]
class MaintenanceProvider extends Model
{
    /** @use HasFactory<MaintenanceProviderFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return HasMany<Maintenance, $this>
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class, 'provider_id');
    }

    /**
     * @return HasMany<Tire, $this>
     */
    public function tires(): HasMany
    {
        return $this->hasMany(Tire::class, 'provider_id');
    }
}
