<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../config.php'; 

// 1. SECURITY: Only allow admins
if (!isset($_SESSION['accountID']) || $_SESSION['permission'] !== 'admin') {
    header("Location: ../login/index.php");
    exit();
}

$message = "";
$targetDir = "../../database/imgs/"; 

// --- ACTION: DELETE USER ---
if (isset($_GET['delete_user'])) {
    $targetAccID = mysqli_real_escape_string($conn, $_GET['delete_user']);
    $delUser = "DELETE FROM account WHERE accountID = '$targetAccID'";
    if (mysqli_query($conn, $delUser)) {
        header("Location: dashboard.php?msg=user_deleted");
        exit();
    }
}

// --- ACTION: UPDATE USER PROFILE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_update_user'])) {
    $uID = mysqli_real_escape_string($conn, $_POST['targetUserID']);
    $aID = mysqli_real_escape_string($conn, $_POST['targetAccountID']);
    $fName = mysqli_real_escape_string($conn, $_POST['fName']);
    $lName = mysqli_real_escape_string($conn, $_POST['lName']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $perm = mysqli_real_escape_string($conn, $_POST['permission']);

    $q1 = "UPDATE userinfo SET FName='$fName', LName='$lName' WHERE userID='$uID'";
    $q2 = "UPDATE account SET email='$email', permission='$perm' WHERE accountID='$aID'";

    if (mysqli_query($conn, $q1) && mysqli_query($conn, $q2)) {
        $message = "User record updated successfully.";
    }
}

// --- ACTION: ADD NEW SPOT ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_new_spot'])) {
    $photoName = "default.jpg";
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photoName = time() . '_' . uniqid() . '.' . $extension;
        move_uploaded_file($_FILES['photo']['tmp_name'], $targetDir . $photoName);
    }

    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $district = mysqli_real_escape_string($conn, $_POST['district']);
    $street = mysqli_real_escape_string($conn, $_POST['street']);
    $houseNumber = mysqli_real_escape_string($conn, $_POST['houseNumber']);
    
    $addrSql = "INSERT INTO address (country, province, district, street, houseNumber) VALUES ('$country', '$province', '$district', '$street', '$houseNumber')";
    
    if (mysqli_query($conn, $addrSql)) {
        $addressID = mysqli_insert_id($conn);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $type = mysqli_real_escape_string($conn, $_POST['type']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $detail = mysqli_real_escape_string($conn, $_POST['detail']);
        $price = mysqli_real_escape_string($conn, $_POST['price']);

        $spotSql = "INSERT INTO spot (name, type, status, phone, addressID, detail, price, photo) 
                    VALUES ('$name', '$type', '$status', '$phone', '$addressID', '$detail', '$price', '$photoName')";
        
        if (mysqli_query($conn, $spotSql)) {
            header("Location: dashboard.php?msg=added");
            exit();
        }
    }
}

// --- ACTION: UPDATE SPOT (FIXED) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_spot_full'])) {
    
    // Start Transaction
    mysqli_begin_transaction($conn);

    try {
        $sID = mysqli_real_escape_string($conn, $_POST['spotID']);
        $aID = mysqli_real_escape_string($conn, $_POST['addressID']);
        
        // Address Data
        $country = mysqli_real_escape_string($conn, $_POST['country']);
        $province = mysqli_real_escape_string($conn, $_POST['province']);
        $district = mysqli_real_escape_string($conn, $_POST['district']);
        $street = mysqli_real_escape_string($conn, $_POST['street']);
        $house = mysqli_real_escape_string($conn, $_POST['houseNumber']);

        // Query 1: Address Table
        mysqli_query($conn, "UPDATE address SET country='$country', province='$province', district='$district', street='$street', houseNumber='$house' WHERE addressID='$aID'");

        // Photo Upload Logic
        $photoUpdatePart = ""; 
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (in_array($extension, $allowed)) {
                $newPhotoName = time() . '_' . uniqid() . '.' . $extension;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetDir . $newPhotoName)) {
                    $photoUpdatePart = ", photo='$newPhotoName'";
                }
            }
        }

        // Spot Data
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $price = mysqli_real_escape_string($conn, $_POST['price']);
        $type = mysqli_real_escape_string($conn, $_POST['type']);
        
        // NEW: Capture Status from radio buttons
        $status = mysqli_real_escape_string($conn, $_POST['status']); 

        // Query 2: Spot Table (Now including status)
        $spotUpdate = "UPDATE spot SET name='$name', price='$price', type='$type', status='$status' $photoUpdatePart WHERE spotID='$sID'";

        if (mysqli_query($conn, $spotUpdate)) {
            // If everything is okay, save the changes permanently
            mysqli_commit($conn);
            header("Location: dashboard.php?msg=updated");
            exit();
        } else {
            throw new Exception("Spot update failed");
        }

    } catch (Exception $e) {
        // If anything fails, undo both updates
        mysqli_rollback($conn);
        header("Location: dashboard.php?msg=error");
        exit();
    }
}

