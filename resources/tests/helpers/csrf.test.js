import { describe, it, expect, vi, beforeEach } from 'vitest';
import { getCsrfToken } from '../../js/Pages/utils/helpers/csrf.js';

describe('getCsrfToken', () => {
    beforeEach(() => {
        document.cookie.split(';').forEach((c) => {
            const name = c.trim().split('=')[0];
            if (name) document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
        });
        document.head.innerHTML = '';
    });

    it('returns token from XSRF-TOKEN cookie', () => {
        document.cookie = 'XSRF-TOKEN=mytoken%3D';

        const token = getCsrfToken();

        expect(token).toBe('mytoken=');
    });

    it('returns token from meta tag when cookie is absent', () => {
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = 'meta-token';
        document.head.appendChild(meta);

        const token = getCsrfToken();

        expect(token).toBe('meta-token');
    });

    it('prefers cookie over meta tag', () => {
        document.cookie = 'XSRF-TOKEN=cookie-token';
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = 'meta-token';
        document.head.appendChild(meta);

        const token = getCsrfToken();

        expect(token).toBe('cookie-token');
    });

    it('returns undefined when both cookie and meta tag are absent', () => {
        const token = getCsrfToken();

        expect(token).toBeUndefined();
    });

    it('handles URL-encoded cookie values', () => {
        document.cookie = 'XSRF-TOKEN=encoded%2Bvalue%3F';

        const token = getCsrfToken();

        expect(token).toBe('encoded+value?');
    });
});
