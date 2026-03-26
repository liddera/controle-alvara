@props([
    'name',
    'value' => null,
    'options' => [],
    'placeholder' => 'Selecione...',
    'initialSearch' => '',
    'idField' => 'id',
    'labelField' => 'nome'
])

<div x-data="searchableSelect({
    options: {{ json_encode($options) }},
    value: '{{ $value }}',
    initialSearch: '{{ $initialSearch }}',
    idField: '{{ $idField }}',
    labelField: '{{ $labelField }}'
})" class="relative w-full">

    <div class="relative" x-ref="inputWrapper">
        <input
            x-ref="input"
            type="text"
            x-model="search"
            @focus="openDropdown()"
            @click.away="open = false"
            @keydown.escape="open = false"
            @input="id = ''; openDropdown()"
            placeholder="{{ $placeholder }}"
            class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 pr-10 cursor-pointer transition-all"
            autocomplete="off"
        >

        <!-- Chevron (sempre visível, pointer-events-none) -->
        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
            <svg class="w-4 h-4 text-gray-400" :class="open ? 'rotate-180' : ''" style="transition: transform 0.15s"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>

        <!-- Botão limpar (substitui o chevron quando há texto) -->
        <button type="button" x-show="search" @click="clear(); $event.stopPropagation()"
                class="absolute inset-y-0 right-0 flex items-center pr-2 text-gray-400 hover:text-gray-600 focus:outline-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Dropdown (fixed, escapa de overflow:hidden) -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         :style="dropdownStyle"
         class="bg-white border border-gray-300 rounded-md shadow-xl max-h-60 overflow-auto"
         x-cloak>

        <template x-for="item in filteredItems" :key="item['{{ $idField }}']">
            <div @click="select(item)"
                 class="px-4 py-3 cursor-pointer hover:bg-blue-50 hover:text-blue-700 text-sm transition-colors border-b border-gray-50 last:border-b-0 flex justify-between items-center"
                 :class="id == item['{{ $idField }}'] ? 'bg-blue-50 text-blue-700 font-bold' : 'text-gray-700'">
                <span x-text="item['{{ $labelField }}']"></span>
                <svg x-show="id == item['{{ $idField }}']" class="w-4 h-4 text-blue-600 shrink-0"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </template>

        <div x-show="filteredItems.length === 0" class="px-4 py-4 text-sm text-gray-500 italic text-center bg-gray-50">
            Nenhum resultado para "<span x-text="search" class="font-bold"></span>"
        </div>
    </div>

    <input type="hidden" name="{{ $name }}" x-model="id">
</div>
