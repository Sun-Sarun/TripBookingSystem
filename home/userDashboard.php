<?php
session_start();
require_once '../admin/config.php'; 

if (!isset($_SESSION['accountID'])) {
    header("Location: ../profile/login/index.php");
    exit();
}

$accID = $_SESSION['accountID'];
$message = "";

// 1. INITIAL FETCH (To get userID for updates)
$initialUser = mysqli_fetch_assoc(mysqli_query($conn, "SELECT userID FROM userinfo WHERE accountID = '$accID'"));
$userID = $initialUser['userID'];

// 2. HANDLE UPDATES (POST REQUESTS)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Logic for Profile Update
    if (isset($_POST['update_profile'])) {
        $fname = mysqli_real_escape_string($conn, $_POST['FName']);
        $lname = mysqli_real_escape_string($conn, $_POST['LName']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);

        $profileName = $_POST['current_profile']; 
        if (!empty($_FILES['profile_img']['name'])) {
            $targetDir = "../database/imgs/"; 
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            $profileName = time() . "_" . basename($_FILES['profile_img']['name']);
            move_uploaded_file($_FILES['profile_img']['tmp_name'], $targetDir . $profileName);
        }

        $sql = "UPDATE userinfo SET FName='$fname', LName='$lname', phone='$phone', address='$address', profile='$profileName' WHERE accountID='$accID'";
        if (mysqli_query($conn, $sql)) {
            $message = "Profile updated successfully.";
        }
    }

    // Logic for Payment Update
    if (isset($_POST['update_payment'])) {
        $uID = mysqli_real_escape_string($conn, $_POST['userID']);
        $pType = mysqli_real_escape_string($conn, $_POST['paymentType']);
        $cCode = mysqli_real_escape_string($conn, $_POST['cardCode']);
        $exp = mysqli_real_escape_string($conn, $_POST['expireDate']);
        $cvv = mysqli_real_escape_string($conn, $_POST['cvv']);

        $checkPayment = mysqli_query($conn, "SELECT paymentID FROM paymentInfo WHERE userID = '$uID'");
        
        if (mysqli_num_rows($checkPayment) > 0) {
            $paySql = "UPDATE paymentInfo SET paymentType='$pType', cardCode='$cCode', expireDate='$exp', cvv='$cvv' WHERE userID='$uID'";
        } else {
            $paySql = "INSERT INTO paymentInfo (userID, paymentType, cardCode, expireDate, cvv) VALUES ('$uID', '$pType', '$cCode', '$exp', '$cvv')";
        }

        if (mysqli_query($conn, $paySql)) {
            $message = "Payment method synchronized successfully.";
        } else {
            $message = "Error updating payment: " . mysqli_error($conn);
        }
    }
}

// 3. FINAL DATA RETRIEVAL (Fetch fresh data for display)
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM userinfo WHERE accountID = '$accID'"));
$payment = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM paymentInfo WHERE userID = '$userID'"));

// Statistics
$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count, SUM(totalPrice) as total FROM booking WHERE accountID = '$accID'"));
$totalBookings = $stats['count'] ?? 0;
$totalSpent = $stats['total'] ?? 0.00;

// Last Activity
$lastBooking = mysqli_fetch_assoc(mysqli_query($conn, "SELECT purchaseDate FROM booking WHERE accountID = '$accID' ORDER BY purchaseDate DESC LIMIT 1"));
$lastDate = $lastBooking['purchaseDate'] ?? 'No activity';

// Booking History
$bookingResult = mysqli_query($conn, "SELECT b.*, s.name, s.photo, a.province, a.street, a.houseNumber 
                 FROM booking b 
                 JOIN spot s ON b.spotID = s.spotID
                 JOIN address a ON s.addressID = a.addressID
                 WHERE b.accountID = '$accID' ORDER BY b.purchaseDate DESC");

// Profile Picture logic
// Define a default placeholder first
$profilePic = 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';

if (!empty($user['profile'])) {
    // Check if the database value is a full URL
    if (filter_var($user['profile'], FILTER_VALIDATE_URL)) {
        $profilePic = $user['profile'];
    } else {
        // If it's a filename, check multiple possible local paths
        $paths = [
            "../database/imgs/" . $user['profile'], 
            "../database/imgs/" . $user['profile']
        ];
        
        foreach ($paths as $p) {
            if (file_exists($p)) {
                $profilePic = $p;
                break; 
            }
        }
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | BookingMaster</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] },
                    colors: { brand: '#3B82F6', dark: '#0F172A', slateBg: '#F8FAFC' }
                }
            }
        }
        function navigateTo(tabId) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
            document.getElementById(tabId).classList.remove('hidden');
            document.querySelectorAll('.nav-link').forEach(l => {
                l.classList.remove('bg-white/10', 'text-white');
                l.classList.add('text-slate-400');
            });
            event.currentTarget.classList.add('bg-white/10', 'text-white');
        }
    </script>
