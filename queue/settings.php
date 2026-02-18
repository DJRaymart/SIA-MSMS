<?php include 'config.php'; ?>
<?php require_once 'header_unified.php'; ?>

<?php

try {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->query("SELECT * FROM display_settings LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        $stmt = $conn->prepare("INSERT INTO display_settings (company_name, welcome_message, refresh_interval) VALUES (?, ?, ?)");
        $stmt->execute(['Holy Cross of Mintal, Inc.', 'Welcome to MSMS Queue Management', 10]);
        $stmt = $conn->query("SELECT * FROM display_settings LIMIT 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $settings = [
        'company_name' => 'Holy Cross of Mintal, Inc.',
        'welcome_message' => 'Welcome to MSMS Queue Management',
        'refresh_interval' => 10
    ];
    $error = $e->getMessage();
}
?>

<!-- Display Settings Section -->
<div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
    <div class="flex items-center gap-3 mb-6 pb-4 border-b-2 border-blue-200">
        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <circle cx="12" cy="12" r="3" />
        </svg>
        <h2 class="text-2xl font-bold text-slate-900">Display Settings</h2>
    </div>

    <form id="settingsForm" class="space-y-6">
        <input type="hidden" id="settingsId" name="id" value="<?php echo htmlspecialchars($settings['id'] ?? 1); ?>">
        
        <!-- Company Name -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">
                Company Name
                <span class="text-red-500">*</span>
            </label>
            <input type="text" id="companyName" name="company_name" required
                   value="<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>"
                   class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                   placeholder="Enter company name">
            <p class="text-xs text-slate-500 mt-1">This name will be displayed on the queue display screen</p>
        </div>

        <!-- Welcome Message -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">
                Welcome Message
            </label>
            <textarea id="welcomeMessage" name="welcome_message" rows="4"
                      class="w-full px-4 py-3 border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                      placeholder="Enter welcome message"><?php echo htmlspecialchars($settings['welcome_message'] ?? ''); ?></textarea>
            <p class="text-xs text-slate-500 mt-1">This message will be shown on the queue display screen</p>
        </div>

        <!-- Refresh Interval -->
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">
                Refresh Interval (seconds)
                <span class="text-red-500">*</span>
            </label>
            <div class="flex items-center gap-4">
                <input type="number" id="refreshInterval" name="refresh_interval" required min="5" max="60"
                       value="<?php echo htmlspecialchars($settings['refresh_interval'] ?? 10); ?>"
                       class="w-32 px-4 py-3 border-2 border-slate-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                <div class="flex-1">
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <span>Recommended: 10-15 seconds</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">How often the display screen refreshes to show updated queue information</p>
                </div>
            </div>
        </div>

        <!-- Preview Section -->
        <div class="bg-slate-50 rounded-xl p-6 border-2 border-slate-200">
            <h3 class="text-lg font-semibold text-slate-700 mb-4">Preview</h3>
            <div class="bg-white rounded-lg p-6 shadow-md">
                <div class="text-center mb-4">
                    <h4 class="text-2xl font-bold text-slate-900" id="previewCompanyName">
                        <?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>
                    </h4>
                    <p class="text-slate-600 mt-2" id="previewWelcomeMessage">
                        <?php echo htmlspecialchars($settings['welcome_message'] ?? ''); ?>
                    </p>
                </div>
                <div class="text-center text-xs text-slate-500">
                    Refresh interval: <span id="previewRefreshInterval"><?php echo htmlspecialchars($settings['refresh_interval'] ?? 10); ?></span> seconds
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 justify-end pt-4 border-t border-slate-200">
            <button type="button" onclick="resetForm()" class="px-6 py-2 border-2 border-slate-300 text-slate-700 rounded-lg font-semibold hover:bg-slate-50 transition-colors">
                Reset
            </button>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Save Settings
            </button>
        </div>
    </form>
</div>

<!-- Additional Settings Section -->
<div class="bg-white rounded-2xl shadow-lg p-6">
    <div class="flex items-center gap-3 mb-6 pb-4 border-b-2 border-blue-200">
        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h2 class="text-2xl font-bold text-slate-900">System Information</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border-2 border-blue-100">
            <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-3">Display Screen</h3>
            <p class="text-slate-700 mb-4">Access the public queue display screen</p>
            <a href="display.php" target="_blank" class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                Open Display Screen
            </a>
        </div>

        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl p-6 border-2 border-emerald-100">
            <h3 class="text-sm font-semibold text-slate-600 uppercase tracking-wider mb-3">Queue Statistics</h3>
            <div class="space-y-2">
                <?php
                try {
                    $stmt = $conn->query("SELECT COUNT(*) as total FROM customers WHERE DATE(created_at) = CURDATE()");
                    $todayTotal = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                    
                    $stmt = $conn->query("SELECT COUNT(*) as total FROM counters WHERE is_online = 1");
                    $onlineCounters = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                    
                    $stmt = $conn->query("SELECT COUNT(*) as total FROM counters");
                    $totalCounters = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                } catch (Exception $e) {
                    $todayTotal = 0;
                    $onlineCounters = 0;
                    $totalCounters = 0;
                }
                ?>
                <div class="flex justify-between">
                    <span class="text-slate-600">Today's Customers:</span>
                    <span class="font-bold text-slate-900"><?php echo $todayTotal; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600">Online Counters:</span>
                    <span class="font-bold text-emerald-600"><?php echo $onlineCounters; ?> / <?php echo $totalCounters; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Update preview as user types
document.getElementById('companyName').addEventListener('input', function() {
    document.getElementById('previewCompanyName').textContent = this.value || 'Company Name';
});

document.getElementById('welcomeMessage').addEventListener('input', function() {
    document.getElementById('previewWelcomeMessage').textContent = this.value || 'Welcome Message';
});

document.getElementById('refreshInterval').addEventListener('input', function() {
    document.getElementById('previewRefreshInterval').textContent = this.value || '10';
});

function resetForm() {
    if (confirm('Are you sure you want to reset all changes?')) {
        location.reload();
    }
}

document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {
        id: formData.get('id'),
        company_name: formData.get('company_name'),
        welcome_message: formData.get('welcome_message'),
        refresh_interval: parseInt(formData.get('refresh_interval'))
    };
    
    fetch('api/update_settings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Settings saved successfully.',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: data.message || 'Failed to save settings.'
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'An error occurred while saving the settings.'
        });
    });
});
</script>

<?php require_once 'footer_unified.php'; ?>
