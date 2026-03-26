@props([
    'name' => 'confirm-action',
    'title' => __('Confirmar Ação'),
    'content' => __('Tem certeza que deseja realizar esta operação?'),
    'confirm' => __('Confirmar'),
    'cancel' => __('Cancelar'),
    'type' => 'danger' // 'danger' or 'primary'
])

<x-modal :name="$name" :centered="true" focusable>
    <div x-data="{ 
            action: '', 
            title: '{{ $title }}', 
            content: '{{ $content }}',
            confirmText: '{{ $confirm }}',
            method: 'POST',
            type: '{{ $type }}'
         }" 
         x-on:open-confirm-modal.window="
            if($event.detail.name === '{{ $name }}') {
                action = $event.detail.action;
                title = $event.detail.title || title;
                content = $event.detail.content || content;
                confirmText = $event.detail.confirm || confirmText;
                method = $event.detail.method || 'POST';
                type = $event.detail.type || type;
                $dispatch('open-modal', '{{ $name }}');
            }
         "
         class="p-6">
        
        <form :action="action" method="POST">
            @csrf
            <template x-if="method !== 'POST'">
                <input type="hidden" name="_method" :value="method">
            </template>

            <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-900" x-text="title"></h2>
            </div>

            <p class="text-gray-600 mb-8" x-text="content"></p>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button" x-on:click="$dispatch('close')" class="px-6 py-2.5 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-50 transition">
                    {{ $cancel }}
                </button>

                <template x-if="type === 'danger'">
                    <button type="submit" class="px-6 py-2.5 bg-red-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-red-200 hover:bg-red-700 transition">
                        <span x-text="confirmText"></span>
                    </button>
                </template>

                <template x-if="type === 'primary'">
                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition">
                        <span x-text="confirmText"></span>
                    </button>
                </template>
            </div>
        </form>
    </div>
</x-modal>
