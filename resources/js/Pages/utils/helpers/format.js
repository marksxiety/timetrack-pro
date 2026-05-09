/**
 * Truncate a string to a maximum length, appending "..." if truncated.
 * @param {string|null|undefined} text
 * @param {number} [maxLength=80]
 * @returns {string}
 */
export function truncateText(text, maxLength = 40) {
    if (!text) return "";
    if (text.length <= maxLength) return text;
    return text.slice(0, maxLength) + "...";
}

/**
 * Extract initials from a full name.
 * @param {string|null|undefined} name
 * @returns {string}
 */
export function getInitials(name) {
    if (!name) return "?";
    const parts = name.trim().split(/\s+/);
    if (parts.length === 1) return parts[0][0].toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}
