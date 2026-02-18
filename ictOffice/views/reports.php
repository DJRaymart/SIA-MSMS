<!-- Reports content - header and sidebar are already included via header_unified.php -->

<div id="main" class="transition-all duration-300 ease-in-out min-h-screen p-6">
    <h1 class="text-3xl font-semibold mb-6">Statistics Report</h1>
    
    <!-- Stats Box -->
    <div class="bg-white shadow-lg rounded-lg p-6 mb-8">
        <p class="text-lg font-medium text-gray-800 mb-3">
            <strong>Total Number of Classes:</strong> 3
        </p>
        <p class="text-lg font-medium text-gray-800 mb-3">
            <strong>Average Number of Users per Week:</strong> 3
        </p>
        <p class="text-lg font-medium text-gray-800 mb-3">
            <strong>Average Daily Users:</strong> 2
        </p>
    </div>

    <!-- Chart -->
    <div class="bg-white shadow-lg rounded-lg p-6">
        <canvas id="statsChart"></canvas>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('statsChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Total Classes', 'Avg Users per Week', 'Avg Daily Users'],
            datasets: [{
                label: 'Statistics',
                data: [3, 3, 2],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.2)',
                    'rgba(255, 159, 64, 0.2)',
                    'rgba(75, 192, 192, 0.2)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(75, 192, 192, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
