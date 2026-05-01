<div class="space-y-6">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-6">
        @livewire('site.deployment-history', ['siteId' => $site_id], key($site_id))
    </div>
</div>
