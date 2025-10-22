export function getTailwindColor(className) {
    const div = document.createElement("div");
    div.className = className;
    div.style.display = "none";
    document.body.appendChild(div);
    const color = getComputedStyle(div).backgroundColor;
    document.body.removeChild(div);
    return color;
}
