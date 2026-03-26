/**
 * Alpine.js component for a searchable select dropdown.
 * Registered as Alpine.data('searchableSelect', ...) in app.js.
 */
export default function searchableSelect(config) {
    return {
        open: false,
        search: config.initialSearch || '',
        id: config.value || '',
        items: config.options || [],
        dropdownStyle: {},

        get filteredItems() {
            if (!this.search) return this.items;
            // Show all when a selection is already made and user hasn't changed the text yet
            const lower = this.search.toLowerCase();
            return this.items.filter(i => i[config.labelField].toLowerCase().includes(lower));
        },

        select(item) {
            this.search = item[config.labelField];
            this.id = item[config.idField];
            this.open = false;
        },

        clear() {
            this.search = '';
            this.id = '';
            this.$refs.input.focus();
        },

        openDropdown() {
            const rect = this.$refs.inputWrapper.getBoundingClientRect();
            this.dropdownStyle = {
                position: 'fixed',
                top: (rect.bottom + 4) + 'px',
                left: rect.left + 'px',
                width: rect.width + 'px',
                zIndex: 9999,
            };
            this.open = true;
        },
    };
}
