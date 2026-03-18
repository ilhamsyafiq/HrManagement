import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.store('darkMode', {
    mode: localStorage.getItem('theme') || 'system',

    get class() {
        if (this.mode === 'dark') return 'dark';
        if (this.mode === 'light') return '';
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : '';
    },

    toggle() {
        if (this.mode === 'light') this.mode = 'dark';
        else if (this.mode === 'dark') this.mode = 'system';
        else this.mode = 'light';

        localStorage.setItem('theme', this.mode);

        if (this.mode === 'dark' || (this.mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    },

    init() {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (this.mode === 'system') {
                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
        });
    }
});

Alpine.start();
