<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission;

class ModulePermission extends Model
{
    use HasUuids;

    protected $fillable = [
        'module_id',
        'permission_id',
        'is_required',
        'description',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }
}