// --- ACTION: DELETE SPOT ---
if (isset($_GET['delete_spot'])) {
    $sID = mysqli_real_escape_string($conn, $_GET['delete_spot']);
    $aID = mysqli_real_escape_string($conn, $_GET['addressID']);
    if (mysqli_query($conn, "DELETE FROM spot WHERE spotID = '$sID'")) {
        mysqli_query($conn, "DELETE FROM address WHERE addressID = '$aID'");
        $message = "Spot removed successfully.";
    }
}  

// --- DATA FETCHING ---
$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count, SUM(totalPrice) as total FROM booking"));
$userCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as uCount FROM userinfo"))['uCount'];

$allBookings = mysqli_query($conn, "SELECT b.*, s.name as spotName, u.FName, u.LName FROM booking b JOIN spot s ON b.spotID = s.spotID JOIN userinfo u ON b.accountID = u.accountID ORDER BY b.purchaseDate DESC");
$usersResult = mysqli_query($conn, "SELECT u.*, a.email, a.permission FROM userinfo u JOIN account a ON u.accountID = a.accountID ORDER BY u.createdDate DESC");

// Fetching all spots with joined address data
$allSpots = mysqli_query($conn, "SELECT s.*, a.country, a.province, a.district, a.street, a.houseNumber FROM spot s LEFT JOIN address a ON s.addressID = a.addressID ORDER BY s.spotID DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Master Admin | BookingMaster</title>
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
        function navigateTo(tabId, btn) {
            document.querySelectorAll('.tab-content').forEach(t => t.classList.add('hidden'));
            document.getElementById(tabId).classList.remove('hidden');
            document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('bg-brand', 'text-white'));
            btn.classList.add('bg-brand', 'text-white');
        }
    </script>
