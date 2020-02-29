import EasyMDE from "easymde";

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById("character_preset_notes")) {
        let mde = new EasyMDE({
            element: document.getElementById("character_preset_notes"),
            forceSync: true,
            // previewRender: function(plainText) {
            //     return parseMarkdown(plainText);
            // },
            renderingConfig: {
                singleLineBreaks: true,
            },
            toolbar: ['bold', 'italic', 'strikethrough', 'preview']
        });
    }
});