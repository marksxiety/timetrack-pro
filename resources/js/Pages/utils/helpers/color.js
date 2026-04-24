/**
 * Read the computed background color of a Tailwind CSS class by
 * temporarily mounting a hidden element.
 * @param {string} className - e.g. "bg-base-100"
 * @returns {string}
 */
export function getTailwindColor(className) {
    const div = document.createElement('div');
    div.className = className;
    div.style.display = 'none';
    document.body.appendChild(div);
    const color = getComputedStyle(div).backgroundColor;
    document.body.removeChild(div);
    return color;
}
