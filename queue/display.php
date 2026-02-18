<?php

if (!defined('APP_ROOT')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }

include 'config.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->query("SELECT * FROM display_settings LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $settings = ['company_name' => 'Customer Service', 'welcome_message' => 'Welcome to Holycross Mintal'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Display</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Arial', sans-serif;
            overflow-x: hidden;
        }
        .marquee {
            animation: marquee 20s linear infinite;
            white-space: nowrap;
            display: inline-block;
            padding-left: 100%;
        }
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }
        .flip-in {
            animation: flipIn 0.6s ease-in-out;
        }
        @keyframes flipIn {
            from { 
                transform: rotateX(90deg) scale(0.8); 
                opacity: 0; 
            }
            to { 
                transform: rotateX(0deg) scale(1); 
                opacity: 1; 
            }
        }
        .pulse-glow {
            animation: pulseGlow 2s infinite;
        }
        @keyframes pulseGlow {
            0%, 100% { 
                box-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
            }
            50% { 
                box-shadow: 0 0 40px rgba(255, 255, 255, 0.8);
            }
        }
        .queue-number {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .ticker-item {
            animation: tickerScroll 30s linear infinite;
        }
        @keyframes tickerScroll {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
    </style>
</head>
<body class="text-white min-h-screen">
    <!-- Header with Marquee -->
    <div class="bg-black bg-opacity-30 py-3 mb-8">
        <div class="container mx-auto px-4">
            <div class="overflow-hidden">
                <div class="marquee text-xl font-semibold">
                    <i class="fas fa-info-circle mr-3"></i>
                    <?php echo htmlspecialchars($settings['welcome_message'] ?? 'Welcome to Holycross Mintal'); ?>
                    • Please have your queue number ready • Thank you for your patience •
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <!-- Company Header -->
        <div class="text-center mb-12">
            <h1 class="text-6xl font-bold mb-6" id="companyName">
                <?php echo htmlspecialchars($settings['company_name'] ?? 'Customer Service Center'); ?>
            </h1>
            <div class="text-3xl opacity-90" id="welcomeMessage">
                Queue Management System
            </div>
        </div>

        <!-- Now Serving Section -->
        <div class="bg-white bg-opacity-20 rounded-3xl p-12 mb-12 text-center border-4 border-white border-opacity-30 pulse-glow">
            <h2 class="text-5xl font-bold mb-8 text-yellow-300">NOW SERVING</h2>
            <div id="nowServing" class="text-9xl font-bold flip-in queue-number text-yellow-300 mb-8">---</div>
            <div id="servingCounter" class="bg-yellow-400 bg-opacity-30 rounded-2xl py-6 px-8 inline-block border-2 border-yellow-300 border-opacity-50">
                <p class="text-2xl font-semibold text-white mb-4 opacity-90">Please proceed to:</p>
                <div class="flex flex-col items-center gap-3 text-left">
                    <div class="flex items-center gap-3">
                        <span class="text-xl font-semibold text-white opacity-90 w-28">Counter:</span>
                        <span id="counterNumber" class="text-4xl font-bold text-yellow-300">—</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xl font-semibold text-white opacity-90 w-28">Service:</span>
                        <span id="serviceType" class="text-4xl font-bold text-yellow-300">—</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <!-- Next in Line -->
            <div class="bg-white bg-opacity-20 rounded-2xl p-8 text-center backdrop-blur-sm">
                <h3 class="text-3xl font-bold mb-6 text-green-300">
                    <i class="fas fa-arrow-right mr-3"></i>NEXT IN LINE
                </h3>
                <div id="nextInLine" class="text-5xl font-bold queue-number text-green-300 mb-4">---</div>
                <div class="text-xl opacity-90" id="nextCustomerName">Waiting for next customer</div>
            </div>
            
            <!-- Waiting Count -->
            <div class="bg-white bg-opacity-20 rounded-2xl p-8 text-center backdrop-blur-sm">
                <h3 class="text-3xl font-bold mb-6 text-blue-300">
                    <i class="fas fa-users mr-3"></i>WAITING
                </h3>
                <div id="waitingCount" class="text-5xl font-bold text-blue-300 mb-4">0</div>
                <div class="text-xl opacity-90">customers in queue</div>
            </div>
            
            <!-- Average Wait Time -->
            <div class="bg-white bg-opacity-20 rounded-2xl p-8 text-center backdrop-blur-sm">
                <h3 class="text-3xl font-bold mb-6 text-purple-300">
                    <i class="fas fa-clock mr-3"></i>AVERAGE WAIT
                </h3>
                <div id="averageWait" class="text-5xl font-bold text-purple-300 mb-4">5 min</div>
                <div class="text-xl opacity-90">estimated time</div>
            </div>
        </div>

        <!-- Recently Called Section -->
        <div class=" bg-opacity-15 rounded-2xl p-8 mb-8 text-center backdrop-blur-sm">
            <h3 class="text-4xl font-bold mb-6  text-orange-300">
                <i class="fas fa-history mr-3"></i>RECENTLY CALLED
            </h3>
            <div id="recentNumbers" class="flex justify-center space-x-6 flex-wrap gap-4">
                <!-- Recent numbers will appear here -->
                <div class="text-2xl opacity-70">No recent calls</div>
            </div>
        </div>

        <!-- Waiting Queue Ticker -->
        <div class="bg-black bg-opacity-40 rounded-xl p-4 mb-8">
            <div class="flex items-center mb-2">
                <i class="fas fa-list-ol text-2xl mr-3 text-yellow-400"></i>
                <h4 class="text-2xl font-bold text-yellow-400">Waiting Queue</h4>
            </div>
            <div class="overflow-hidden">
                <div id="waitingQueueTicker" class="ticker-item text-xl font-semibold">
                    Queue is empty
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <div class="text-2xl font-semibold" id="currentDate"></div>
                <div id="currentTime" class="text-4xl font-mono font-bold text-yellow-300"></div>
                <div class="text-2xl font-semibold">
                    <i class="fas fa-heart text-red-400 mr-2"></i>
                    Thank you for waiting
                </div>
            </div>
        </div>
    </div>

    <!-- Audio for notifications -->
    <audio id="notificationSound" preload="auto">
        <source src="https://assets.mixkit.co/sfx/preview/mixkit-correct-answer-tone-2870.mp3" type="audio/mpeg">
    </audio>

    <script>
        window.DISPLAY_API_URL = (function(){var b=document.location.pathname;return b.substring(0,b.lastIndexOf('/')+1)+'api/get_display_data.php';})();
    </script>
    <script src="js/display.js"></script>
</body>
</html>