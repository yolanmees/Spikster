<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    <x-card size="md" dark="false">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <x-icon icon="server" class="h-5 w-5 text-zinc-700 dark:text-zinc-300" />
                Git Repository
            </div>
        </x-slot>
        <div class="space-y-4">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Configure your repository and deployment scripts.</p>
            <div class="grid grid-cols-1 gap-2">
                <button type="button" id="sitesetrepo"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                    Repository Settings
                </button>
                <button type="button" id="editdeploy"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                    Deployment Script
                </button>
            </div>
            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <p class="mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Deploy command:</p>
                <div class="p-3 bg-zinc-950 dark:bg-zinc-950 border border-zinc-700 rounded-lg">
                    <code class="font-mono text-xs text-green-400">
                        ssh <span id="repodeployinfouser1"></span>@<span id="repodeployinfoip"></span><br />
                        sh /home/<span id="repodeployinfouser2"></span>/git/deploy.sh
                    </code>
                </div>
            </div>
        </div>
    </x-card>

    <x-site-management-card
        title="WordPress Manager"
        subtitle="Application"
        description="Manage themes, plugins, updates, and WordPress maintenance tasks."
        status="Optional"
        statusClass="bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
        :href="route('site.wordpress', $site_id)"
        buttonLabel="Open WordPress"
    >
        <x-slot name="icon">
            <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12.158 12.786l-2.698 7.84c.806.236 1.657.365 2.54.365 1.047 0 2.051-.18 2.986-.51-.024-.037-.046-.078-.065-.123l-2.763-7.572zm-5.203-2.62l3.246 8.9c-1.837-.87-3.288-2.43-4.093-4.315l.847-4.585zm11.68 1.17c0-.972-.349-1.646-.648-2.168-.399-.648-.772-1.197-.772-1.845 0-.723.548-1.395 1.32-1.395.035 0 .068.004.102.006C17.157 4.368 14.754 3.5 12 3.5c-3.399 0-6.39 1.742-8.128 4.382.228.007.443.011.623.011 1.013 0 2.582-.123 2.582-.123.522-.03.583.736.061.798 0 0-.525.062-1.109.092l3.53 10.5 2.119-6.357-1.508-4.143c-.522-.03-1.016-.092-1.016-.092-.522-.03-.461-.828.061-.798 0 0 1.601.123 2.552.123.987 0 2.582-.123 2.582-.123.522-.03.583.736.061.798 0 0-.525.062-1.109.092l3.506 10.426 1.294-4.32c.56-1.795.987-3.086.987-4.197zm.664-4.827c.034.251.053.52.053.81 0 .797-.149 1.692-.597 2.815L16.03 16.76c1.863-1.084 3.113-3.108 3.113-5.425 0-1.069-.268-2.073-.744-2.952l-.1.125z" />
            </svg>
        </x-slot>
    </x-site-management-card>
</div>
