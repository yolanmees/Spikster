@extends('layouts.app')

@section('title')
    File Manager
@endsection

@section('topbar-title')
    File Manager
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.css">
<style>
    #context-menu, #context-folder-menu {
        @apply fixed z-50 w-48 rounded-xl border border-zinc-200 bg-white py-1 shadow-soft dark:border-zinc-700 dark:bg-zinc-900;
        transform-origin: top left;
    }
    #context-menu .item, #context-folder-menu .item {
        @apply flex w-full items-center gap-2.5 px-4 py-2.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-800 cursor-pointer transition-colors;
    }
    #context-menu .item.border-bottom, #context-folder-menu .item.border-bottom {
        @apply border-b border-zinc-100 dark:border-zinc-800;
    }
    td a { @apply block w-full h-full; }
</style>
@endpush

@section('content')
<div x-data="fileManager()" x-init="init()" class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">File Manager</h2>
            <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Browse, edit and manage server files</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="openModal('createFile')" class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 transition-colors">
                <i data-lucide="file-plus" class="h-4 w-4"></i>
                New File
            </button>
            <button @click="openModal('createDir')" class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800 transition-colors">
                <i data-lucide="folder-plus" class="h-4 w-4"></i>
                New Folder
            </button>
        </div>
    </div>

    {{-- Breadcrumbs --}}
    <nav class="flex items-center gap-1.5 text-sm">
        <a href="{{ url()->to('/files') }}" class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 font-medium text-purple-700 hover:bg-purple-50 dark:text-purple-400 dark:hover:bg-purple-700/10 transition-colors">
            <i data-lucide="home" class="h-3.5 w-3.5"></i>
            Files
        </a>
        @if (!is_null($headers))
            @foreach ($headers as $i => $header)
                <i data-lucide="chevron-right" class="h-3.5 w-3.5 shrink-0 text-zinc-400"></i>
                @if (Str::afterLast($queryPath, '/') == $header)
                    <span class="rounded-lg px-2.5 py-1.5 font-medium text-zinc-900 dark:text-white">{{ $header }}</span>
                @else
                    <a href="{{ url()->to('/files?' . implode('/', array_slice($headers, 0, $i + 1))) }}" class="rounded-lg px-2.5 py-1.5 font-medium text-zinc-500 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800 transition-colors">{{ $header }}</a>
                @endif
            @endforeach
        @endif
    </nav>

    {{-- Flash Messages --}}
    @if (Session::has('success'))
        <div class="flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-700/30 dark:bg-emerald-900/20">
            <i data-lucide="check-circle-2" class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400"></i>
            <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ Session::get('success') }}</p>
        </div>
    @endif

    {{-- File Table --}}
    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-center justify-between gap-3 border-b border-zinc-200 px-5 py-3 dark:border-zinc-800">
            <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                <i data-lucide="folder" class="mr-1.5 inline h-4 w-4 text-zinc-400"></i>
                {{ $params ?: '/' }}
            </p>
            <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-600 hover:bg-zinc-50 dark:border-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-800 transition-colors">
                <i data-lucide="upload" class="h-3.5 w-3.5"></i>
                Upload
                <input type="file" name="upload" class="hidden">
            </label>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Size</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Last modified</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @foreach ($pathContents as $content)
                        @if ($content['type'] === 'file')
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors" @contextmenu.prevent="showContextMenu($event, 'file', {{ json_encode($content) }})">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                            <i data-lucide="file" class="h-4 w-4"></i>
                                        </span>
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $content['filename'] }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-zinc-500 dark:text-zinc-400">{{ number_format($content['size'] / 1000) }} KB</td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-zinc-500 dark:text-zinc-400">{{ $content['last_modified'] }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <button @click="showContextMenu($event, 'file', {{ json_encode($content) }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300 transition-colors">
                                        <i data-lucide="ellipsis-vertical" class="h-4 w-4"></i>
                                    </button>
                                </td>
                            </tr>
                        @else
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors" @contextmenu.prevent="showContextMenu($event, 'folder', {{ json_encode($content) }})">
                                <td class="px-5 py-3.5">
                                    <a href="{{ url()->current() }}?{{$queryPath}}/{{ $content['folder_name'] }}" class="flex items-center gap-3">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 text-purple-700 dark:bg-purple-700/15 dark:text-purple-300">
                                            <i data-lucide="folder" class="h-4 w-4"></i>
                                        </span>
                                        <span class="font-medium text-zinc-900 hover:text-purple-700 dark:text-white dark:hover:text-purple-400 transition-colors">{{ $content['folder_name'] }}</span>
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-zinc-400">&mdash;</td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-zinc-400">&mdash;</td>
                                <td class="px-5 py-3.5 text-right">
                                    <button @click="showContextMenu($event, 'folder', {{ json_encode($content) }})" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300 transition-colors">
                                        <i data-lucide="ellipsis-vertical" class="h-4 w-4"></i>
                                    </button>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- File Context Menu --}}
    <div id="context-menu" x-show="contextMenu" x-cloak @click.outside="contextMenu = false" @click="contextMenu = false">
        <button class="item" @click="downloadFile()"><i data-lucide="download" class="h-4 w-4 text-zinc-400"></i>Download</button>
        <button class="item" @click="viewFile()"><i data-lucide="eye" class="h-4 w-4 text-zinc-400"></i>View</button>
        <button class="item border-bottom" @click="editFile()"><i data-lucide="pencil" class="h-4 w-4 text-zinc-400"></i>Edit</button>
        <button class="item" @click="openModal('move')"><i data-lucide="move" class="h-4 w-4 text-zinc-400"></i>Move</button>
        <button class="item border-bottom" @click="openModal('copy')"><i data-lucide="copy" class="h-4 w-4 text-zinc-400"></i>Copy</button>
        <button class="item" @click="openModal('rename')"><i data-lucide="pencil-line" class="h-4 w-4 text-zinc-400"></i>Rename</button>
        <button class="item text-red-600 hover:!text-red-700 hover:!bg-red-50 dark:text-red-400 dark:hover:!bg-red-900/20" @click="deleteFile()"><i data-lucide="trash-2" class="h-4 w-4 text-red-400"></i>Delete</button>
    </div>

    {{-- Folder Context Menu --}}
    <div id="context-folder-menu" x-show="folderContextMenu" x-cloak @click.outside="folderContextMenu = false" @click="folderContextMenu = false">
        <button class="item" @click="openModal('rename')"><i data-lucide="pencil-line" class="h-4 w-4 text-zinc-400"></i>Rename</button>
    </div>

    {{-- View Modal --}}
    <div x-show="modals.view" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center bg-zinc-950/45 px-4 backdrop-blur-sm" @click.self="modals.view = false">
        <div class="w-full max-w-3xl rounded-xl border border-zinc-200 bg-white shadow-soft dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 p-5 dark:border-zinc-800">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-white">View Content</h2>
                <button @click="modals.view = false" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
            <div class="max-h-[70vh] overflow-auto p-5">
                <pre class="overflow-auto rounded-lg bg-zinc-50 p-4 font-mono text-sm leading-6 text-zinc-700 dark:bg-zinc-950 dark:text-zinc-300" id="view-content" x-text="viewContent"></pre>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-show="modals.edit" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center bg-zinc-950/45 px-4 backdrop-blur-sm" @click.self="modals.edit = false">
        <div class="w-full max-w-4xl rounded-xl border border-zinc-200 bg-white shadow-soft dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 p-5 dark:border-zinc-800">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Edit Content</h2>
                <button @click="modals.edit = false" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
            <form id="edit-form" action="{{ route('files.store') }}" method="post">
                @csrf
                <div class="max-h-[70vh] overflow-auto p-5">
                    <div id="editor-div" class="min-h-[400px] rounded-lg border border-zinc-200 dark:border-zinc-800"></div>
                    <input type="hidden" name="content" id="edit-content-input">
                    <input type="hidden" name="data" id="edit-data-input">
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-zinc-200 p-5 dark:border-zinc-800">
                    <button type="button" @click="modals.edit = false" class="inline-flex items-center justify-center rounded-lg border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-800 transition-colors">Cancel</button>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-purple-700 px-4 py-2 text-sm font-medium text-white hover:bg-purple-800 transition-colors">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Create Directory Modal --}}
    <div x-show="modals.createDir" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center bg-zinc-950/45 px-4 backdrop-blur-sm" @click.self="modals.createDir = false">
        <div class="w-full max-w-lg rounded-xl border border-zinc-200 bg-white shadow-soft dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 p-5 dark:border-zinc-800">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Create New Directory</h2>
                <button @click="modals.createDir = false" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
            <form action="{{ route('files.create.directory') }}" method="post" class="p-5">
                @csrf
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Directory name</label>
                        <input type="text" name="new-directory-name" id="createDirId" required
                            class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                            placeholder="my-folder">
                    </div>
                    <input type="hidden" name="path" value="{{ str_replace('\\', '~', $path . '\\' . $params) }}">
                    <button type="submit" id="dirSubmit" disabled
                        class="inline-flex items-center gap-2 rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-purple-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i data-lucide="check" class="h-4 w-4"></i>
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Create File Modal --}}
    <div x-show="modals.createFile" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center bg-zinc-950/45 px-4 backdrop-blur-sm" @click.self="modals.createFile = false">
        <div class="w-full max-w-lg rounded-xl border border-zinc-200 bg-white shadow-soft dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 p-5 dark:border-zinc-800">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Create New File</h2>
                <button @click="modals.createFile = false" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
            <form action="{{ route('files.create.file') }}" method="post" class="p-5">
                @csrf
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">File name</label>
                        <input type="text" name="new-file-name" id="createFileId" required
                            class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 font-mono text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white"
                            placeholder="e.g. index.php, file.txt">
                    </div>
                    <input type="hidden" name="path" value="{{ str_replace('\\', '~', $path . '\\' . $params) }}">
                    <button type="submit" id="fileSubmit" disabled
                        class="inline-flex items-center gap-2 rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-purple-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i data-lucide="check" class="h-4 w-4"></i>
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Rename Modal --}}
    <div x-show="modals.rename" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center bg-zinc-950/45 px-4 backdrop-blur-sm" @click.self="modals.rename = false">
        <div class="w-full max-w-lg rounded-xl border border-zinc-200 bg-white shadow-soft dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 p-5 dark:border-zinc-800">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Rename File</h2>
                <button @click="modals.rename = false" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
            <form action="{{ route('files.rename.file') }}" method="post" class="p-5">
                @csrf
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">New name</label>
                        <input type="text" name="rename-file-name" id="renameFileId" required
                            class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 font-mono text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                    </div>
                    <input type="hidden" name="content" id="rename-content-input">
                    <button type="submit" id="renameSubmit" disabled
                        class="inline-flex items-center gap-2 rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-purple-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i data-lucide="check" class="h-4 w-4"></i>
                        Rename
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Copy Modal --}}
    <div x-show="modals.copy" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center bg-zinc-950/45 px-4 backdrop-blur-sm" @click.self="modals.copy = false">
        <div class="w-full max-w-lg rounded-xl border border-zinc-200 bg-white shadow-soft dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 p-5 dark:border-zinc-800">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Copy File</h2>
                <button @click="modals.copy = false" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
            <form action="{{ route('files.copy') }}" method="post" class="p-5">
                @csrf
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Destination path</label>
                        <input type="text" name="copy-file-path" id="copyFilePathId" required
                            class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 font-mono text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                    </div>
                    <input type="hidden" name="content" id="copy-content-input">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-purple-800 transition-colors">
                        <i data-lucide="copy" class="h-4 w-4"></i>
                        Copy
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Move Modal --}}
    <div x-show="modals.move" x-cloak class="fixed inset-0 z-[80] flex items-center justify-center bg-zinc-950/45 px-4 backdrop-blur-sm" @click.self="modals.move = false">
        <div class="w-full max-w-lg rounded-xl border border-zinc-200 bg-white shadow-soft dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-200 p-5 dark:border-zinc-800">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-white">Move File</h2>
                <button @click="modals.move = false" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-300">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
            <form action="{{ route('files.move') }}" method="post" class="p-5">
                @csrf
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Destination path</label>
                        <input type="text" name="move-file-path" id="moveFilePathId" required
                            class="h-10 w-full rounded-lg border border-zinc-200 bg-white px-3 font-mono text-sm outline-none focus:border-purple-700 focus:ring-2 focus:ring-purple-700/20 dark:border-zinc-800 dark:bg-zinc-950 dark:text-white">
                    </div>
                    <input type="hidden" name="content" id="move-content-input">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-purple-700 px-4 py-2.5 text-sm font-medium text-white hover:bg-purple-800 transition-colors">
                        <i data-lucide="move" class="h-4 w-4"></i>
                        Move
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function fileManager() {
        return {
            contextMenu: false,
            folderContextMenu: false,
            selectedContent: null,
            viewContent: '',
            editor: null,
            modals: {
                view: false,
                edit: false,
                createDir: false,
                createFile: false,
                rename: false,
                copy: false,
                move: false,
            },
            init() {
                this.$watch('modals.edit', val => {
                    if (val) {
                        this.$nextTick(() => {
                            if (!this.editor) {
                                this.editor = ace.edit('editor-div');
                                this.editor.setTheme('ace/theme/tomorrow');
                                this.editor.session.setMode('ace/mode/php');
                                this.editor.setOptions({
                                    fontSize: '13px',
                                    fontFamily: '"JetBrains Mono", "Fira Code", monospace',
                                    showPrintMargin: false,
                                    minLines: 25,
                                    maxLines: 50,
                                });
                            }
                        });
                    }
                });
            },
            showContextMenu(event, type, content) {
                this.selectedContent = content;
                if (type === 'file') {
                    this.contextMenu = true;
                    this.folderContextMenu = false;
                } else {
                    this.folderContextMenu = true;
                    this.contextMenu = false;
                }
                const menu = document.getElementById(type === 'file' ? 'context-menu' : 'context-folder-menu');
                if (menu) {
                    menu.style.left = event.clientX + 'px';
                    menu.style.top = event.clientY + 'px';
                }
            },
            openModal(name) {
                this.contextMenu = false;
                this.folderContextMenu = false;
                this.modals[name] = true;
                if (name === 'rename' && this.selectedContent) {
                    this.$nextTick(() => {
                        document.getElementById('renameFileId').value = this.selectedContent.filename || '';
                        document.getElementById('rename-content-input').value = JSON.stringify(this.selectedContent);
                    });
                }
                if (name === 'copy' && this.selectedContent) {
                    this.$nextTick(() => {
                        const path = this.selectedContent.pathName || '';
                        document.getElementById('copyFilePathId').value = path.substring(0, path.lastIndexOf('/'));
                        document.getElementById('copy-content-input').value = JSON.stringify(this.selectedContent);
                    });
                }
                if (name === 'move' && this.selectedContent) {
                    this.$nextTick(() => {
                        const path = this.selectedContent.pathName || '';
                        document.getElementById('moveFilePathId').value = path.substring(0, path.lastIndexOf('/'));
                        document.getElementById('move-content-input').value = JSON.stringify(this.selectedContent);
                    });
                }
            },
            viewFile() {
                this.contextMenu = false;
                if (!this.selectedContent) return;
                $.ajax({
                    url: '{{ route('files.show') }}',
                    type: 'POST',
                    data: JSON.stringify(this.selectedContent),
                    success: (data) => {
                        if (typeof data === 'object' && data.nonmedia) {
                            this.viewContent = data.nonmedia;
                            this.modals.view = true;
                        } else {
                            window.open(data, '_blank');
                        }
                    }
                });
            },
            editFile() {
                this.contextMenu = false;
                if (!this.selectedContent) return;
                $.ajax({
                    url: '{{ route('files.edit') }}',
                    type: 'POST',
                    data: JSON.stringify(this.selectedContent),
                    success: (data) => {
                        this.modals.edit = true;
                        this.$nextTick(() => {
                            if (this.editor) {
                                this.editor.setValue(data || '', -1);
                            }
                            const form = document.getElementById('edit-form');
                            const contentInput = document.getElementById('edit-content-input');
                            const dataInput = document.getElementById('edit-data-input');
                            form.onsubmit = () => {
                                contentInput.value = JSON.stringify(this.selectedContent);
                                dataInput.value = this.editor ? this.editor.getValue() : '';
                            };
                        });
                    }
                });
            },
            downloadFile() {
                this.contextMenu = false;
                if (!this.selectedContent) return;
                $.ajax({
                    url: '{{ route('files.download') }}',
                    type: 'POST',
                    data: JSON.stringify(this.selectedContent),
                    success: (data) => window.open(data, '_blank')
                });
            },
            deleteFile() {
                this.contextMenu = false;
                if (!this.selectedContent) return;
                if (!confirm('Delete this file?')) return;
                $.ajax({
                    url: '{{ route('files.delete') }}',
                    type: 'POST',
                    data: JSON.stringify(this.selectedContent),
                    success: () => window.location.reload()
                });
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const dirInput = document.getElementById('createDirId');
        const fileInput = document.getElementById('createFileId');
        const renameInput = document.getElementById('renameFileId');
        const dirSubmit = document.getElementById('dirSubmit');
        const fileSubmit = document.getElementById('fileSubmit');
        const renameSubmit = document.getElementById('renameSubmit');

        function toggleButton(input, btn) {
            if (!input || !btn) return;
            input.addEventListener('input', () => {
                btn.disabled = input.value.length < 2;
            });
        }
        toggleButton(dirInput, dirSubmit);
        toggleButton(fileInput, fileSubmit);
        toggleButton(renameInput, renameSubmit);
    });
</script>
@endpush
