@extends('layouts.app')

@section('title', 'Design System')

@section('content')
<div class="space-y-12 pb-16">

    {{-- Page Header --}}
    <x-page-header title="Design System" subtitle="Living styleguide — all available UI components at a glance.">
        <x-slot name="actions">
            <x-back-button href="/" />
        </x-slot>
    </x-page-header>

    {{-- =========================================================
         PAGE HEADER
    ========================================================= --}}
    <section>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Page Header</h2>
        <div class="border border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6">
            <x-page-header title="Example Page" subtitle="Subtitle text below the title.">
                <x-slot name="actions">
                    <x-primary-button>Action</x-primary-button>
                </x-slot>
            </x-page-header>
        </div>
    </section>

    {{-- =========================================================
         FLASH MESSAGES
    ========================================================= --}}
    <section>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Flash Messages</h2>
        <x-flash-messages />
    </section>

    {{-- =========================================================
         ALERTS
    ========================================================= --}}
    <section>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Alerts</h2>
        <div class="space-y-3">
            <x-alert type="info">This is an informational alert.</x-alert>
            <x-alert type="success">This is a success alert.</x-alert>
            <x-alert type="warning">This is a warning alert.</x-alert>
            <x-alert type="danger">This is a danger alert.</x-alert>
        </div>
    </section>

    {{-- =========================================================
         CARDS
    ========================================================= --}}
    <section>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Cards</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-card header="Default Card (md)">Card body content goes here.</x-card>
            <x-card header="Large Card" size="lg">Card body with size lg.</x-card>
            <x-card>Card without header.</x-card>
        </div>
    </section>

    {{-- =========================================================
         STAT CARDS
    ========================================================= --}}
    <section>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Stat Cards</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-stat-card label="Servers" value="12" icon="server" color="blue" />
            <x-stat-card label="Sites" value="48" icon="globe" color="green" />
            <x-stat-card label="Uptime" value="99.9%" icon="check" color="purple" />
            <x-stat-card label="Alerts" value="3" icon="bell" color="orange" />
        </div>
    </section>

    {{-- =========================================================
         INFO GRID
    ========================================================= --}}
    <section>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Info Grid</h2>
        <x-info-grid :items="[
            ['label' => 'Domain', 'value' => 'example.com', 'icon' => 'globe', 'color' => 'blue'],
            ['label' => 'Server', 'value' => 'prod-01', 'icon' => 'server', 'color' => 'purple'],
            ['label' => 'PHP', 'value' => '8.3', 'icon' => 'code', 'color' => 'green'],
            ['label' => 'Base Path', 'value' => '/home/user/web', 'icon' => 'folder', 'color' => 'orange'],
        ]" />
    </section>

    {{-- =========================================================
         BADGES
    ========================================================= --}}
    <section>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Badges</h2>
        <div class="flex flex-wrap gap-2">
            <x-badge color="green" text="Active" />
            <x-badge color="red" text="Inactive" />
            <x-badge color="yellow" text="Pending" />
            <x-badge color="blue" text="Info" />
            <x-badge color="purple" text="Catch-All" />
            <x-badge color="gray" text="Unknown" />
        </div>
    </section>

    {{-- =========================================================
         BUTTONS
    ========================================================= --}}
    <section>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Buttons</h2>
        <div class="space-y-4">
            <div class="flex flex-wrap gap-3">
                <x-primary-button>Primary</x-primary-button>
                <x-secondary-button>Secondary</x-secondary-button>
                <x-danger-button>Danger</x-danger-button>
                <x-back-button href="#" />
            </div>
            <div class="flex flex-wrap gap-3">
                <x-button variant="primary">x-button primary</x-button>
                <x-button variant="secondary">x-button secondary</x-button>
                <x-button variant="success">x-button success</x-button>
                <x-button variant="danger">x-button danger</x-button>
                <x-button variant="warning">x-button warning</x-button>
                <x-button variant="info">x-button info</x-button>
            </div>
        </div>
    </section>

    {{-- =========================================================
         FORM ELEMENTS
    ========================================================= --}}
    <section>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Form Elements</h2>
        <x-card>
            <div class="space-y-4 max-w-lg">
                <div>
                    <x-label for="demo_input" value="Text Input" />
                    <x-input id="demo_input" type="text" placeholder="Enter something..." class="mt-1 w-full" />
                </div>
                <div>
                    <x-label for="demo_select" value="Select" />
                    <x-select id="demo_select" class="mt-1 w-full">
                        <option>Option 1</option>
                        <option>Option 2</option>
                        <option>Option 3</option>
                    </x-select>
                </div>
                <div>
                    <x-search-input placeholder="Search..." />
                </div>
            </div>
        </x-card>
    </section>

    {{-- =========================================================
         EMPTY STATE
    ========================================================= --}}
    <section>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Empty State</h2>
        <x-card>
            <x-empty-state icon="server" title="No servers found" message="Add your first server to get started." />
        </x-card>
    </section>

    {{-- =========================================================
         TABLE WRAPPER
    ========================================================= --}}
    <section>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Table Wrapper</h2>
        <x-table-wrapper>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">example.com</td>
                        <td class="px-6 py-4"><x-badge color="green" text="Active" /></td>
                        <td class="px-6 py-4 text-right">
                            <x-secondary-button>Edit</x-secondary-button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">staging.example.com</td>
                        <td class="px-6 py-4"><x-badge color="yellow" text="Pending" /></td>
                        <td class="px-6 py-4 text-right">
                            <x-secondary-button>Edit</x-secondary-button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </x-table-wrapper>
    </section>

    {{-- =========================================================
         SPINNER
    ========================================================= --}}
    <section>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Spinners</h2>
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2">
                <x-spinner />
                <span class="text-sm text-gray-600 dark:text-gray-400">x-spinner</span>
            </div>
            <div class="flex items-center gap-2">
                <x-wire-spinner />
                <span class="text-sm text-gray-600 dark:text-gray-400">x-wire-spinner (always shows in design view)</span>
            </div>
        </div>
    </section>

    {{-- =========================================================
         ALPINE TABS
    ========================================================= --}}
    <section>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Alpine Tabs</h2>
        <div x-data="{ tab: 'one' }">
            <x-alpine-tabs model="tab" :tabs="[
                ['key' => 'one',   'label' => 'Tab One'],
                ['key' => 'two',   'label' => 'Tab Two'],
                ['key' => 'three', 'label' => 'Tab Three'],
            ]" />
            <div x-show="tab === 'one'" x-transition class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                Content for Tab One
            </div>
            <div x-show="tab === 'two'" x-transition class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg" style="display:none">
                Content for Tab Two
            </div>
            <div x-show="tab === 'three'" x-transition class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg" style="display:none">
                Content for Tab Three
            </div>
        </div>
    </section>

    {{-- =========================================================
         MODAL
    ========================================================= --}}
    <section>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Modal</h2>
        <x-primary-button @click="$dispatch('open-modal', 'demo-modal')">Open Demo Modal</x-primary-button>

        <x-modal id="demo-modal" title="Demo Modal" max-width="lg">
            <p class="text-sm text-gray-600 dark:text-gray-400">This is the modal body content. You can put any content here.</p>
            <x-slot name="footer">
                <x-secondary-button @click="open = false">Cancel</x-secondary-button>
                <x-primary-button @click="open = false">Confirm</x-primary-button>
            </x-slot>
        </x-modal>
    </section>

    {{-- =========================================================
         SORT BUTTON & SEARCH INPUT
    ========================================================= --}}
    <section>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Sort Button &amp; Search Input</h2>
        <div class="space-y-4">
            <div class="flex gap-3">
                <x-sort-button field="name" label="Name" sort-field="name" sort-direction="asc" />
                <x-sort-button field="date" label="Date" sort-field="name" sort-direction="asc" />
            </div>
            <x-search-input placeholder="Search items..." />
        </div>
    </section>

</div>
@endsection
