<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    <x-card size="md" dark="false">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <x-icon icon="info" class="h-5 w-5 text-purple-700 dark:text-purple-400" />
                Basic Information
            </div>
        </x-slot>
        <div class="space-y-4">
            <div>
                <label class="block mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Domain:</label>
                <input type="text" placeholder="e.g. domain.tld" id="sitedomain" autocomplete="off"
                    class="w-full px-4 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white focus:ring-2 focus:ring-purple-700 focus:border-transparent transition-all" />
            </div>
            <div>
                <label class="block mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Base path:</label>
                <input type="text" placeholder="e.g. public" id="sitebasepath" autocomplete="off"
                    class="w-full px-4 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white focus:ring-2 focus:ring-purple-700 focus:border-transparent transition-all" />
            </div>
            <button type="button" id="updateSite"
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all duration-200">
                Update Site
            </button>
        </div>
    </x-card>

    <x-card size="md" dark="false">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <x-icon icon="plus" class="h-5 w-5 text-purple-700 dark:text-purple-400" />
                Aliases
            </div>
        </x-slot>
        <div class="space-y-4">
            <div>
                <p class="mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Add alias:</p>
                <div class="flex gap-2">
                    <input type="text" placeholder="e.g. www.domain.tld" id="siteaddalias" autocomplete="off"
                        class="flex-1 px-4 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white focus:ring-2 focus:ring-purple-700 focus:border-transparent transition-all" />
                    <button type="button" id="siteaddaliassubmit"
                        class="px-4 py-2.5 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 text-white rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                        <x-icon icon="plus" class="h-5 w-5" />
                    </button>
                </div>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Current aliases:</p>
                <div id="sitealiaseslist" class="flex flex-wrap gap-2"></div>
            </div>
        </div>
    </x-card>

    <x-card size="md" dark="false" class="xl:col-span-2">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <x-icon icon="cog" class="h-5 w-5 text-purple-700 dark:text-purple-400" />
                Runtime Tools
            </div>
        </x-slot>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">PHP-FPM version:</p>
                <div class="flex gap-2">
                    <select id="sitephpver"
                        class="flex-1 px-4 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white focus:ring-2 focus:ring-purple-700 focus:border-transparent transition-all">
                        <option value="8.3" id="php83">8.3</option>
                        <option value="8.2" id="php82">8.2</option>
                        <option value="8.1" id="php81">8.1</option>
                        <option value="8.0" id="php80">8.0</option>
                        <option value="7.4" id="php74">7.4</option>
                    </select>
                    <button type="button" id="sitephpversubmit"
                        class="px-4 py-2.5 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 text-white rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                        Save
                    </button>
                </div>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">Supervisor script:</p>
                <input type="text" id="sitesupervisor" autocomplete="off"
                    class="w-full px-4 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white focus:ring-2 focus:ring-purple-700 focus:border-transparent transition-all mb-3" />
                <button type="button" id="sitesupervisorupdate"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                    Update Supervisor
                </button>
            </div>
        </div>
    </x-card>

    {{-- Nginx Config Card --}}
    <x-card size="md" dark="false" class="xl:col-span-2">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <x-icon icon="server" class="h-5 w-5 text-zinc-700 dark:text-zinc-300" />
                Custom Nginx Config
            </div>
        </x-slot>
        <div class="space-y-4">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                Custom nginx rules are included in the site's vhost. Changes are syntax-checked before reload.
            </p>
            <div id="nginxeditor" style="height:250px;width:100%;border-radius:0.5rem;overflow:hidden;border:1px solid #e4e4e7;"></div>
            <button type="button" id="sitenginxsubmit"
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                Update Nginx Config
            </button>
        </div>
    </x-card>

    {{-- PHP Settings Card --}}
    <x-card size="md" dark="false" class="xl:col-span-2">
        <x-slot name="header">
            <div class="flex items-center gap-2">
                <x-icon icon="cog" class="h-5 w-5 text-purple-700 dark:text-purple-400" />
                PHP Settings
            </div>
        </x-slot>
        <div class="space-y-4">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Override PHP ini values for this site. Leave empty to use defaults.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Memory Limit</label>
                    <input type="text" id="phpmemorylimit" placeholder="256M"
                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white font-mono text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Upload Max Size</label>
                    <input type="text" id="phpuploadmaxsize" placeholder="256M"
                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white font-mono text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Post Max Size</label>
                    <input type="text" id="phppostmaxsize" placeholder="256M"
                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white font-mono text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Max Execution Time (s)</label>
                    <input type="number" id="phpmaxexectime" placeholder="300"
                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white font-mono text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Max Input Vars</label>
                    <input type="number" id="phpmaxinputvars" placeholder="3000"
                        class="w-full px-4 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white font-mono text-sm" />
                </div>
            </div>
            <button type="button" id="sitephpsettings"
                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:text-zinc-950 dark:hover:bg-zinc-100 text-white font-semibold rounded-lg transform hover:scale-105 active:scale-95 transition-all">
                Update PHP Settings
            </button>
        </div>
    </x-card>
</div>
