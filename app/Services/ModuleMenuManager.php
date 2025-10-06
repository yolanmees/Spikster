<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleMenuItem;
use Illuminate\Support\Collection;

class ModuleMenuManager
{
    public function getMenuItems(string $location = 'main', bool $checkPermissions = true): Collection
    {
        $query = ModuleMenuItem::query()
            ->whereHas('module', function ($q) {
                $q->where('is_active', true);
            })
            ->active()
            ->byLocation($location)
            ->rootItems()
            ->withChildren()
            ->ordered();

        $items = $query->get();

        if ($checkPermissions) {
            $items = $items->filter(fn ($item) => $item->hasPermission());
            
            // Filter children by permission
            $items->each(function ($item) {
                if ($item->children) {
                    $item->setRelation(
                        'children',
                        $item->children->filter(fn ($child) => $child->hasPermission())
                    );
                }
            });
        }

        return $items;
    }

    public function buildTree(Collection $items): array
    {
        return $items->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'url' => $item->getUrl(),
                'icon' => $item->icon,
                'icon_type' => $item->icon_type,
                'badge' => $this->getBadge($item),
                'target' => $item->target,
                'is_external' => $item->is_external,
                'children' => $item->children ? $this->buildTree($item->children) : [],
            ];
        })->toArray();
    }

    public function getBadge(ModuleMenuItem $item): ?array
    {
        if ($item->badge_type === 'none') {
            return null;
        }

        $value = $item->getBadgeValue();

        if ($value === null) {
            return null;
        }

        return [
            'type' => $item->badge_type,
            'value' => $value,
            'color' => $item->badge_color ?? 'blue',
        ];
    }

    public function registerMenuItem(Module $module, array $data): ModuleMenuItem
    {
        // Find parent if specified
        if (isset($data['parent_title'])) {
            $parent = ModuleMenuItem::where('module_id', $module->id)
                ->where('title', $data['parent_title'])
                ->first();
            
            $data['parent_id'] = $parent?->id;
            unset($data['parent_title']);
        }

        return ModuleMenuItem::create([
            'module_id' => $module->id,
            ...$data,
        ]);
    }

    public function unregisterMenuItems(Module $module): void
    {
        ModuleMenuItem::where('module_id', $module->id)->delete();
    }

    public function getMenuLocations(): array
    {
        return [
            'main' => 'Main Navigation',
            'admin' => 'Admin Panel',
            'user' => 'User Dropdown',
            'footer' => 'Footer',
        ];
    }

    public function getBadgeTypes(): array
    {
        return [
            'none' => 'No Badge',
            'count' => 'Count Badge',
            'text' => 'Text Badge',
            'dot' => 'Dot Badge',
        ];
    }

    public function getIconTypes(): array
    {
        return [
            'heroicon' => 'Heroicon',
            'fontawesome' => 'Font Awesome',
            'custom' => 'Custom Icon',
        ];
    }
}
