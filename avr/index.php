<?php

if (!defined('APP_ROOT')) { require_once dirname(__DIR__) . '/auth/path_config_loader.php'; }
require_once (defined('APP_ROOT') ? APP_ROOT : dirname(__DIR__)) . '/auth/check_admin_access.php';
require_once 'header_unified.php';
?>

<div class="container-fluid">
    <!-- Statistics Cards -->
                    <?php
                    require_once 'config/database.php';
                    $conn = getDBConnection();

                    $result = $conn->query("SELECT COUNT(*) as total FROM avr_inventory");
                    $totalEquipment = $result->fetch_assoc()['total'];
                    
                    $result = $conn->query("SELECT COUNT(DISTINCT Item) as used FROM avr_borrowed WHERE Status = 'Active'");
                    $usedEquipment = $result->fetch_assoc()['used'];
                    
                    $availableEquipment = $totalEquipment - $usedEquipment;
                    $usagePercentage = $totalEquipment > 0 ? round(($usedEquipment / $totalEquipment) * 100, 1) : 0;

                    $result = $conn->query("SELECT COUNT(*) as active FROM avr_borrowed WHERE Status = 'Active'");
                    $activeBorrows = $result->fetch_assoc()['active'];
                    
                    $result = $conn->query("SELECT COUNT(*) as returned FROM avr_borrowed WHERE Status = 'Returned'");
                    $returnedBorrows = $result->fetch_assoc()['returned'];
                    
                    $result = $conn->query("SELECT COUNT(*) as overdue FROM avr_borrowed WHERE Status = 'Overdue'");
                    $overdueBorrows = $result->fetch_assoc()['overdue'];
                    
                    $result = $conn->query("SELECT COUNT(*) as total FROM avr_borrowed");
                    $totalBorrows = $result->fetch_assoc()['total'];

                    $result = $conn->query("SELECT COUNT(*) as today FROM avr_reservation WHERE Date = CURDATE()");
                    $todayReservations = $result->fetch_assoc()['today'];
                    
                    $result = $conn->query("SELECT COUNT(*) as upcoming FROM avr_reservation WHERE Date > CURDATE()");
                    $upcomingReservations = $result->fetch_assoc()['upcoming'];
                    
                    $result = $conn->query("SELECT COUNT(*) as total FROM avr_reservation WHERE MONTH(Date) = MONTH(CURDATE()) AND YEAR(Date) = YEAR(CURDATE())");
                    $monthlyReservations = $result->fetch_assoc()['total'];
                    
                    $result = $conn->query("SELECT COUNT(DISTINCT Department) as dept FROM avr_reservation WHERE MONTH(Date) = MONTH(CURDATE()) AND YEAR(Date) = YEAR(CURDATE())");
                    $activeDepartments = $result->fetch_assoc()['dept'];

                    $result = $conn->query("SELECT COUNT(*) as today FROM log_attendance WHERE Date = CURDATE()");
                    $todayAttendance = $result->fetch_assoc()['today'];
                    
                    $result = $conn->query("SELECT COUNT(*) as total FROM log_attendance WHERE MONTH(Date) = MONTH(CURDATE()) AND YEAR(Date) = YEAR(CURDATE())");
                    $monthlyAttendance = $result->fetch_assoc()['total'];
                    
                    $result = $conn->query("SELECT SUM(NoOfStudent) as students FROM log_attendance WHERE MONTH(Date) = MONTH(CURDATE()) AND YEAR(Date) = YEAR(CURDATE())");
                    $totalStudents = $result->fetch_assoc()['students'] ?? 0;

                    $recentBorrowed = $conn->query("SELECT * FROM avr_borrowed ORDER BY DateBorrowed DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
                    $recentReservations = $conn->query("SELECT * FROM avr_reservation WHERE Date >= CURDATE() ORDER BY Date ASC, Time ASC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
                    $recentAttendance = $conn->query("SELECT * FROM log_attendance ORDER BY Date DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
                    
                    $conn->close();
                    ?>
                    
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <div class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Total Equipment</h3>
                                <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <p class="text-4xl font-black mb-2"><?php echo $totalEquipment; ?></p>
                            <p class="text-sm opacity-80"><?php echo $availableEquipment; ?> available</p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-pink-500 to-rose-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Active Borrows</h3>
                                <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                                </svg>
                            </div>
                            <p class="text-4xl font-black mb-2"><?php echo $activeBorrows; ?></p>
                            <p class="text-sm opacity-80"><?php echo $overdueBorrows; ?> overdue</p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Today's Reservations</h3>
                                <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <p class="text-4xl font-black mb-2"><?php echo $todayReservations; ?></p>
                            <p class="text-sm opacity-80"><?php echo $upcomingReservations; ?> upcoming</p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Today's Attendance</h3>
                                <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-4xl font-black mb-2"><?php echo $todayAttendance; ?></p>
                            <p class="text-sm opacity-80"><?php echo number_format($totalStudents); ?> students this month</p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-yellow-500 to-orange-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Equipment Usage</h3>
                                <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <p class="text-4xl font-black mb-2"><?php echo $usagePercentage; ?>%</p>
                            <p class="text-sm opacity-80"><?php echo $usedEquipment; ?> of <?php echo $totalEquipment; ?> in use</p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-violet-600 to-purple-800 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-sm font-semibold uppercase tracking-wider opacity-90">Monthly Reservations</h3>
                                <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <p class="text-4xl font-black mb-2"><?php echo $monthlyReservations; ?></p>
                            <p class="text-sm opacity-80"><?php echo $activeDepartments; ?> active departments</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Section -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-6">
                    <!-- Recent Borrowed Items -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <div class="flex items-center gap-2 mb-6 pb-4 border-b-2 border-blue-200">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                            </svg>
                            <h3 class="text-lg font-bold text-blue-600">Recent Borrowed Items</h3>
                        </div>
                        <?php if (empty($recentBorrowed)): ?>
                            <p class="text-slate-500 text-center py-8">No recent borrows</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($recentBorrowed as $borrow): ?>
                                    <div class="flex justify-between items-start p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                                        <div class="flex-1">
                                            <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($borrow['Item']); ?></p>
                                            <p class="text-sm text-slate-600 mt-1">
                                                <?php echo htmlspecialchars($borrow['Name']); ?> • Qty: <?php echo $borrow['Quantity']; ?>
                                            </p>
                                        </div>
                                        <div class="text-right ml-4">
                                            <?php 
                                            $status = strtolower($borrow['Status']);
                                            $statusColors = [
                                                'active' => 'bg-green-500',
                                                'returned' => 'bg-blue-500',
                                                'overdue' => 'bg-red-500'
                                            ];
                                            $color = $statusColors[$status] ?? 'bg-slate-500';
                                            ?>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold text-white <?php echo $color; ?> mb-2">
                                                <?php echo htmlspecialchars($borrow['Status']); ?>
                                            </span>
                                            <p class="text-xs text-slate-500">
                                                <?php echo date('M j', strtotime($borrow['DateBorrowed'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <a href="modules/borrowed.php" class="block text-center mt-6 text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                            View All Borrowed →
                        </a>
                    </div>

                    <!-- Upcoming Reservations -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <div class="flex items-center gap-2 mb-6 pb-4 border-b-2 border-blue-200">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <h3 class="text-lg font-bold text-blue-600">Upcoming Reservations</h3>
                        </div>
                        <?php if (empty($recentReservations)): ?>
                            <p class="text-slate-500 text-center py-8">No upcoming reservations</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($recentReservations as $reservation): ?>
                                    <div class="flex justify-between items-start p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                                        <div class="flex-1">
                                            <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($reservation['Name']); ?></p>
                                            <p class="text-sm text-slate-600 mt-1">
                                                <?php echo htmlspecialchars($reservation['Department']); ?>
                                            </p>
                                        </div>
                                        <div class="text-right ml-4">
                                            <p class="font-semibold text-blue-600">
                                                <?php echo date('M j', strtotime($reservation['Date'])); ?>
                                            </p>
                                            <p class="text-xs text-slate-500 mt-1">
                                                <?php echo date('g:i A', strtotime($reservation['Time'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <a href="modules/reservation.php" class="block text-center mt-6 text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                            View All Reservations →
                        </a>
                    </div>

                    <!-- Recent Attendance -->
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <div class="flex items-center gap-2 mb-6 pb-4 border-b-2 border-blue-200">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-lg font-bold text-blue-600">Recent Attendance Records</h3>
                        </div>
                        <?php if (empty($recentAttendance)): ?>
                            <p class="text-slate-500 text-center py-8">No recent attendance records</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($recentAttendance as $attendance): ?>
                                    <div class="flex justify-between items-start p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                                        <div class="flex-1">
                                            <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($attendance['Name']); ?></p>
                                            <p class="text-sm text-slate-600 mt-1">
                                                <?php echo htmlspecialchars($attendance['GradeSection']); ?>
                                            </p>
                                        </div>
                                        <div class="text-right ml-4">
                                            <p class="font-semibold text-blue-600">
                                                <?php echo $attendance['NoOfStudent']; ?> students
                                            </p>
                                            <p class="text-xs text-slate-500 mt-1">
                                                <?php echo date('M j, Y', strtotime($attendance['Date'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <a href="modules/attendance.php" class="block text-center mt-6 text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                            View All Attendance →
                        </a>
                    </div>
</div>

<?php require_once 'footer_unified.php'; ?>

