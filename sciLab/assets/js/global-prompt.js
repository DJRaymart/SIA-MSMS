
function showSystemPrompt(title = "Security_Override", message = "Are you sure?") {
    return new Promise((resolve) => {
        const modal = document.getElementById('systemPrompt');
        const titleEl = document.getElementById('promptTitle');
        const messageEl = document.getElementById('promptMessage');
        const confirmBtn = document.getElementById('promptConfirm');
        const cancelBtn = document.getElementById('promptCancel');

        titleEl.innerText = title;
        messageEl.innerText = message;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        const cleanup = (value) => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            confirmBtn.onclick = null;
            cancelBtn.onclick = null;
            resolve(value);
        };

        confirmBtn.onclick = () => cleanup(true);
        cancelBtn.onclick = () => cleanup(false);
    });
}