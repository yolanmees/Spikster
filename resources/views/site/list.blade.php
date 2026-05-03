@extends('layouts.app')

@section('title', 'Sites')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Sites">
            <x-slot name="actions">
                <x-primary-button @click="$dispatch('open-modal', 'new-site')">
                    <x-icon icon="plus" class="-ml-1 mr-2 h-5 w-5" />
                    New Site
                </x-primary-button>
            </x-slot>
        </x-page-header>

        {{-- Page-level flash messages (shown after Livewire events) --}}
        <div x-data="{
            messages: [],
            addMessage(type, text) {
                const id = Date.now() + Math.random();
                this.messages.push({ id, type, text });
                setTimeout(() => this.removeMessage(id), 8000);
            },
            removeMessage(id) {
                this.messages = this.messages.filter(m => m.id !== id);
            }
        }"
        x-on:flash-success.window="addMessage('success', $event.detail?.message || '')"
        x-on:flash-error.window="addMessage('error', $event.detail?.message || '')">
            <template x-for="msg in messages" :key="msg.id">
                <div x-show="true"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mb-4">
                    <div :class="msg.type === 'error'
                        ? 'bg-red-50 dark:bg-red-900/20 border-red-400 dark:border-red-600'
                        : 'bg-green-50 dark:bg-green-900/20 border-green-400 dark:border-green-600'"
                        class="flex items-start gap-3 rounded-lg border-l-4 p-4">
                        <svg x-show="msg.type === 'success'" class="h-5 w-5 shrink-0 mt-0.5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <svg x-show="msg.type === 'error'" class="h-5 w-5 shrink-0 mt-0.5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div class="flex-1">
                            <p :class="msg.type === 'error' ? 'text-red-800 dark:text-red-300' : 'text-green-800 dark:text-green-300'"
                                class="text-sm font-medium" x-text="msg.text"></p>
                        </div>
                        <button @click="removeMessage(msg.id)" type="button"
                            :class="msg.type === 'error' ? 'text-red-500 hover:text-red-700' : 'text-green-500 hover:text-green-700'"
                            class="shrink-0 transition-colors">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        @livewire('site.site-table')
    </div>

    <x-modal id="new-site" title="New Site" max-width="2xl">
        @livewire('site.new-site')
    </x-modal>
@endsection

@section('js')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('close-modal', () => window.dispatchEvent(new CustomEvent('close-modal')));
        Livewire.on('open-new-site-modal', () => window.dispatchEvent(new CustomEvent('open-modal', { detail: 'new-site' })));

        // Forward Livewire flash-* events to Alpine page-level toast
        Livewire.on('flash-error', (data) => {
            window.dispatchEvent(new CustomEvent('flash-error', {
                detail: { message: data.message || data[0] || 'An unknown error occurred.' }
            }));
        });
        Livewire.on('flash-success', (data) => {
            window.dispatchEvent(new CustomEvent('flash-success', {
                detail: { message: data.message || data[0] || 'Operation completed.' }
            }));
        });
    });
</script>
@endsection
