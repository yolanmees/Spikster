<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleHook extends Model
{
    use HasUuids;

    protected $fillable = [
        'module_id',
        'hook_name',
        'hook_type',
        'callback_class',
        'callback_method',
        'priority',
        'accepted_args',
        'is_active',
        'description',
    ];

    protected $casts = [
        'priority' => 'integer',
        'accepted_args' => 'integer',
        'is_active' => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function execute(...$args)
    {
        if (! $this->is_active) {
            return $this->hook_type === 'filter' ? ($args[0] ?? null) : null;
        }

        $class = $this->callback_class;
        $method = $this->callback_method;

        if (! class_exists($class) || ! method_exists($class, $method)) {
            return $this->hook_type === 'filter' ? ($args[0] ?? null) : null;
        }

        $instance = app($class);
        $result = $instance->$method(...array_slice($args, 0, $this->accepted_args));

        return $result;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByHook($query, string $hookName)
    {
        return $query->where('hook_name', $hookName);
    }

    public function scopeActions($query)
    {
        return $query->where('hook_type', 'action');
    }

    public function scopeFilters($query)
    {
        return $query->where('hook_type', 'filter');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('priority')->orderBy('id');
    }
}
