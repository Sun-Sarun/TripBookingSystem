<?php
session_start();
require_once '../admin/config.php'; 

// --- 1. REMOVE FUNCTION LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_id'])) {
    $removeID = $_POST['remove_id'];
    if (isset($_SESSION['cart'][$removeID])) {
        unset($_SESSION['cart'][$removeID]);
    }
    header("Location: checkoutComplete.php");
    exit();
}

// --- 2. INITIALIZE & CALCULATE ---
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0;

foreach ($cart as $item) {
    // SECURITY: Clean the price to ensure it's a number (strips '$' and ',')
    $cleanPrice = str_replace(['$', ','], '', $item['price']);
    $itemPrice = (float)$cleanPrice;
    $itemQty = (int)($item['quantity'] ?? 1);

    $subtotal += ($itemPrice * $itemQty);
}

$serviceFee = ($subtotal > 0) ? 45.00 : 0;
$total = $subtotal + $serviceFee;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookingMaster | Review Order</title>
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

    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-200">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-extrabold text-brand tracking-tighter">BookingMaster</a>
            <a href="viewAllProduct.php" class="font-bold text-slate-600 hover:text-brand transition text-sm">Add More</a>
        </div>
    </nav>

    <main class="container mx-auto px-6 py-12">
        <h1 class="text-4xl font-extrabold mb-10 tracking-tight">Review Your Booking</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <div class="lg:col-span-2 space-y-6">
                <?php if (empty($cart)): ?>
                    <div class="bg-white rounded-[2.5rem] p-20 text-center border-2 border-dashed border-slate-200">
                        <i class="bi bi-cart-x text-5xl text-slate-300 mb-4 block"></i>
                        <h2 class="text-2xl font-bold text-slate-700">Your cart is empty</h2>
                        <a href="viewAllProduct.php" class="text-brand font-bold mt-4 inline-block hover:underline">Find an adventure →</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($cart as $id => $item): ?>
                    <div class="bg-white rounded-[2.5rem] p-6 flex flex-col md:flex-row gap-6 shadow-sm border border-slate-100">
                        <div class="w-full md:w-48 h-36 rounded-3xl overflow-hidden shrink-0">
                            <?php 
                                $photo = $item['photo'];
                                $imageSrc = (filter_var($photo, FILTER_VALIDATE_URL)) ? $photo : "../database/imgs/" . htmlspecialchars($photo);
                            ?>
                            <img src="<?= $imageSrc ?>" class="w-full h-full object-cover">
                        </div>

                        <div class="flex-1 flex flex-col justify-between">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-xl font-bold"><?= htmlspecialchars($item['name']) ?></h3>
                                    <p class="text-slate-400 text-sm font-medium">
                                        <i class="bi bi-calendar-event mr-2"></i>
                                        <?= htmlspecialchars($item['checkinDate']) ?> to <?= htmlspecialchars($item['checkoutDate']) ?>
                                    </p>
                                </div>
                                <form action="" method="POST">
                                    <input type="hidden" name="remove_id" value="<?= $id ?>">
                                    <button type="submit" class="text-slate-300 hover:text-red-500 transition text-xl">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>

                            <div class="flex justify-between items-end mt-4">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                                    Unit: <?= $item['quantity'] ?> Travelers
                                </span>
                                <span class="text-2xl font-black text-slate-900">
                                    $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-2xl sticky top-32">
                    <h2 class="text-2xl font-extrabold mb-8 text-dark">Summary</h2>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between text-slate-500 font-semibold">
                            <span>Subtotal</span>
                            <span class="text-dark">$<?= number_format($subtotal, 2) ?></span>
                        </div>
                        <div class="flex justify-between text-slate-500 font-semibold">
                            <span>Service Fee</span>
                            <span class="text-dark">$<?= number_format($serviceFee, 2) ?></span>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-6 mb-8">
                        <div class="flex justify-between text-slate-900 font-black text-3xl">
                            <span>Total</span>
                            <span class="text-brand">$<?= number_format($total, 2) ?></span>
                        </div>
                    </div>

                    <form action="checkoutComplete.php" method="POST">
                        <button type="submit" <?= empty($cart) ? 'disabled' : '' ?> 
                                class="w-full bg-brand text-white py-4 rounded-2xl font-bold text-lg hover:bg-blue-600 transition-all shadow-lg shadow-blue-100 disabled:opacity-50">
                            Confirm & Pay Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

</body>
</html>