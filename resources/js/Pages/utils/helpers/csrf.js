/**
 * Retrieve the CSRF token from cookies or meta tag.
 * @returns {string}
 */
export function getCsrfToken() {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match
        ? decodeURIComponent(match[1])
        : document.querySelector('meta[name="csrf-token"]')?.content;
}
