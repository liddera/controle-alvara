import './bootstrap';

import Alpine from 'alpinejs';
import searchableSelect from './searchable-select';
import cnpjLookup from './cnpj-lookup';

Alpine.data('searchableSelect', searchableSelect);
Alpine.data('cnpjLookup', cnpjLookup);

window.Alpine = Alpine;

Alpine.start();
