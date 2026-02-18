<?php
include "../config/db.php";
include "../admin/header.php";

$result = $conn->query("
    SELECT i.*, l.lab_name, loc.location_name
    FROM inventory i
    LEFT JOIN labs l ON i.lab_id = l.lab_id
    LEFT JOIN locations loc ON i.location_id = loc.location_id
    ORDER BY i.date_added DESC
");
?>

<div class="bg-white p-8 rounded-2xl shadow-lg">

    <div class="flex flex-col md:flex-row justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-3 md:mb-0">Inventory</h2>
        <a href="add.php"
           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition transform hover:scale-105">
            Add Item
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300 text-gray-700 rounded-lg">
            <thead class="bg-gradient-to-r from-indigo-200 via-purple-200 to-pink-200">
                <tr>
                    <th class="p-3 border text-left text-sm md:text-base">ID</th>
                    <th class="p-3 border text-left text-sm md:text-base">Item Name</th>
                    <th class="p-3 border text-left text-sm md:text-base">Description</th>
                    <th class="p-3 border text-left text-sm md:text-base">Model No.</th>
                    <th class="p-3 border text-left text-sm md:text-base">Serial No.</th>
                    <th class="p-3 border text-left text-sm md:text-base">Remarks</th>
                    <th class="p-3 border text-left text-sm md:text-base">Location</th>
                    <th class="p-3 border text-left text-sm md:text-base">Laboratory</th>
                    <th class="p-3 border text-left text-sm md:text-base">Date Added</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while($row = $result->fetch_assoc()): ?>
                <tr class="odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition transform hover:scale-[1.01]">
                    <td class="p-2 md:p-3 border text-sm md:text-base"><?= $row['item_id'] ?></td>
                    <td class="p-2 md:p-3 border text-sm md:text-base"><?= $row['item_name'] ?></td>
                    <td class="p-2 md:p-3 border text-sm md:text-base"><?= $row['description'] ?></td>
                    <td class="p-2 md:p-3 border text-sm md:text-base"><?= $row['model_no'] ?></td>
                    <td class="p-2 md:p-3 border text-sm md:text-base"><?= $row['serial_no'] ?></td>
                    <td class="p-2 md:p-3 border text-sm md:text-base"><?= $row['remarks'] ?></td>
                    <td class="p-2 md:p-3 border text-sm md:text-base"><?= $row['location_name'] ?></td>
                    <td class="p-2 md:p-3 border text-sm md:text-base"><?= $row['lab_name'] ?></td>
                    <td class="p-2 md:p-3 border text-sm md:text-base"><?= $row['date_added'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include "../partials/footer.php"; ?>