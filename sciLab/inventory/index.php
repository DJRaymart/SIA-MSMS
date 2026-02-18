<?php
include "../config/db.php";
include "../partials/header.php";

$result = $conn->query("
    SELECT i.*, l.lab_name, loc.location_name
    FROM inventory i
    LEFT JOIN labs l ON i.lab_id = l.lab_id
    LEFT JOIN locations loc ON i.location_id = loc.location_id
    ORDER BY i.date_added DESC
");
?>
<div class="relative min-h-screen w-full overflow-hidden">

    <!-- Full-page background layers -->
    <div class="fixed inset-0 -z-30">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-blue-100 to-green-50"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%)] animate-pulse-slow"></div>
        <div class="absolute inset-0 bg-[url('/images/lab-pattern.svg')] bg-repeat opacity-10"></div>
    </div>

    <!-- Floating lab objects covering entire page -->
    <div class="fixed inset-0 w-full h-full pointer-events-none">

        <!-- <img src="./assets/images/flask.png" class="floating-object w-16" style="top:5%; left:10%; animation-duration:7s; animation-delay:0s;"> -->
        <img src="../assets/images/beaker.png" class="floating-object w-20" style="top:10%; right:20%; animation-duration:9s; animation-delay:1s;">
        <img src="../assets/images/particles.png" class="floating-object w-12" style="top:30%; left:25%; animation-duration:6s; animation-delay:2s;">
        <!-- <img src="./assets/images/test-tube.png" class="floating-object w-16" style="top:45%; left:60%; animation-duration:8s; animation-delay:0.5s;"> -->
        <img src="../assets/images/microscope.png" class="floating-object w-24" style="top:60%; left:15%; animation-duration:10s; animation-delay:1.5s;">
        <!-- <img src="./assets/images/atom.png" class="floating-object w-12" style="top:70%; right:20%; animation-duration:6.5s; animation-delay:0.8s;"> -->
        <img src="../assets/images/petridish.png" class="floating-object w-16" style="top:80%; right:35%; animation-duration:8.5s; animation-delay:1.2s;">
        <img src="../assets/images/formula.png" class="floating-object w-12" style="bottom:5%; left:35%; animation-duration:7.5s; animation-delay:0.3s;">

    </div>

    <!-- Molecule overlay -->
    <div class="fixed inset-0 w-full h-full pointer-events-none overflow-hidden">
        <?php for ($i=0; $i<30; $i++): ?>
            <div class="absolute w-3 h-3 rounded-full animate-molecule-gradient"
                style="top:<?= rand(0,100) ?>%; left:<?= rand(0,100) ?>%; animation-duration:<?= rand(6,15) ?>s; animation-delay:<?= rand(0,5) ?>s;">
            </div>
        <?php endfor; ?>
    </div>

        <div class="relative z-20 max-w-[95rem] mx-auto bg-white/95 backdrop-blur-lg p-16 rounded-3xl shadow-2xl border border-white/30">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <h2 class="text-3xl font-bold text-gray-800 mb-3 md:mb-0">Inventory</h2>
            <a href="add.php"
            class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-2xl font-semibold shadow-lg transition transform hover:scale-105">
                Add Item
            </a>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
            <table class="min-w-full text-gray-700">
                <thead class="bg-gradient-to-r from-indigo-200 via-purple-200 to-pink-200 text-gray-800 uppercase text-sm tracking-wide">
                    <tr>
                        <th class="p-4 border-b">ID</th>
                        <th class="p-4 border-b">Item Name</th>
                        <th class="p-4 border-b">Description</th>
                        <th class="p-4 border-b">Model No.</th>
                        <th class="p-4 border-b">Serial No.</th>
                        <th class="p-4 border-b">Remarks</th>
                        <th class="p-4 border-b">Location</th>
                        <th class="p-4 border-b">Laboratory</th>
                        <th class="p-4 border-b">Date Added</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="bg-white hover:bg-blue-50 transition transform hover:scale-[1.01] cursor-pointer">
                        <td class="p-4 border-b"><?= $row['item_id'] ?></td>
                        <td class="p-4 border-b font-medium"><?= $row['item_name'] ?></td>
                        <td class="p-4 border-b"><?= $row['description'] ?></td>
                        <td class="p-4 border-b"><?= $row['model_no'] ?></td>
                        <td class="p-4 border-b"><?= $row['serial_no'] ?></td>
                        <td class="p-4 border-b"><?= $row['remarks'] ?></td>
                        <td class="p-4 border-b"><?= $row['location_name'] ?></td>
                        <td class="p-4 border-b"><?= $row['lab_name'] ?></td>
                        <td class="p-4 border-b"><?= $row['date_added'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>               
    </div>

</div>

<?php include "../partials/footer.php"; ?>

<link rel="stylesheet" href="../assets/style.css">