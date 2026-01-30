<?php
session_start();
require_once '../admin/config.php';

// Initialize variables for the UI
$order_processed = false;
$order_id = "";
$error_message = "";

// 1. Pre-Flight Checks
if (!isset($_SESSION['accountID'])) {
    header("Location: ../login/index.php"); // Redirect to login if not logged in
    exit();
}

// Only process if the cart isn't empty
if (!empty($_SESSION['cart'])) {
    $accountID = $_SESSION['accountID'];
    $purchaseDate = date('Y-m-d H:i:s');

    // 2. Start Database Transaction
    $conn->begin_transaction();

    try {
        // 3. Retrieve Payment ID
        $payQuery = "SELECT p.paymentID FROM paymentInfo p
                     INNER JOIN userinfo u ON p.userID = u.userID 
                     WHERE u.accountID = ? LIMIT 1";
        
        $payStmt = $conn->prepare($payQuery);
        $payStmt->bind_param("i", $accountID);
        $payStmt->execute();
        $payRes = $payStmt->get_result();
        $payData = $payRes->fetch_assoc();

        if (!$payData) {
            throw new Exception("Missing billing information. Please update your profile.");
        }
        $paymentID = $payData['paymentID'];

        // 4. Prepare the Insertion Query
        $insertSQL = "INSERT INTO booking (accountID, spotID, paymentID, purchaseDate, checkinDate, checkoutDate, unit, totalPrice) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertSQL);

        // 5. Loop through Cart and Execute
        foreach ($_SESSION['cart'] as $spotID => $item) {
            $qty = (int)$item['quantity'];
            $cleanPrice = (float)str_replace(['$', ','], '', $item['price']);
            $subtotal = $cleanPrice * $qty;
            
            $checkin = date('Y-m-d', strtotime($item['checkinDate']));
            $checkout = date('Y-m-d', strtotime($item['checkoutDate']));

            $stmt->bind_param("iiisssid", 
                $accountID, 
                $spotID, 
                $paymentID, 
                $purchaseDate, 
                $checkin, 
                $checkout, 
                $qty, 
                $subtotal
            );

            if (!$stmt->execute()) {
                throw new Exception("Error processing item: " . $item['name']);
            }
        }

        // 6. Finalize
        $conn->commit();
        unset($_SESSION['cart']); // Clear cart
        
        $order_processed = true;
        $order_id = uniqid('BM-');

    } catch (Exception $e) {
        $conn->rollback();
        $order_processed = false;
        $error_message = $e->getMessage();
    }
} else {
    // If cart is empty and not already processed, send back
    if (!$order_processed) {
        header("Location: checkOut.php?error=empty_cart");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed | BookingMaster</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: { brand: '#3B82F6', dark: '#0F172A' }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-6">

    <div class="max-w-xl w-full bg-white p-12 rounded-[3.5rem] shadow-2xl text-center border border-slate-100 animate-in zoom-in duration-500">
        
        <?php if ($order_processed): ?>
            <div class="w-24 h-24 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-8">
                <i class="bi bi-check-lg text-5xl"></i>
            </div>
            <h1 class="text-4xl font-black text-dark mb-4 tracking-tight">Booking Confirmed!</h1>
            <p class="text-slate-500 mb-8 text-lg font-medium leading-relaxed">
                Pack your bags! Your trip reference is <span class="text-brand font-bold"><?= $order_id ?></span>. 
                You can view your itinerary in the dashboard.
            </p>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <a href="../index.php" class="bg-brand text-white py-4 rounded-2xl font-bold hover:bg-blue-600 transition shadow-lg shadow-blue-100 flex items-center justify-center gap-2">
                    <i class="bi bi-house-door"></i> Return Home
                </a>
                <a href="userDashboard.php" class="bg-dark text-white py-4 rounded-2xl font-bold hover:bg-slate-800 transition shadow-lg flex items-center justify-center gap-2">
                    <i class="bi bi-grid-1x2"></i> My Dashboard
                </a>
            </div>

        <?php else: ?>
            <div class="w-24 h-24 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-8">
                <i class="bi bi-exclamation-triangle text-5xl"></i>
            </div>
            <h1 class="text-3xl font-black text-dark mb-4">Transaction Failed</h1>
            <p class="text-slate-500 mb-8"><?= htmlspecialchars($error_message ?? "An unexpected error occurred.") ?></p>
            
            <a href="cart.php" class="inline-block bg-slate-100 text-slate-600 px-10 py-4 rounded-2xl font-bold hover:bg-slate-200 transition">
                Return to Cart
            </a>
        <?php endif; ?>

    </div>

</body>
</html>