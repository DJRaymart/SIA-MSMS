
var queueApiBase = (typeof window.QUEUE_API_URL !== 'undefined' && window.QUEUE_API_URL)
    ? window.QUEUE_API_URL
    : ((typeof window.QUEUE_BASE !== 'undefined' && window.QUEUE_BASE) ? (window.QUEUE_BASE + '/api') : 'api');

function updateTime() {
    var el = document.getElementById('current-time');
    if (el) {
        var now = new Date();
        el.textContent = now.toLocaleTimeString() + ' - ' + now.toLocaleDateString();
    }
}
if (typeof setInterval !== 'undefined') setInterval(updateTime, 1000);
updateTime();

var customerForm = document.getElementById('customerForm');
if (customerForm) {
    customerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        var name = document.getElementById('customerName').value;
        var serviceType = document.getElementById('serviceType').value;
        
        fetch(queueApiBase + '/add_customer.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ name: name, service_type: serviceType })
    })
    .then(function(response) {
        return response.text().then(function(text) {
            var data;
            try { data = JSON.parse(text); } catch (e) { data = { success: false, message: text || 'Invalid server response' }; }
            return { ok: response.ok, status: response.status, data: data };
        });
    })
    .then(function(result) {
        var data = result.data;
        if (result.ok && data.success) {
            document.getElementById('generatedQueue').textContent = data.queue_number;
            document.getElementById('queueResult').classList.remove('hidden');
            document.getElementById('customerForm').reset();
            refreshQueue();
            refreshStats();

            setTimeout(function() {
                document.getElementById('queueResult').classList.add('hidden');
            }, 5000);
        } else {
            showError(data.message || 'Failed to generate queue number');
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        showError('An error occurred while generating queue number');
    });
    });
}

function refreshQueue(page) {
    var p = page || (window.QUEUE_CURRENT_PAGE || 1);
    fetch(queueApiBase + '/get_queue.php?page=' + p)
        .then(function(response) {
            return response.text().then(function(text) {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    return { success: false, customers: [], counters: [], total: 0, page: 1, total_pages: 1 };
                }
            });
        })
        .then(function(data) {
            if (data && data.success !== false) {
                updateQueueTable(data.customers || []);
                updateCounters(data.counters || []);
                updateQueuePagination(data);
            } else if (data && data.message) {
                showError(data.message);
            }
        })
        .catch(function(error) {
            console.error('Error loading queue:', error);
            updateQueueTable([]);
            updateCounters([]);
        });
}

function goToQueuePage(page) {
    window.QUEUE_CURRENT_PAGE = Math.max(1, parseInt(page, 10));
    refreshQueue(window.QUEUE_CURRENT_PAGE);
}

function updateQueuePagination(data) {
    var total = data.total || 0;
    var page = data.page || 1;
    var perPage = data.per_page || 10;
    var totalPages = data.total_pages || 1;
    window.QUEUE_CURRENT_PAGE = page;
    window.QUEUE_TOTAL_PAGES = totalPages;
    var start = total > 0 ? (page - 1) * perPage + 1 : 0;
    var end = Math.min(page * perPage, total);
    var rangeEl = document.getElementById('queueRange');
    var totalEl = document.getElementById('queueTotal');
    var pageNumEl = document.getElementById('queuePageNum');
    var totalPagesEl = document.getElementById('queueTotalPages');
    if (rangeEl) rangeEl.textContent = total > 0 ? (start + '-' + end) : 0;
    if (totalEl) totalEl.textContent = total;
    if (pageNumEl) pageNumEl.textContent = page;
    if (totalPagesEl) totalPagesEl.textContent = totalPages;
}

function updateQueueTable(customers) {
    var table = document.getElementById('queueTable');
    if (!table) return;
    
    if (!customers || customers.length === 0) {
        table.innerHTML = `
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2 block"></i>
                    No customers in queue
                </td>
            </tr>
        `;
        return;
    }
    
    table.innerHTML = '';
    
    customers.forEach(customer => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        
        let statusClass = '';
        let statusText = '';
        switch(customer.status) {
            case 'waiting': 
                statusClass = 'bg-yellow-100 text-yellow-800';
                statusText = 'Waiting';
                break;
            case 'serving': 
                statusClass = 'bg-blue-100 text-blue-800';
                statusText = 'Serving';
                break;
            case 'completed': 
                statusClass = 'bg-green-100 text-green-800';
                statusText = 'Completed';
                break;
            case 'cancelled': 
                statusClass = 'bg-red-100 text-red-800';
                statusText = 'Cancelled';
                break;
        }
        
        const createdDate = new Date(customer.created_at);
        row.innerHTML = `
            <td class="px-4 py-3">
                <span class="queue-number text-lg font-bold">${customer.queue_number}</span>
            </td>
            <td class="px-4 py-3">${customer.name}</td>
            <td class="px-4 py-3">
                <span class="px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    ${customer.service_type}
                </span>
            </td>
            <td class="px-4 py-3">
                <span class="px-3 py-1 rounded-full text-sm font-medium ${statusClass}">
                    ${statusText}
                </span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-500">
                ${createdDate.toLocaleDateString()}
            </td>
            <td class="px-4 py-3 text-sm text-gray-500">
                ${createdDate.toLocaleTimeString()}
            </td>
            <td class="px-4 py-3">
                <div class="flex space-x-2">
                    ${customer.status === 'waiting' ? `
                        <button onclick="callCustomer(${customer.id})" 
                                class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 transition duration-200">
                            <i class="fas fa-bullhorn mr-1"></i>Call
                        </button>
                    ` : ''}
                    ${customer.status === 'serving' ? `
                        <button onclick="completeCustomer(${customer.id})" 
                                class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition duration-200">
                            <i class="fas fa-check mr-1"></i>Complete
                        </button>
                    ` : ''}
                    ${customer.status !== 'completed' && customer.status !== 'cancelled' ? `
                        <button onclick="cancelCustomer(${customer.id})" 
                                class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 transition duration-200">
                            <i class="fas fa-times mr-1"></i>Cancel
                        </button>
                    ` : ''}
                </div>
            </td>
        `;
        table.appendChild(row);
    });
}

