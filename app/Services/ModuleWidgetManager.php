<?php

namespace App\Services;

use App\Models\Module;
use App\Models\ModuleWidget;
use Illuminate\Support\Collection;

class ModuleWidgetManager
{
    public function getActiveWidgets(bool $checkPermissions = true): Collection
    {
        $query = ModuleWidget::query()
            ->whereHas('module', function ($q) {
                $q->where('is_active', true);
            })
            ->active()
            ->ordered();

        $widgets = $query->get();

        if ($checkPermissions) {
            $widgets = $widgets->filter(fn ($widget) => $widget->hasPermission());
        }

        return $widgets;
    }

    public function renderWidget(ModuleWidget $widget): string
    {
        if (!class_exists($widget->component_class)) {
            return "<div class='text-red-500'>Widget component not found: {$widget->component_class}</div>";
        }

        try {
            $component = app($widget->component_class);
            
            if (method_exists($component, 'render')) {
                return $component->render()->render();
            }
            
            return (string) $component;
        } catch (\Exception $e) {
            return "<div class='text-red-500'>Error rendering widget: {$e->getMessage()}</div>";
        }
    }

    public function registerWidget(Module $module, array $data): ModuleWidget
    {
        return ModuleWidget::create([
            'module_id' => $module->id,
            'widget_key' => $data['key'],
            'widget_name' => $data['name'],
            'component_class' => $data['component'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'width' => $data['width'] ?? 'half',
            'height' => $data['height'] ?? 'medium',
            'order_index' => $data['order'] ?? 0,
            'permission' => $data['permission'] ?? null,
            'config' => $data['config'] ?? null,
        ]);
    }

    public function unregisterWidgets(Module $module): void
    {
        ModuleWidget::where('module_id', $module->id)->delete();
    }

    public function toggleWidget(ModuleWidget $widget): bool
    {
        $widget->update(['is_active' => !$widget->is_active]);
        return $widget->is_active;
    }

    public function getWidgetGrid(Collection $widgets): array
    {
        return $widgets->map(function ($widget) {
            return [
                'id' => $widget->id,
                'key' => $widget->widget_key,
                'title' => $widget->title,
                'description' => $widget->description,
                'icon' => $widget->icon,
                'width' => $widget->width,
                'height' => $widget->height,
                'widthClass' => $widget->getWidthClass(),
                'heightClass' => $widget->getHeightClass(),
                'component' => $widget->component_class,
                'config' => $widget->config,
            ];
        })->toArray();
    }

    public function getWidthOptions(): array
    {
        return [
            'quarter' => '1/4 Width (3 columns)',
            'half' => '1/2 Width (6 columns)',
            'three-quarter' => '3/4 Width (9 columns)',
            'full' => 'Full Width (12 columns)',
        ];
    }

    public function getHeightOptions(): array
    {
        return [
            'small' => 'Small (12rem)',
            'medium' => 'Medium (16rem)',
            'large' => 'Large (24rem)',
            'auto' => 'Auto Height',
        ];
    }
}
