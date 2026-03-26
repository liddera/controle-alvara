/**
 * Alpine.js component for CNPJ lookup and auto-fill.
 * Uses https://open.cnpja.com to fetch company data.
 */
export default function cnpjLookup() {
    return {
        cnpj: '',
        loading: false,
        error: '',
        found: false,

        // Format CNPJ as user types (XX.XXX.XXX/XXXX-XX)
        formatCnpj(value) {
            const digits = value.replace(/\D/g, '').slice(0, 14);
            this.cnpj = digits
                .replace(/^(\d{2})(\d)/, '$1.$2')
                .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                .replace(/\.(\d{3})(\d)/, '.$1/$2')
                .replace(/(\d{4})(\d)/, '$1-$2');
            // Clear error as soon as user starts retyping
            this.error = '';
            this.found = false;
        },

        async lookup() {
            const digits = this.cnpj.replace(/\D/g, '');

            if (digits.length !== 14) {
                this.error = 'Digite um CNPJ válido com 14 dígitos.';
                return;
            }

            this.loading = true;
            this.error = '';
            this.found = false;

            try {
                const res = await fetch(`https://open.cnpja.com/office/${digits}`, {
                    headers: { 'Accept': 'application/json' }
                });

                if (res.status === 404 || res.status === 400) {
                    this.error = 'CNPJ não encontrado. Verifique e tente novamente.';
                    return;
                }

                if (!res.ok) {
                    this.error = 'Erro ao consultar o CNPJ. Tente novamente.';
                    return;
                }

                const data = await res.json();
                this.fillForm(data);
                this.found = true;

            } catch (e) {
                this.error = 'Não foi possível conectar à API. Verifique sua conexão.';
            } finally {
                this.loading = false;
            }
        },

        fillForm(data) {
            // Name: official name from company.name, alias as fallback
            this.setField('nome', data.company?.name || data.alias || '');

            // Responsável: first member (e.g. Sócio-Administrador)
            const responsavel = data.company?.members?.[0]?.person?.name ?? '';
            this.setField('responsavel', responsavel);

            // Email (first corporate email)
            const email = data.emails?.find(e => e.ownership === 'CORPORATE')?.address
                ?? data.emails?.[0]?.address
                ?? '';
            this.setField('email', email);

            // Phone (area + number)
            const phone = data.phones?.[0];
            if (phone) {
                this.setField('telefone', `(${phone.area}) ${phone.number}`);
            }
        },

        setField(name, value) {
            const el = document.querySelector(`[name="${name}"]`);
            if (el) {
                el.value = value;
                // Trigger input event so any reactivity picks it up
                el.dispatchEvent(new Event('input', { bubbles: true }));
            }
        },
    };
}
