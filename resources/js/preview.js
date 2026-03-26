/**
 * Handles image preview for file inputs.
 * 
 * @param {HTMLInputElement} input - The file input element.
 * @param {string} previewId - The ID of the primary image/preview element.
 * @param {string} placeholderId - The ID of the placeholder element (if no image exists yet).
 * @param {string} nameSpanId - Optional ID of a span to display the filename.
 */
window.handleFilePreview = function(input, previewId, placeholderId, nameSpanId = null) {
    const file = input.files[0];
    if (!file) return;

    if (nameSpanId) {
        const nameSpan = document.getElementById(nameSpanId);
        if (nameSpan) nameSpan.innerText = file.name;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
        const img = document.getElementById(previewId);
        const placeholder = document.getElementById(placeholderId);

        if (img) {
            img.src = e.target.result;
        } else if (placeholder) {
            // If the image doesn't exist yet, replace placeholder content with an <img> tag
            placeholder.innerHTML = `<img id="${previewId}" src="${e.target.result}" class="h-full w-full object-cover">`;
        }
    };
    reader.readAsDataURL(file);
};