</head>
<body class="bg-slateBg font-sans text-slate-900">

    <div class="flex min-h-screen">
        <aside class="w-72 bg-dark text-slate-400 p-8 hidden lg:flex flex-col fixed h-full z-50">
            <div class="text-white text-2xl font-black mb-12 flex items-center gap-3 italic">
                <div class="bg-brand p-2 rounded-xl"><i class="bi bi-shield-check"></i></div> AdminHub
            </div>
            <nav class="flex-1 space-y-2">
                <button onclick="navigateTo('tab-overview', this)" class="nav-btn w-full flex items-center gap-4 px-5 py-3.5 rounded-2xl bg-brand text-white font-bold transition-all">
                    <i class="bi bi-grid"></i> Overview
                </button>
                <button onclick="navigateTo('tab-users', this)" class="nav-btn w-full flex items-center gap-4 px-5 py-3.5 rounded-2xl hover:bg-white/5 font-bold text-left transition-all">
                    <i class="bi bi-people"></i> Manage Users
                </button>
                <button onclick="navigateTo('tab-add-spot', this)" class="nav-btn w-full flex items-center gap-4 px-5 py-3.5 rounded-2xl hover:bg-white/5 font-bold text-left transition-all">
                    <i class="bi bi-plus-circle-fill"></i> New Product
                </button>
                <button onclick="navigateTo('tab-manage-spots', this)" class="nav-btn w-full flex items-center gap-4 px-5 py-3.5 rounded-2xl hover:bg-white/5 font-bold text-left transition-all">
                    <i class="bi bi-collection-play-fill"></i> Manage Product
                </button>
            </nav>
            <a href="../../home/index.php" class="text-slate-400 font-bold flex items-center gap-4 px-5 py-3.5 hover:bg-white/5 hover:text-white rounded-2xl transition-all">
                <i class="bi bi-house-door-fill"></i> Return Home
            </a>
            <a href="../../profile/logout/index.php" class="text-red-400 font-bold flex items-center gap-4 px-5 py-3 hover:bg-red-500/10 rounded-xl">
                <i class="bi bi-power"></i> Logout
            </a>
        </aside>

        <main class="flex-1 ml-72 p-12">
            <?php if($message): ?>
                <div class="mb-8 p-4 bg-green-500 text-white rounded-2xl font-bold shadow-lg"><?= $message ?></div>
            <?php endif; ?>

            <div id="tab-overview" class="tab-content space-y-10">
                <div class="flex justify-between items-end">
                    <div>
                        <h1 class="text-4xl font-black">System Overview</h1>
                        <p class="text-slate-400">Manage all transactions and platform metrics.</p>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-6">
                    <div class="bg-white p-8 rounded-[2rem] border shadow-sm">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Bookings</p>
                        <h3 class="text-3xl font-black"><?= number_format($stats['count']) ?></h3>
                    </div>
                    <div class="bg-white p-8 rounded-[2rem] border shadow-sm">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Revenue</p>
                        <h3 class="text-3xl font-black text-brand">$<?= number_format($stats['total'] ?? 0, 2) ?></h3>
                    </div>
                    <div class="bg-white p-8 rounded-[2rem] border shadow-sm">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Active Users</p>
                        <h3 class="text-3xl font-black"><?= number_format($userCount) ?></h3>
                    </div>
                </div>
                <div class="bg-white rounded-[2.5rem] border shadow-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <tr>
                                <th class="px-8 py-5">Booking ID</th>
                                <th class="px-8 py-5">Customer</th>
                                <th class="px-8 py-5">Spot</th>
                                <th class="px-8 py-5">Dates</th>
                                <th class="px-8 py-5 text-right">Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php while($b = mysqli_fetch_assoc($allBookings)): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-8 py-5 font-black text-slate-300">#BK-<?= $b['bookingID'] ?></td>
                                <td class="px-8 py-5 font-bold text-sm"><?= htmlspecialchars($b['FName'].' '.$b['LName']) ?></td>
                                <td class="px-8 py-5 text-sm text-slate-500 italic"><?= htmlspecialchars($b['spotName']) ?></td>
                                <td class="px-8 py-5 text-xs font-bold text-slate-400">
                                    <?= date('M d', strtotime($b['checkinDate'])) ?> - <?= date('M d, Y', strtotime($b['checkoutDate'])) ?>
                                </td>
                                <td class="px-8 py-5 text-right font-black text-brand">$<?= number_format($b['totalPrice'], 2) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="tab-users" class="tab-content hidden space-y-8">
                <h1 class="text-4xl font-black italic">User Registry</h1>
                <div class="bg-white rounded-[2.5rem] border shadow-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-slate-900 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <tr>
                                <th class="px-8 py-5">Member</th>
                                <th class="px-8 py-5">Role</th>
                                <th class="px-8 py-5">Contact</th>
                                <th class="px-8 py-5 text-center">Manage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php while($u = mysqli_fetch_assoc($usersResult)): ?>
                            <tr class="hover:bg-slate-50 transition group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center font-bold text-brand uppercase">
                                            <?= substr($u['FName'], 0, 1) ?>
                                        </div>
                                        <div>
                                            <p class="font-bold text-sm"><?= $u['FName'].' '.$u['LName'] ?></p>
                                            <p class="text-[10px] text-slate-400">ID: #<?= $u['userID'] ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="<?= $u['permission'] == 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?> px-3 py-1 rounded-full text-[10px] font-black uppercase">
                                        <?= $u['permission'] ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5">
                                    <p class="text-sm font-semibold"><?= $u['email'] ?></p>
                                    <p class="text-xs text-slate-400"><?= $u['phone'] ?: 'No Phone' ?></p>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button onclick='openEditUserModal(<?= json_encode($u) ?>)' class="bg-slate-100 p-3 rounded-xl text-slate-400 hover:bg-brand hover:text-white transition">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <a href="?delete_user=<?= $u['accountID'] ?>" onclick="return confirm('Delete user?')" class="bg-red-50 p-3 rounded-xl text-red-400 hover:bg-red-500 hover:text-white transition">
                                            <i class="bi bi-trash3-fill"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="tab-add-spot" class="tab-content hidden space-y-10">
                <h1 class="text-4xl font-black">Add New Destination</h1>
                <form action="" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                    <div class="xl:col-span-2 bg-white p-10 rounded-[3rem] border space-y-6">
                        <label class="block">
                            <span class="text-[10px] font-black uppercase text-slate-400 ml-4">Photo</span>
                            <input type="file" name="photo" class="w-full mt-2" required>
                        </label>
                        <input type="text" name="name" placeholder="Spot Name" class="w-full bg-slate-50 border-none rounded-2xl p-5 font-bold" required>
                        <div class="grid grid-cols-2 gap-6">
                            <select name="type" class="bg-slate-50 border-none rounded-2xl p-5 font-bold">
                                <option value="Travel-Packages">Travel-Packages</option>
                                <option value="Luxury-Hotels">Luxury-Hotels</option>
                                <option value="Vehicle-Rental">Vehicle-Rental</option>
                                <option value="Room-Rentals">Room-Rentals</option>
                            </select>
                            <input type="number" step="0.01" name="price" placeholder="Price ($)" class="bg-slate-50 border-none rounded-2xl p-5 font-bold" required>
                        </div>
                        <textarea name="detail" rows="4" placeholder="Description..." class="w-full bg-slate-50 border-none rounded-2xl p-5 font-bold"></textarea>
                    </div>
                    <div class="bg-slate-900 text-white p-10 rounded-[3rem] space-y-4">
                        <h3 class="text-xl font-bold italic border-b border-white/10 pb-4">Location Details</h3>
                        <input type="text" name="houseNumber" placeholder="House No." class="w-full bg-white/5 p-4 rounded-xl border-none">
                        <input type="text" name="street" placeholder="Street" class="w-full bg-white/5 p-4 rounded-xl border-none">
                        <input type="text" name="district" placeholder="District" class="w-full bg-white/5 p-4 rounded-xl border-none">
                        <input type="text" name="province" placeholder="Province" class="w-full bg-white/5 p-4 rounded-xl border-none">
                        <input type="text" name="country" placeholder="Country" class="w-full bg-white/5 p-4 rounded-xl border-none">
                        <input type="text" name="phone" placeholder="Contact Phone" class="w-full bg-white/5 p-4 rounded-xl border-none">
                        <select name="status" class="w-full bg-white/5 p-4 rounded-xl border-none">
                            <option value="Available" class="text-dark">Available</option>
                            <option value="Hidden" class="text-dark">Hidden</option>
                        </select>
                        <button type="submit" name="add_new_spot" class="w-full bg-brand py-5 rounded-2xl font-black uppercase mt-4">Save to Database</button>
                    </div>
                </form>
            </div>

          <div id="tab-manage-spots" class="tab-content hidden space-y-8 animate-in fade-in duration-500">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Destination Inventory</h1>
            <p class="text-slate-400 font-medium">Manage and monitor your properties and their availability.</p>
        </div>
        <div class="bg-white px-6 py-4 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 bg-brand/10 rounded-xl flex items-center justify-center text-brand">
                <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div>
                <span class="text-2xl font-black text-slate-900 block leading-none">
                    <?php 
                    if($allSpots) {
                        mysqli_data_seek($allSpots, 0); 
                        echo mysqli_num_rows($allSpots); 
                    } else { echo "0"; }
                    ?>
                </span>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Spots</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-[3rem] border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                <tr>
                    <th class="px-8 py-6">Spot Details</th>
                    <th class="px-8 py-6">Location</th>
                    <th class="px-8 py-6">Pricing</th>
                    <th class="px-8 py-6">Status</th>
                    <th class="px-8 py-6 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php 
                if($allSpots && mysqli_num_rows($allSpots) > 0):
                    mysqli_data_seek($allSpots, 0); 
                    // RESTORED LOOP START
                    while($s = mysqli_fetch_assoc($allSpots)): 
                        $isAvail = (strtolower($s['status'] ?? '') == 'available');
                ?>
                <tr class="hover:bg-slate-50/30 transition-colors group">
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-4">
                            <div class="relative w-14 h-14 shrink-0 rounded-2xl bg-slate-100 overflow-hidden border-2 border-white shadow-sm">
                                <?php if(!empty($s['photo'])): ?>
                                    <img src="../../database/imgs/<?= htmlspecialchars($s['photo']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-slate-300"><i class="bi bi-image"></i></div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="font-black text-slate-800 leading-tight"><?= htmlspecialchars($s['name']) ?></p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tight mt-0.5"><?= htmlspecialchars($s['type']) ?></p>
                            </div>
                        </div>
                    </td>

                    <td class="px-8 py-6">
                        <p class="text-sm font-bold text-slate-600"><?= htmlspecialchars($s['district'] ?? '') ?>, <?= htmlspecialchars($s['province'] ?? '') ?></p>
                        <p class="text-[10px] text-slate-400 font-black uppercase"><?= htmlspecialchars($s['country'] ?? '') ?></p>
                    </td>

                    <td class="px-8 py-6">
                        <span class="text-lg font-black text-slate-900">$<?= number_format($s['price'], 2) ?></span>
                        <span class="text-[9px] font-bold text-slate-400 block uppercase">per night</span>
                    </td>

                    <td class="px-8 py-6">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border shadow-sm <?= $isAvail ? 'bg-green-50 border-green-100 text-green-600' : 'bg-red-50 border-red-100 text-red-600' ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?= $isAvail ? 'bg-green-500 animate-pulse' : 'bg-red-500' ?>"></span>
                            <span class="text-[10px] font-black uppercase tracking-widest"><?= $isAvail ? 'Active' : 'Inactive' ?></span>
                        </div>
                    </td>

                    <td class="px-8 py-6 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick='openEditSpotModal(<?= json_encode($s, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' 
                                    class="w-10 h-10 bg-white border border-slate-100 rounded-xl text-slate-400 hover:text-brand hover:border-brand/30 hover:shadow-lg transition-all flex items-center justify-center">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <a href="?delete_spot=<?= $s['spotID'] ?>&addressID=<?= $s['addressID'] ?>" 
                               onclick="return confirm('Delete spot?')" 
                               class="w-10 h-10 bg-white border border-slate-100 rounded-xl text-slate-300 hover:text-red-500 hover:border-red-100 hover:shadow-lg transition-all flex items-center justify-center">
                                <i class="bi bi-trash3-fill"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; // RESTORED LOOP END ?>
                
                <?php else: ?>
                <tr>
                    <td colspan="5" class="py-20 text-center">
                        <div class="flex flex-col items-center">
                            <i class="bi bi-inbox text-5xl text-slate-100 mb-4"></i>
                            <p class="text-slate-400 font-bold uppercase tracking-widest">No destinations found in inventory.</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
        </main>
    </div>

    <div id="editUserModal" class="hidden fixed inset-0 bg-dark/80 backdrop-blur-sm z-[100] flex items-center justify-center p-6">
        <div class="bg-white w-full max-w-2xl rounded-[3rem] p-12 shadow-2xl relative">
            <button onclick="closeEditUserModal()" class="absolute top-10 right-10 text-slate-300 hover:text-red-500"><i class="bi bi-x-circle-fill text-3xl"></i></button>
            <h3 class="text-3xl font-black mb-8 italic">Edit User Record</h3>
            <form action="" method="POST" class="space-y-6">
                <input type="hidden" name="targetUserID" id="m_userID">
                <input type="hidden" name="targetAccountID" id="m_accID">
                <div class="grid grid-cols-2 gap-6">
                    <input type="text" name="fName" id="m_fName" placeholder="First Name" class="w-full bg-slate-50 rounded-2xl p-4 font-bold border-none">
                    <input type="text" name="lName" id="m_lName" placeholder="Last Name" class="w-full bg-slate-50 rounded-2xl p-4 font-bold border-none">
                </div>
                <input type="email" name="email" id="m_email" placeholder="Email" class="w-full bg-slate-50 rounded-2xl p-4 font-bold border-none">
                <div class="grid grid-cols-2 gap-6">
                    <input type="text" name="phone" id="m_phone" placeholder="Phone" class="w-full bg-slate-50 rounded-2xl p-4 font-bold border-none">
                    <select name="permission" id="m_perm" class="w-full bg-slate-50 rounded-2xl p-4 font-bold border-none">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" name="admin_update_user" class="w-full bg-brand text-white py-5 rounded-2xl font-black uppercase">Save Changes</button>
            </form>
        </div>
    </div>

    <div id="editSpotModal" class="hidden fixed inset-0 bg-dark/80 backdrop-blur-md z-[100] flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-2xl rounded-[3rem] p-10 shadow-2xl relative overflow-y-auto max-h-[90vh]">
            <h3 class="text-2xl font-black mb-6 italic border-b pb-4">Edit Destination</h3>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="spotID" id="es_spotID">
                <input type="hidden" name="addressID" id="es_addressID">
                <div class="flex items-center gap-6 bg-slate-50 p-6 rounded-[2rem]">
                    <img id="es_preview" src="" class="w-24 h-24 rounded-2xl object-cover border-2 border-white shadow-sm">
                    <div class="flex-1">
                        <label class="text-[10px] font-black uppercase text-slate-400">Replace Photo</label>
                        <input type="file" name="photo" class="w-full text-xs mt-1">
                    </div>
                </div>
                <input type="text" name="name" id="es_name" placeholder="Name" class="w-full bg-slate-50 rounded-2xl p-4 font-bold border-none">
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="type" id="es_type" placeholder="Type" class="bg-slate-50 rounded-2xl p-4 font-bold border-none">
                    <input type="number" step="0.01" name="price" id="es_price" placeholder="Price" class="bg-slate-50 rounded-2xl p-4 font-bold border-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="houseNumber" id="es_house" placeholder="House No." class="bg-slate-50 rounded-2xl p-4 border-none text-sm font-bold">
                    <input type="text" name="street" id="es_street" placeholder="Street" class="bg-slate-50 rounded-2xl p-4 border-none text-sm font-bold">
                    <input type="text" name="district" id="es_district" placeholder="District" class="bg-slate-50 rounded-2xl p-4 border-none text-sm font-bold">
                    <input type="text" name="province" id="es_province" placeholder="Province" class="bg-slate-50 rounded-2xl p-4 border-none text-sm font-bold">
                    <input type="text" name="country" id="es_country" placeholder="Country" class="col-span-2 bg-slate-50 rounded-2xl p-4 border-none text-sm font-bold">
                </div>
                <div class="space-y-2">
    <label class="text-[10px] font-black uppercase text-slate-400 ml-2">Destination Status</label>
    <div class="flex p-1.5 bg-slate-100 rounded-2xl w-full">
        <label class="flex-1 cursor-pointer">
            <input type="radio" name="status" value="Available" id="es_status_available" class="hidden peer">
            <div class="text-center py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all peer-checked:bg-green-500 peer-checked:text-white text-slate-400">
                <i class="bi bi-check-circle-fill mr-1"></i> Available
            </div>
        </label>
        <label class="flex-1 cursor-pointer">
            <input type="radio" name="status" value="Unavailable" id="es_status_unavailable" class="hidden peer">
            <div class="text-center py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all peer-checked:bg-red-500 peer-checked:text-white text-slate-400">
                <i class="bi bi-x-circle-fill mr-1"></i> Unavailable
            </div>
        </label>
    </div>
