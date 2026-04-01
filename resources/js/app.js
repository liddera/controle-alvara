import './bootstrap';

import Alpine from 'alpinejs';
import searchableSelect from './searchable-select';
import cnpjLookup from './cnpj-lookup';
import alertRecipients from './alert-recipients';
import alertPhones from './alert-phones';
import './whatsapp-connection';

Alpine.data('searchableSelect', searchableSelect);
Alpine.data('cnpjLookup', cnpjLookup);
Alpine.data('alertRecipients', alertRecipients);
Alpine.data('alertPhones', alertPhones);

window.Alpine = Alpine;

Alpine.start();
