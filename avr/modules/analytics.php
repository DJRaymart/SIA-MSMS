<?php
require_once '../config/database.php';

$conn = getDBConnection();

$currentMonth = date('m');
$currentYear = date('Y');
$daysInMonth = date('t');

$totalEquipment = $conn->query("SELECT COUNT(*) as total FROM avr_inventory")->fetch_assoc()['total'];
$usedEquipment = $conn->query("SELECT COUNT(DISTINCT Item) as used FROM avr_borrowed WHERE Status = 'Active'")->fetch_assoc()['used'];
$totalBorrowed = $conn->query("SELECT COUNT(*) as total FROM avr_borrowed")->fetch_assoc()['total'];
$activeBorrowed = $conn->query("SELECT COUNT(*) as active FROM avr_borrowed WHERE Status = 'Active'")->fetch_assoc()['active'];
$returnedBorrowed = $conn->query("SELECT COUNT(*) as returned FROM avr_borrowed WHERE Status = 'Returned'")->fetch_assoc()['returned'];
$overdueBorrowed = $conn->query("SELECT COUNT(*) as overdue FROM avr_borrowed WHERE Status = 'Overdue'")->fetch_assoc()['overdue'];

$todayBorrowed = $conn->query("SELECT COUNT(*) as today FROM avr_borrowed WHERE DateBorrowed = CURDATE()")->fetch_assoc()['today'];
$dailyPercentage = $totalEquipment > 0 ? round(($todayBorrowed / $totalEquipment) * 100, 2) : 0;

$totalDepartments = $conn->query("SELECT COUNT(DISTINCT Department) as total FROM avr_reservation WHERE MONTH(Date) = $currentMonth AND YEAR(Date) = $currentYear")->fetch_assoc()['total'];
$totalUsers = $conn->query("SELECT COUNT(DISTINCT Name) as total FROM avr_reservation WHERE MONTH(Date) = $currentMonth AND YEAR(Date) = $currentYear")->fetch_assoc()['total'];
$totalReservations = $conn->query("SELECT COUNT(*) as total FROM avr_reservation WHERE MONTH(Date) = $currentMonth AND YEAR(Date) = $currentYear")->fetch_assoc()['total'];
$monthlyPercentage = $daysInMonth > 0 ? round(($totalReservations / $daysInMonth) * 100, 2) : 0;

$reservationByDept = $conn->query("SELECT Department, COUNT(*) as count FROM avr_reservation WHERE MONTH(Date) = $currentMonth AND YEAR(Date) = $currentYear GROUP BY Department ORDER BY count DESC")->fetch_all(MYSQLI_ASSOC);

$totalDays = $conn->query("SELECT COUNT(DISTINCT Date) as total FROM log_attendance WHERE MONTH(Date) = $currentMonth AND YEAR(Date) = $currentYear")->fetch_assoc()['total'];
$totalAttendanceUsers = $conn->query("SELECT COUNT(DISTINCT Name) as total FROM log_attendance WHERE MONTH(Date) = $currentMonth AND YEAR(Date) = $currentYear")->fetch_assoc()['total'];
$totalAttendanceRecords = $conn->query("SELECT COUNT(*) as total FROM log_attendance WHERE MONTH(Date) = $currentMonth AND YEAR(Date) = $currentYear")->fetch_assoc()['total'];
$totalStudents = $conn->query("SELECT SUM(NoOfStudent) as total FROM log_attendance WHERE MONTH(Date) = $currentMonth AND YEAR(Date) = $currentYear")->fetch_assoc()['total'] ?? 0;

$attendanceByDate = $conn->query("SELECT Date, COUNT(*) as count, SUM(NoOfStudent) as students FROM log_attendance WHERE MONTH(Date) = $currentMonth AND YEAR(Date) = $currentYear GROUP BY Date ORDER BY Date DESC")->fetch_all(MYSQLI_ASSOC);