function updateCounters(counters) {
    var container = document.getElementById('countersStatus');
    if (!container) return;
    
    if (!counters || counters.length === 0) {
        container.innerHTML = '<div class="text-center text-gray-500">No counters configured</div>';
        return;
    }

    
    var callToEl = document.getElementById('callToCounter');
    if (callToEl) {
        var currentVal = callToEl.value;
        callToEl.innerHTML = counters.map(function(c) {
            var disabled = !c.is_online ? ' disabled' : '';
            var name = (c.name || 'Counter ' + c.id).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
            var sel = (String(c.id) === currentVal || (!currentVal && c.is_online)) ? ' selected' : '';
            return '<option value="' + c.id + '"' + disabled + sel + '>' + name + '</option>';
        }).join('');
        if (!callToEl.value && counters.length) {
            var firstOnline = counters.find(function(c) { return c.is_online; }) || counters[0];
            if (firstOnline) callToEl.value = firstOnline.id;
        }
    }
    
    container.innerHTML = '';
    
    counters.forEach(function(counter) {
        var counterDiv = document.createElement('div');
        var st = counter.service_types;
        if (typeof st === 'string') {
            try { st = JSON.parse(st); } catch (e) { st = []; }
        }
        var servicesText = Array.isArray(st) ? st.join(', ') : (st || 'General');
        counterDiv.className = 'border rounded-lg p-4 ' + (counter.is_online ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200');
        counterDiv.innerHTML = 
            '<div class="flex justify-between items-center mb-2">' +
            '<h4 class="font-semibold">' + (counter.name || 'Counter') + '</h4>' +
            '<span class="px-2 py-1 rounded text-xs ' + (counter.is_online ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800') + '">' +
            (counter.is_online ? 'Online' : 'Offline') + '</span></div>' +
            '<div class="text-sm text-gray-600 mb-2">Services: ' + servicesText + '</div>' +
            '<div class="text-sm">' + (counter.current_customer_name ? 'Serving: <span class="font-bold">' + counter.current_customer_name + '</span>' : 'Available') + '</div>';
        container.appendChild(counterDiv);
    });
}

function callCustomer(customerId) {
    var counterEl = document.getElementById('callToCounter');
    var counterId = counterEl ? parseInt(counterEl.value, 10) : 0;
    var payload = { customer_id: customerId };
    if (counterId > 0) payload.counter_id = counterId;
    fetch(queueApiBase + '/call_customer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            refreshQueue();
            refreshStats();
        } else {
            showError(data.message || 'Failed to call customer');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to call customer');
    });
}

function completeCustomer(customerId) {
    fetch(queueApiBase + '/complete_customer.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ customer_id: customerId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            refreshQueue();
            refreshStats();
        } else {
            showError(data.message || 'Failed to complete customer');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to complete customer');
    });
}

function cancelCustomer(customerId) {
    if (confirm('Are you sure you want to cancel this customer?')) {
        fetch(queueApiBase + '/cancel_customer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ customer_id: customerId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                refreshQueue();
                refreshStats();
            } else {
                showError(data.message || 'Failed to cancel customer');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to cancel customer');
        });
    }
}

function refreshStats() {
    fetch(queueApiBase + '/get_stats.php')
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data && data.success && data.data) {
                var stats = data.data;
                var w = document.getElementById('waiting-count');
                var s = document.getElementById('serving-count');
                var c = document.getElementById('completed-count');
                var t = document.getElementById('today-count');
                if (w) w.textContent = stats.waiting;
                if (s) s.textContent = stats.serving;
                if (c) c.textContent = stats.completed;
                if (t) t.textContent = stats.today_total;
            }
        })
        .catch(function(error) {
            console.error('Error loading stats:', error);
        });
}

function showError(message) {

    let errorDiv = document.getElementById('errorNotification');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'errorNotification';
        errorDiv.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
        document.body.appendChild(errorDiv);
    }
    
    errorDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    setTimeout(() => {
        if (errorDiv.parentElement) {
            errorDiv.remove();
        }
    }, 5000);
}

setInterval(function() {
    refreshQueue();
    refreshStats();
}, 5000);

function loadQueueData() {
    refreshQueue();
    refreshStats();
}

function initQueueDashboard() {
    loadQueueData();
}

function runWhenReady() {
    initQueueDashboard();
    setTimeout(initQueueDashboard, 200);
    setTimeout(initQueueDashboard, 800);
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runWhenReady);
} else {
    runWhenReady();
}
window.addEventListener('load', function() { loadQueueData(); });