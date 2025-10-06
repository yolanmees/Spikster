<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleSetting extends Model
{
    use HasUuids;

    protected $fillable = [
        'module_id',
        'setting_key',
        'setting_value',
        'setting_type',
        'is_encrypted',
        'is_visible',
        'group_name',
        'order_index',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
        'is_visible' => 'boolean',
        'order_index' => 'integer',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function getValue()
    {
        $value = $this->setting_value;

        if ($this->is_encrypted) {
            $value = decrypt($value);
        }

        return match ($this->setting_type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'float' => (float) $value,
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group_name', $group);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index')->orderBy('setting_key');
    }
}