$borrowedByItem = $conn->query("SELECT Item, COUNT(*) as count, SUM(Quantity) as total_qty FROM avr_borrowed GROUP BY Item ORDER BY count DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<?php require_once '../header_unified.php'; ?>

            <!-- AVR Inventory and Borrowed Report -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <h3 class="text-2xl font-black text-slate-900 mb-6">AVR Inventory and Borrowed Report</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
                        <h4 class="text-sm font-semibold uppercase tracking-wider opacity-90 mb-2">Total # of Equipment</h4>
                        <div class="text-4xl font-black"><?php echo $totalEquipment; ?></div>
                    </div>
                    <div class="bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg">
                        <h4 class="text-sm font-semibold uppercase tracking-wider opacity-90 mb-2">Total # of Used Equipment</h4>
                        <div class="text-4xl font-black"><?php echo $usedEquipment; ?></div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-600 to-pink-600 rounded-2xl p-6 text-white shadow-lg">
                        <h4 class="text-sm font-semibold uppercase tracking-wider opacity-90 mb-2">Daily Percentage</h4>
                        <div class="text-4xl font-black"><?php echo $dailyPercentage; ?>%</div>
                    </div>
                    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg">
                        <h4 class="text-sm font-semibold uppercase tracking-wider opacity-90 mb-2">Active Borrows</h4>
                        <div class="text-4xl font-black"><?php echo $activeBorrowed; ?></div>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg">
                        <h4 class="text-sm font-semibold uppercase tracking-wider opacity-90 mb-2">Returned Items</h4>
                        <div class="text-4xl font-black"><?php echo $returnedBorrowed; ?></div>
                    </div>
                    <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl p-6 text-white shadow-lg">
                        <h4 class="text-sm font-semibold uppercase tracking-wider opacity-90 mb-2">Overdue Items</h4>
                        <div class="text-4xl font-black"><?php echo $overdueBorrowed; ?></div>
                    </div>
                </div>
                
                <h4 class="text-lg font-bold text-blue-600 mb-4">Summary</h4>
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-100 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Item</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Borrow Count</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Total Quantity Borrowed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php if (empty($borrowedByItem)): ?>
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-slate-500">No borrowed items found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($borrowedByItem as $item): ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4 text-sm text-slate-900 font-semibold"><?php echo htmlspecialchars($item['Item']); ?></td>
                                            <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars($item['count']); ?></td>
                                            <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars($item['total_qty']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- AVR Reservation Monthly Report -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <h3 class="text-2xl font-black text-slate-900 mb-6">AVR Reservation Monthly Report (<?php echo date('F Y'); ?>)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-indigo-600 to-purple-600 rounded-2xl p-6 text-white shadow-lg">
                        <h4 class="text-sm font-semibold uppercase tracking-wider opacity-90 mb-2">Total # of Department</h4>
                        <div class="text-4xl font-black"><?php echo $totalDepartments; ?></div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-600 to-cyan-600 rounded-2xl p-6 text-white shadow-lg">
                        <h4 class="text-sm font-semibold uppercase tracking-wider opacity-90 mb-2">Total # of User</h4>
                        <div class="text-4xl font-black"><?php echo $totalUsers; ?></div>
                    </div>
                    <div class="bg-gradient-to-br from-violet-600 to-purple-800 rounded-2xl p-6 text-white shadow-lg">
                        <h4 class="text-sm font-semibold uppercase tracking-wider opacity-90 mb-2">Monthly Percentage</h4>
                        <div class="text-4xl font-black"><?php echo $monthlyPercentage; ?>%</div>
                    </div>
                    <div class="bg-gradient-to-br from-pink-500 to-rose-600 rounded-2xl p-6 text-white shadow-lg">
                        <h4 class="text-sm font-semibold uppercase tracking-wider opacity-90 mb-2">Total Reservations</h4>
                        <div class="text-4xl font-black"><?php echo $totalReservations; ?></div>
                    </div>
                </div>
                
                <h4 class="text-lg font-bold text-blue-600 mb-4">Summary</h4>
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-100 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Department</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Number of Reservations</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php if (empty($reservationByDept)): ?>
                                    <tr>
                                        <td colspan="2" class="px-6 py-8 text-center text-slate-500">No reservations found for this month</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($reservationByDept as $dept): ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4 text-sm text-slate-900 font-semibold"><?php echo htmlspecialchars($dept['Department']); ?></td>
                                            <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars($dept['count']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Log Attendance Monthly Report -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <h3 class="text-2xl font-black text-slate-900 mb-6">Log Attendance Monthly Report (<?php echo date('F Y'); ?>)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-teal-500 to-cyan-600 rounded-2xl p-6 text-white shadow-lg">
                        <h4 class="text-sm font-semibold uppercase tracking-wider opacity-90 mb-2">Total # of Day</h4>
                        <div class="text-4xl font-black"><?php echo $totalDays; ?></div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
                        <h4 class="text-sm font-semibold uppercase tracking-wider opacity-90 mb-2">Total # of User</h4>
                        <div class="text-4xl font-black"><?php echo $totalAttendanceUsers; ?></div>
                    </div>
                    <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg">
                        <h4 class="text-sm font-semibold uppercase tracking-wider opacity-90 mb-2">Total Records</h4>
                        <div class="text-4xl font-black"><?php echo $totalAttendanceRecords; ?></div>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg">
                        <h4 class="text-sm font-semibold uppercase tracking-wider opacity-90 mb-2">Total Students</h4>
                        <div class="text-4xl font-black"><?php echo number_format($totalStudents); ?></div>
                    </div>
                </div>
                
                <h4 class="text-lg font-bold text-blue-600 mb-4">Summary</h4>
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-100 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Number of Records</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">Total Students</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php if (empty($attendanceByDate)): ?>
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-slate-500">No attendance records found for this month</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($attendanceByDate as $record): ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-4 text-sm text-slate-900 font-semibold"><?php echo htmlspecialchars($record['Date']); ?></td>
                                            <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars($record['count']); ?></td>
                                            <td class="px-6 py-4 text-sm text-slate-700"><?php echo htmlspecialchars($record['students']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
<?php require_once '../footer_unified.php'; ?>