</head>
<body class="bg-slateBg font-sans antialiased text-slate-900">

    <div class="flex min-h-screen">
        <aside class="w-72 bg-dark text-slate-400 p-8 hidden lg:flex flex-col fixed h-full z-50">
            <a href="index.php">
                <div class="flex items-center gap-3 text-white text-2xl font-extrabold mb-12">
                    <div class="bg-brand p-2 rounded-xl"><i class="bi bi-geo-alt-fill"></i></div>
                    <span>BookingMaster</span>
                </div>
            </a>
            <nav class="flex-1 space-y-2">
                <button onclick="navigateTo('tab-overview')" class="nav-link w-full flex items-center gap-4 px-5 py-3.5 rounded-2xl bg-white/10 text-white font-semibold">
                    <i class="bi bi-grid-1x2"></i><span>Overview</span>
                </button>
                <button onclick="navigateTo('tab-bookings')" class="nav-link w-full flex items-center gap-4 px-5 py-3.5 rounded-2xl hover:bg-white/5 font-semibold">
                    <i class="bi bi-journal-bookmark"></i><span>Bookings</span>
                </button>
                <button onclick="navigateTo('tab-profile')" class="nav-link w-full flex items-center gap-4 px-5 py-3.5 rounded-2xl hover:bg-white/5 font-semibold">
                    <i class="bi bi-person-circle"></i><span>Profile</span>
                </button>
                <button onclick="navigateTo('tab-payment')" class="nav-link w-full flex items-center gap-4 px-5 py-3.5 rounded-2xl hover:bg-white/5 font-semibold">
                    <i class="bi bi-credit-card"></i><span>Billing</span>
                </button>
            </nav>
            <a href="../profile/login/index.php" class="flex items-center gap-4 px-5 py-3 text-red-400 font-bold hover:bg-red-500/10 rounded-xl">
                <i class="bi bi-power"></i><span>Logout</span>
            </a>
        </aside>

        <main class="flex-1 ml-0 lg:ml-72 p-6 lg:p-12">
            
            <?php if($message): ?>
                <div class="mb-8 p-4 bg-brand text-white rounded-2xl font-bold shadow-lg flex items-center gap-3">
                    <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div id="tab-overview" class="tab-content space-y-8">
                <div class="flex justify-between items-end">
                    <div>
                        <p class="text-slate-400 font-bold text-sm uppercase tracking-widest">Dashboard</p>
                        <h1 class="text-4xl font-black">Welcome, <?= htmlspecialchars($user['FName']) ?></h1>
                    </div>
                    <img src="<?= $profilePic ?>" 
     class="w-16 h-16 rounded-2xl object-cover border-4 border-white shadow-md"
     alt="User Profile"
     onerror="this.src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png'">
                    
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-8 rounded-[2rem] border shadow-sm">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Total Spent</p>
                        <h3 class="text-3xl font-black text-brand">$<?= number_format($totalSpent, 2) ?></h3>
                    </div>
                    <div class="bg-white p-8 rounded-[2rem] border shadow-sm">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Trips</p>
                        <h3 class="text-3xl font-black"><?= $totalBookings ?></h3>
                    </div>
                    <div class="bg-white p-8 rounded-[2rem] border shadow-sm">
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Last Activity</p>
                        <h3 class="text-sm font-bold"><?= $lastDate ?></h3>
                    </div>
                </div>

                <div class="bg-slate-900 rounded-[3rem] p-12 text-white flex justify-between items-center relative overflow-hidden group">
                    <div class="z-10">
                        <h2 class="text-3xl font-bold mb-2">Explore New Places</h2>
                        <p class="text-slate-400 mb-8">Ready for your next trip? Thousands of spots are waiting.</p>
                        <a href="../index.php" class="bg-brand px-8 py-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-600 transition">Book New Spot</a>
                    </div>
                    <i class="bi bi-airplane-engines absolute -right-10 -bottom-10 text-[15rem] text-white/5 -rotate-12 transition-transform group-hover:scale-110"></i>
                </div>
            </div>

            <div id="tab-bookings" class="tab-content hidden space-y-6">
                <h2 class="text-2xl font-black mb-8">My History</h2>
                <?php if(mysqli_num_rows($bookingResult) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($bookingResult)): ?>
                        <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 flex flex-col md:flex-row gap-8 hover:shadow-xl transition-all group">
                            <div class="md:w-56 h-40 relative flex-shrink-0">
                                <img src="<?= (filter_var($row['photo'], FILTER_VALIDATE_URL)) ? $row['photo'] : (file_exists('../database/imgs/'.$row['photo']) ? '../database/imgs/'.$row['photo'] : '../database/imgs/'.$row['photo']) ?>"
                                     class="w-full h-full object-cover rounded-[1.5rem] bg-slate-100"
                                     onerror="this.src='https://placehold.co/600x400?text=No+Image'">
                                <div class="absolute top-3 left-3 bg-white/90 backdrop-blur px-3 py-1 rounded-lg text-[10px] font-black text-brand uppercase tracking-tighter">Stay</div>
                            </div>
                            <div class="flex-1 py-2">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h4 class="text-xl font-extrabold text-slate-900"><?= htmlspecialchars($row['name']) ?></h4>
                                        <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-tighter">
                                            <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($row['houseNumber'] . " " . $row['street'] . ", " . $row['province']) ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] font-black text-slate-300 block uppercase">Reference</span>
                                        <span class="text-xs font-mono font-bold">#BK-<?= sprintf('%04d', $row['bookingID']) ?></span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-6 pt-6 border-t border-slate-50">
                                    <div><p class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-1">Check In</p><p class="text-sm font-bold"><?= date('M d, Y', strtotime($row['checkinDate'])) ?></p></div>
                                    <div><p class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-1">Units</p><p class="text-sm font-bold"><?= $row['unit'] ?> Pax</p></div>
                                    <div><p class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-1">Paid Amount</p><p class="text-sm font-black text-brand">$<?= number_format($row['totalPrice'], 2) ?></p></div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="bg-white p-20 rounded-[3rem] text-center border-2 border-dashed border-slate-200">
                        <i class="bi bi-journal-x text-5xl text-slate-200 mb-4 block"></i>
                        <p class="text-slate-400 font-bold uppercase tracking-widest">No Bookings Found</p>
                    </div>
                <?php endif; ?>
            </div>

            <div id="tab-profile" class="tab-content hidden">
                <form action="" method="POST" enctype="multipart/form-data" class="bg-white p-12 rounded-[3rem] border shadow-sm max-w-4xl">
                    <h2 class="text-2xl font-black mb-10">Profile Settings</h2>
                    <div class="flex items-center gap-10 mb-12">
                        <img src="<?= $profilePic ?>" 
     class="w-16 h-16 rounded-2xl object-cover border-4 border-white shadow-md"
     alt="User Profile"
     onerror="this.src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png'">
                        <div>
                            <input type="file" name="profile_img" class="text-xs font-bold text-slate-400 mb-2 block">
                            <input type="hidden" name="current_profile" value="<?= $user['profile'] ?>">
                            <p class="text-[10px] text-slate-400 uppercase font-bold tracking-widest">JPG or PNG. Max 2MB</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label class="text-[10px] font-black text-slate-300 uppercase mb-2 block">First Name</label><input type="text" name="FName" value="<?= htmlspecialchars($user['FName']) ?>" class="w-full bg-slate-50 border-none rounded-2xl p-4 font-bold"></div>
                        <div><label class="text-[10px] font-black text-slate-300 uppercase mb-2 block">Last Name</label><input type="text" name="LName" value="<?= htmlspecialchars($user['LName']) ?>" class="w-full bg-slate-50 border-none rounded-2xl p-4 font-bold"></div>
                        <div class="col-span-2"><label class="text-[10px] font-black text-slate-300 uppercase mb-2 block">Address</label><textarea name="address" rows="3" class="w-full bg-slate-50 border-none rounded-2xl p-4 font-bold"><?= htmlspecialchars($user['address']) ?></textarea></div>
                        <div class="col-span-2"><label class="text-[10px] font-black text-slate-300 uppercase mb-2 block">Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" class="w-full bg-slate-50 border-none rounded-2xl p-4 font-bold"></div>
                    </div>
                    <button type="submit" name="update_profile" class="mt-10 bg-dark text-white px-12 py-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-800 transition">Save Changes</button>
                </form>
            </div>

            <div id="tab-payment" class="tab-content hidden space-y-10">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <div class="space-y-6">
                        <h2 class="text-2xl font-black italic">Payment Method</h2>
                        <div class="relative h-56 w-full rounded-[2.5rem] bg-slate-900 p-8 text-white shadow-2xl overflow-hidden group">
                            <div class="relative z-10 h-full flex flex-col justify-between">
                                <div class="flex justify-between items-start">
                                    <i class="bi bi-chip text-4xl text-yellow-500/80"></i>
                                    <h4 class="font-black italic text-xl"><?= htmlspecialchars($payment['paymentType'] ?? 'Card') ?></h4>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500 font-bold">Card Number</p>
                                    <p class="text-2xl font-mono tracking-widest">
                                        <?= !empty($payment['cardCode']) ? '•••• •••• •••• ' . substr($payment['cardCode'], -4) : '•••• •••• •••• ••••' ?>
                                    </p>
                                </div>
                                <div class="flex justify-between items-end">
                                    <div>
                                        <p class="text-[10px] uppercase text-slate-500 font-bold">Card Holder</p>
                                        <p class="font-bold uppercase"><?= htmlspecialchars($user['FName'] . ' ' . $user['LName']) ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] uppercase text-slate-500 font-bold">Expires</p>
                                        <p class="font-bold"><?= !empty($payment['expireDate']) ? date('m/y', strtotime($payment['expireDate'])) : 'MM/YY' ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-blue-600/10 transition-transform duration-700"></div>
                        </div>

                        <form action="" method="POST" class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm space-y-5">
                            <input type="hidden" name="userID" value="<?= $userID ?>">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="col-span-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block ml-2">Provider</label>
                                    <select name="paymentType" class="w-full bg-slate-50 border-none rounded-2xl p-4 font-bold focus:ring-2 focus:ring-brand">
                                        <option value="Visa" <?= (($payment['paymentType']??'') == 'Visa' ? 'selected' : '') ?>>Visa</option>
                                        <option value="Mastercard" <?= (($payment['paymentType']??'') == 'Mastercard' ? 'selected' : '') ?>>Mastercard</option>
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block ml-2">Card Number</label>
                                    <input type="text" name="cardCode" maxlength="16" placeholder="0000 0000 0000 0000" value="<?= htmlspecialchars($payment['cardCode']??'') ?>" class="w-full bg-slate-50 border-none rounded-2xl p-4 font-black tracking-widest">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block ml-2">Expiry Date</label>
                                    <input type="date" name="expireDate" value="<?= $payment['expireDate']??'' ?>" class="w-full bg-slate-50 border-none rounded-2xl p-4 font-bold">
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block ml-2">CVV</label>
                                    <input type="password" name="cvv" maxlength="3" placeholder="***" value="<?= htmlspecialchars($payment['cvv']??'') ?>" class="w-full bg-slate-50 border-none rounded-2xl p-4 font-bold">
                                </div>
                            </div>
                            <button type="submit" name="update_payment" class="w-full bg-brand text-white py-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-blue-600 transition shadow-lg mt-4">
                                Update Payment Method
                            </button>
                        </form>
                    </div>

                    <div class="space-y-6">
                        <h2 class="text-2xl font-black italic">Billing Security</h2>
                        <div class="bg-blue-600 rounded-[2.5rem] p-10 text-white shadow-xl relative overflow-hidden">
                            <div class="relative z-10">
                                <div class="h-12 w-12 bg-white/20 rounded-xl flex items-center justify-center mb-6">
                                    <i class="bi bi-shield-lock-fill text-2xl"></i>
                                </div>
                                <h3 class="text-xl font-bold mb-2">Encrypted Transactions</h3>
                                <p class="text-blue-100 text-sm leading-relaxed">
                                    Your payment security is our priority. We use 256-bit SSL encryption to ensure that your credit card details are handled safely.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>