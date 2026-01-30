<?php
session_start();
// 1. Database Connection
require_once '../admin/config.php'; 

// --- BOOKING LOGIC: Process form submission within the same file ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['spotID'])) {
    $sID = mysqli_real_escape_string($conn, $_POST['spotID']);

    // Fetch details for the session cart - Updated with JOIN for new schema
    $cartQuery = "SELECT spot.*, address.province 
                  FROM spot 
                  INNER JOIN address ON spot.addressID = address.addressID 
                  WHERE spot.spotID = '$sID'";
    $cartResult = mysqli_query($conn, $cartQuery);
    $cartItem = mysqli_fetch_assoc($cartResult);

    if ($cartItem) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Add/Update item in session cart including parameters for the booking table
        $_SESSION['cart'][$sID] = [
            'name' => $cartItem['name'],
            'price' => $cartItem['price'],
            'photo' => $cartItem['photo'],
            'province' => $cartItem['province'],
            'quantity' => $_POST['unit'] ?? 1,
            'checkinDate' => $_POST['checkinDate'] ?? null,
            'checkoutDate' => $_POST['checkoutDate'] ?? null
        ];
    }
    header("Location: checkOut.php");
    exit();
}

// 2. FETCH DATA FOR DISPLAY: Updated Query for New Database Schema
if (isset($_GET['id'])) {
    $spotID = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Joined with address table to get location details
    $query = "SELECT spot.*, address.country, address.province, address.district 
              FROM spot 
              INNER JOIN address ON spot.addressID = address.addressID 
              WHERE spot.spotID = '$spotID'";
              
    $result = mysqli_query($conn, $query);
    $spot = mysqli_fetch_assoc($result);

    if (!$spot) {
        header("Location: viewAllProduct.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

// Image handling logic
$photo = $spot['photo'];
$imageSrc = (filter_var($photo, FILTER_VALIDATE_URL)) ? $photo : "../database/imgs/" . htmlspecialchars($photo);

// Availability Status Logic
$status = htmlspecialchars($spot['status'] ?? 'Available');
$isAvailable = (strtolower($status) === 'available');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookingMaster | <?= htmlspecialchars($spot['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: '#3B82F6', dark: '#0F172A' }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-900">

    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-gray-200">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="viewAllProduct.php" class="text-slate-600 font-bold hover:text-brand transition">
                <i class="bi bi-arrow-left"></i> Back to Destinations
            </a>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest"><?= htmlspecialchars($spot['type']) ?></span>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-32">
        <div class="max-w-6xl mx-auto">
            
            <div class="mb-8">
                <h1 class="text-4xl md:text-5xl font-extrabold mb-2 tracking-tight"><?= htmlspecialchars($spot['name']) ?></h1>
                <p class="text-slate-500 flex items-center text-lg">
                    <i class="bi bi-geo-alt-fill text-brand mr-2"></i> 
                    <?= htmlspecialchars($spot['district']) ?>, <?= htmlspecialchars($spot['province']) ?>, <?= htmlspecialchars($spot['country']) ?>
                </p>
            </div>

            <div class="rounded-[2.5rem] overflow-hidden shadow-2xl mb-12 h-[500px]">
                <img src="<?= $imageSrc ?>" class="w-full h-full object-cover" alt="<?= htmlspecialchars($spot['name']) ?>">
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <div class="lg:col-span-2">
                    <h2 class="text-2xl font-extrabold mb-4">About this destination</h2>
                    <p class="text-slate-600 leading-relaxed text-lg mb-8">
                        <?= nl2br(htmlspecialchars($spot['detail'])) ?>
                    </p>

                    <div class="grid grid-cols-2 gap-6 border-t border-slate-200 pt-8">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase mb-1">Support</p>
                            <p class="font-bold"><?= htmlspecialchars($spot['phone'] ?? 'N/A') ?></p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase mb-1">Status</p>
                            <p class="font-bold <?= $isAvailable ? 'text-green-600' : 'text-red-600' ?>">
                                <?= $status ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-white p-8 rounded-[2rem] shadow-xl border border-slate-100 sticky top-32">
                        <div class="mb-8">
                            <span class="text-4xl font-extrabold text-slate-900">$<?= number_format($spot['price'], 2) ?></span>
                            <span class="text-slate-400 font-bold">/ person</span>
                        </div>

                        <?php if ($isAvailable): ?>
                            <form action="" method="POST" class="space-y-4">
                                <input type="hidden" name="spotID" value="<?= $spot['spotID'] ?>">
                                
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Check-in</label>
                                        <input type="date" name="checkinDate" required class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Check-out</label>
                                        <input type="date" name="checkoutDate" required class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Travelers</label>
                                        <input type="number" name="unit" value="1" min="1" required class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand">
                                    </div>
                                </div>

                                <button type="submit" class="w-full bg-brand text-white py-4 mt-4 rounded-2xl font-bold text-lg hover:bg-blue-600 transition shadow-lg shadow-blue-100">
                                    Proceed to Checkout
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="bg-red-50 text-red-700 p-4 rounded-xl text-center font-bold">
                                <i class="bi bi-calendar-x mr-2"></i> Currently Unvailable
                            </div>
                            <button disabled class="w-full bg-slate-200 text-slate-400 py-4 mt-4 rounded-2xl font-bold text-lg cursor-not-allowed">
                                Booking Closed
                            </button>
                        <?php endif; ?>
                        
                        <p class="text-center text-slate-400 text-[10px] font-bold uppercase tracking-widest mt-6">
                            <i class="bi bi-shield-lock-fill mr-1"></i> Secure Booking
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>