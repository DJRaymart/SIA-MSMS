<?php
include "../config/db.php";
include "../partials/header.php";

$locations = $conn->query("SELECT location_id, location_name FROM locations");
$labs = $conn->query("SELECT lab_id, lab_name FROM labs");

$alert = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stmt = $conn->prepare("
        INSERT INTO inventory
        (item_name, description, model_no, serial_no, date_added, remarks, location_id, lab_id)
        VALUES (?,?,?,?,CURDATE(),?,?,?)
    ");

    $stmt->bind_param(
        "ssssssi",
        $_POST['item_name'],
        $_POST['description'],
        $_POST['model_no'],
        $_POST['serial_no'],
        $_POST['remarks'],
        $_POST['location_id'],
        $_POST['lab_id']
    );

    if ($stmt->execute()) {
        $alert = ["type" => "success", "message" => "Inventory item added successfully."];
    } else {
        $alert = ["type" => "error", "message" => "Failed to add inventory item. Please try again."];
    }
}
?>

<div class="relative min-h-screen w-full overflow-hidden">

    <!-- ALERT CONTAINER -->
    <div id="alertContainer"
        class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 w-[360px]">
    </div>

    <!-- Background layers -->
    <div class="fixed inset-0 -z-30">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-blue-100 to-green-50"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%)] animate-pulse-slow"></div>
        <div class="absolute inset-0 bg-[url('/images/lab-pattern.svg')] bg-repeat opacity-10"></div>
    </div>

    <!-- Floating lab objects -->
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

    <!-- Molecules -->
    <div class="fixed inset-0 pointer-events-none -z-10">
        <?php for ($i = 0; $i < 30; $i++): ?>
            <div class="absolute w-3 h-3 rounded-full animate-molecule-gradient"
                style="top:<?= rand(0, 100) ?>%; left:<?= rand(0, 100) ?>%; animation-duration:<?= rand(6, 15) ?>s; animation-delay:<?= rand(0, 5) ?>s;">
            </div>
        <?php endfor; ?>
    </div>

    <!-- Main content -->
    <div class="relative z-30 flex justify-center items-start py-20 px-4 min-h-screen">
        <div class="w-full max-w-md bg-white/90 backdrop-blur-lg p-10 rounded-3xl shadow-2xl border border-white/30 transition-all duration-500 hover:shadow-3xl">

            <div class="flex flex-col items-center mb-8">
                <!-- <img src="../assets/images/inventory-icon.png" alt="Inventory" class="w-16 h-16 mb-4 bounce-gradient"> -->
                <h2 class="text-3xl font-bold text-gray-800 text-center bounce-gradient-text">Add Inventory Item</h2>
                <p class="text-gray-500 mt-2 text-center text-sm md:text-base">Fill out the form below to add a new inventory item</p>
            </div>

            <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <input name="item_name" required placeholder="Item Name"
                    class="col-span-2 w-full p-4 rounded-xl border border-gray-300 shadow-sm
                        focus:outline-none focus:ring-2 focus:ring-blue-500 transition">

                <textarea name="description" placeholder="Description" class="col-span-2 w-full p-4 rounded-xl border border-gray-300 shadow-sm
                        focus:outline-none focus:ring-2 focus:ring-blue-500 transition"></textarea>

                <input name="model_no" placeholder="Model No" class="w-full p-4 rounded-xl border border-gray-300 shadow-sm
                    focus:outline-none focus:ring-2 focus:ring-blue-500 transition">

                <input name="serial_no" placeholder="Serial No" class="w-full p-4 rounded-xl border border-gray-300 shadow-sm
                    focus:outline-none focus:ring-2 focus:ring-blue-500 transition">

                <textarea name="remarks" placeholder="Remarks" class="col-span-2 w-full p-4 rounded-xl border border-gray-300 shadow-sm
                        focus:outline-none focus:ring-2 focus:ring-blue-500 transition"></textarea>

                <select name="location_id" required class="w-full p-4 rounded-xl border border-gray-300 shadow-sm transition focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Location</option>
                    <?php while ($loc = $locations->fetch_assoc()): ?>
                        <option value="<?= $loc['location_id'] ?>"><?= htmlspecialchars($loc['location_name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <select name="lab_id" required class="w-full p-4 rounded-xl border border-gray-300 shadow-sm transition focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Laboratory</option>
                    <?php while ($lab = $labs->fetch_assoc()): ?>
                        <option value="<?= $lab['lab_id'] ?>"><?= htmlspecialchars($lab['lab_name']) ?></option>
                    <?php endwhile; ?>
                </select>

                <button type="submit" class="col-span-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 rounded-xl text-lg w-full
                        transition transform hover:scale-105 shadow-lg">
                    Save Item
                </button>

            </form>
        </div>
    </div>

</div>

<!-- Global Alert Container -->
<div id="globalAlert" class="fixed top-5 left-1/2 -translate-x-1/2 z-50"></div>

<link rel="stylesheet" href="../assets/style.css">
<script src="../assets/js/global-alert.js"></script>

<?php if ($alert): ?>
    <script>
        // Correctly call showAlert: type, title, message
        showAlert(
            "<?= $alert['type'] === 'error' ? 'danger' : $alert['type'] ?>", // type: success/danger
            "<?= $alert['type'] === 'error' ? 'Failed!' : 'Success!' ?>",   // title
            "<?= htmlspecialchars($alert['message'], ENT_QUOTES) ?>",        // message
            7000                                                            // duration in ms
        );
    </script>
<?php endif; ?>

<?php include "../partials/footer.php"; ?>