</div>
                <div class="flex gap-3">
                    <button type="submit" name="update_spot_full" class="flex-1 bg-brand text-white py-4 rounded-2xl font-black uppercase">Save All</button>
                    <button type="button" onclick="closeSpotModal()" class="px-8 bg-slate-100 py-4 rounded-2xl font-black uppercase">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditUserModal(user) {
            document.getElementById('m_userID').value = user.userID;
            document.getElementById('m_accID').value = user.accountID;
            document.getElementById('m_fName').value = user.FName;
            document.getElementById('m_lName').value = user.LName;
            document.getElementById('m_email').value = user.email;
            document.getElementById('m_phone').value = user.phone;
            document.getElementById('m_perm').value = user.permission;
            document.getElementById('editUserModal').classList.remove('hidden');
        }
        function closeEditUserModal() { document.getElementById('editUserModal').classList.add('hidden'); }

     function openEditSpotModal(s) {
    document.getElementById('es_spotID').value = s.spotID;
    document.getElementById('es_addressID').value = s.addressID;
    document.getElementById('es_name').value = s.name;
    document.getElementById('es_type').value = s.type;
    document.getElementById('es_price').value = s.price;
    document.getElementById('es_house').value = s.houseNumber;
    document.getElementById('es_street').value = s.street;
    document.getElementById('es_district').value = s.district;
    document.getElementById('es_province').value = s.province;
    document.getElementById('es_country').value = s.country;

    // --- Status Toggle Logic ---
    // Ensure the status is lowercase for comparison
    const currentStatus = (s.status || 'available').toLowerCase();
    if (currentStatus === 'available') {
        document.getElementById('es_status_available').checked = true;
    } else {
        document.getElementById('es_status_unavailable').checked = true;
    }

    document.getElementById('es_preview').src = s.photo ? "../../database/imgs/" + s.photo : "https://via.placeholder.com/150";
    document.getElementById('editSpotModal').classList.remove('hidden');
}
    </script>
</body>
</html>