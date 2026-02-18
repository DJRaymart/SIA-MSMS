
function triggerLabLoader(redirectUrl, message = "Initializing Terminal") {
    const overlay = document.getElementById('initOverlay');
    const progress = document.getElementById('progressBar');
    const percentNum = document.getElementById('percentNum');
    const statusText = document.getElementById('statusText');

    if (!overlay) {
        if (redirectUrl) {
            window.location.href = redirectUrl;
        }
        return;
    }

    if (statusText) statusText.innerText = message;
    overlay.classList.remove('hidden');
    overlay.classList.add('flex');

    if (progress) progress.style.width = '0%';
    if (percentNum) percentNum.innerText = '0';

    let count = 0;
    const interval = setInterval(() => {
        count++;
        if (percentNum) percentNum.innerText = count;
        if (count >= 100) clearInterval(interval);
    }, 12); 

    setTimeout(() => {
        if (progress) progress.style.width = '100%';
    }, 50);

    setTimeout(() => {
        if (redirectUrl) {
            window.location.href = redirectUrl;
        } else {

            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }
    }, 1800);
}