
let lastServingNumber = '';
let lastNextNumber = '';

function updateDisplayTime() {
    const now = new Date();

    document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-US', {
        hour12: true,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });

    document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}

setInterval(updateDisplayTime, 1000);
updateDisplayTime();

function playNotificationSound() {
    const audio = document.getElementById('notificationSound');
    if (audio) {
        audio.currentTime = 0;
        audio.play().catch(e => console.log('Audio play failed:', e));
    }
}

async function updateDisplay() {
    try {
        const apiUrl = (typeof window.DISPLAY_API_URL !== 'undefined' && window.DISPLAY_API_URL) || 'api/get_display_data.php';
        const response = await fetch(apiUrl);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.error) {
            console.error('Server error:', data.error);
        }

        const nowServingElement = document.getElementById('nowServing');
        const currentServing = data.now_serving ? data.now_serving.queue_number : '---';
        
        if (currentServing !== lastServingNumber && currentServing !== '---') {
            nowServingElement.textContent = currentServing;
            nowServingElement.classList.add('flip-in');

            if (lastServingNumber !== '' && currentServing !== lastServingNumber) {
                playNotificationSound();
            }
            
            setTimeout(() => {
                nowServingElement.classList.remove('flip-in');
            }, 600);
            
            lastServingNumber = currentServing;
        } else if (currentServing === '---') {
            nowServingElement.textContent = '---';
        }

        
        const counterNumberEl = document.getElementById('counterNumber');
        const serviceTypeEl = document.getElementById('serviceType');
        var counterText = '—';
        var serviceText = '—';
        if (data.now_serving) {
            var cn = (data.now_serving.counter_name || '').toString().trim();
            counterText = cn || 'Ask staff';
            var st = (data.now_serving.service_type || '').toString().toLowerCase();
            serviceText = (st === 'payment') ? 'Payment' : (st === 'inquiry') ? 'Registrar' : st ? (st.charAt(0).toUpperCase() + st.slice(1)) : 'General';
        }
        if (counterNumberEl) counterNumberEl.textContent = counterText;
        if (serviceTypeEl) serviceTypeEl.textContent = serviceText;

        const nextInLineElement = document.getElementById('nextInLine');
        const nextCustomerNameElement = document.getElementById('nextCustomerName');
        const currentNext = data.next_in_line ? data.next_in_line.queue_number : '---';
        
        if (currentNext !== lastNextNumber) {
            nextInLineElement.textContent = currentNext;
            nextCustomerNameElement.textContent = data.next_in_line ? data.next_in_line.name : 'Waiting for next customer';
            lastNextNumber = currentNext;
        }

        document.getElementById('waitingCount').textContent = data.waiting_count;

        const avgWaitElement = document.getElementById('averageWait');
        if (data.waiting_count > 0) {
            const avgMinutes = Math.max(2, Math.min(15, Math.floor(data.waiting_count * 3)));
            avgWaitElement.textContent = `${avgMinutes} min`;
        } else {
            avgWaitElement.textContent = '0 min';
        }

        const recentContainer = document.getElementById('recentNumbers');
        if (data.recent_called && data.recent_called.length > 0) {
            recentContainer.innerHTML = '';
            data.recent_called.slice(0, 8).forEach(customer => {
                const div = document.createElement('div');
                div.className = 'bg-white bg-opacity-25 rounded-xl px-6 py-4 text-3xl font-bold queue-number transform hover:scale-105 transition duration-300';
                div.textContent = customer.queue_number;
                div.title = `Called at ${new Date(customer.called_at).toLocaleTimeString()}`;
                recentContainer.appendChild(div);
            });
        } else {
            recentContainer.innerHTML = '<div class="text-2xl opacity-70">No recent calls</div>';
        }

        const tickerElement = document.getElementById('waitingQueueTicker');
        if (data.waiting_queue && data.waiting_queue.length > 0) {
            const queueNumbers = data.waiting_queue.map(customer => customer.queue_number).join(' • ');
            tickerElement.textContent = `Waiting: ${queueNumbers} • `;
            tickerElement.classList.add('ticker-item');
        } else {
            tickerElement.textContent = 'Queue is empty';
            tickerElement.classList.remove('ticker-item');
        }

    } catch (error) {
        console.error('Error updating display:', error);
        var n = document.getElementById('nowServing'); if (n) n.textContent = '---';
        var c = document.getElementById('counterNumber'); if (c) c.textContent = 'Ask staff';
        var s = document.getElementById('serviceType'); if (s) s.textContent = '—';
        var nl = document.getElementById('nextInLine'); if (nl) nl.textContent = '---';
        var wc = document.getElementById('waitingCount'); if (wc) wc.textContent = '0';
        var rc = document.getElementById('recentNumbers'); if (rc) rc.innerHTML = '<div class="text-2xl opacity-70">Connection error</div>';
    }
}

let refreshInterval = 3000; 
let errorCount = 0;

function startAutoRefresh() {
    setInterval(() => {
        updateDisplay().then(() => {

            errorCount = 0;
            refreshInterval = 3000;
        }).catch(() => {
            errorCount++;

            refreshInterval = Math.min(30000, 3000 + (errorCount * 2000));
        });
    }, refreshInterval);
}

const style = document.createElement('style');
style.textContent = `
    @keyframes numberPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .number-pulse {
        animation: numberPulse 2s ease-in-out infinite;
    }
    
    @keyframes slideInFromRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    .slide-in-right {
        animation: slideInFromRight 0.5s ease-out;
    }
`;
document.head.appendChild(style);

updateDisplay();
startAutoRefresh();

document.addEventListener('keydown', (e) => {
    if (e.key === 'r' || e.key === 'R') {
        updateDisplay();
    }
});

document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        updateDisplay();
    }
});