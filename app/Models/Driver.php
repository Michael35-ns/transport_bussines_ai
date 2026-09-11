<?php

namespace App\Models;

use App\Enums\ActiveStatus;
use Database\Factories\DriverFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $document_id
 * @property string|null $license_class
 * @property Carbon|null $license_expiry
 * @property Carbon|null $hire_date
 * @property string $hourly_rate
 * @property ActiveStatus $status
 */
#[Fillable(['name', 'document_id', 'license_class', 'license_expiry', 'hire_date', 'hourly_rate', 'status'])]
class Driver extends Model
{
    /** @use HasFactory<DriverFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'license_expiry' => 'date',
            'hire_date' => 'date',
            'hourly_rate' => 'decimal:4',
            'status' => ActiveStatus::class,
        ];
    }

    /**
     * @return HasMany<Trip, $this>
     */
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * @return HasMany<DriverWorklog, $this>
     */
    public function worklogs(): HasMany
    {
        return $this->hasMany(DriverWorklog::class);
    }
}
