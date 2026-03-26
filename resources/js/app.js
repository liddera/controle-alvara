import './bootstrap';

import Alpine from 'alpinejs';
import searchableSelect from './searchable-select';
import cnpjLookup from './cnpj-lookup';
import alertRecipients from './alert-recipients';

Alpine.data('searchableSelect', searchableSelect);
Alpine.data('cnpjLookup', cnpjLookup);
Alpine.data('alertRecipients', alertRecipients);

window.Alpine = Alpine;

Alpine.start();
