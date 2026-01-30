<?php
session_start();
require_once '../admin/config.php'; 

// 1. Authenticated User Data Logic
$isLoggedIn = isset($_SESSION['accountID']);
$profilePic = 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png'; 
$userEmail = '';

if ($isLoggedIn) {
    $accID = $_SESSION['accountID'];
    // FIX: Join account table to get email
    $userQuery = "SELECT u.profile, a.email 
                  FROM userinfo u 
                  INNER JOIN account a ON u.accountID = a.accountID 
                  WHERE a.accountID = ?";
    
    $stmt = mysqli_prepare($conn, $userQuery);
    mysqli_stmt_bind_param($stmt, "i", $accID);
    mysqli_stmt_execute($stmt);
    $userResult = mysqli_stmt_get_result($stmt);
    
    if ($userData = mysqli_fetch_assoc($userResult)) {
        $userEmail = $userData['email'];
        if (!empty($userData['profile'])) {
            // Updated path to match your file structure logic
            $profilePic = (filter_var($userData['profile'], FILTER_VALIDATE_URL)) 
                          ? $userData['profile'] 
                          : "../database/imgs/" . htmlspecialchars($userData['profile']);
        }
    }
}

// 2. Capture Filters (Sanitized)
$searchTerm = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$filterType = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';
$filterCountry = isset($_GET['country']) ? mysqli_real_escape_string($conn, $_GET['country']) : '';

// 3. Dynamic Query Building
$query = "SELECT s.*, a.country, a.province, a.district, a.street 
          FROM spot s 
          INNER JOIN address a ON s.addressID = a.addressID 
          WHERE 1=1";

if (!empty($searchTerm)) {
    $query .= " AND (s.name LIKE '%$searchTerm%' 
                OR a.country LIKE '%$searchTerm%' 
                OR a.province LIKE '%$searchTerm%' 
                OR a.district LIKE '%$searchTerm%' 
                OR a.street LIKE '%$searchTerm%')";
}

if (!empty($filterType)) {
    $query .= " AND s.type = '$filterType'";
}

if (!empty($filterCountry)) {
    $query .= " AND a.country = '$filterCountry'";
}

$result = mysqli_query($conn, $query);
$spots = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Destinations | BookingMaster</title>
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
        function toggleDropdown() {
            document.getElementById('profile-dropdown').classList.toggle('hidden');
        }
    </script>
</head>
<body class="bg-slateBg font-sans text-slate-900 antialiased">

    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-2 text-2xl font-extrabold tracking-tighter text-dark">
                <div class="bg-brand p-1.5 rounded-lg text-white"><i class="bi bi-geo-alt-fill"></i></div>
                <span>BookingMaster</span>
            </a>

             <div class="flex items-center space-x-6">
                <?php if ($isLoggedIn): ?>
                    <div class="relative">
                        <button onclick="toggleDropdown()" class="flex items-center focus:outline-none" aria-label="User menu">
                            <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-brand/20 hover:border-brand transition-all shadow-sm">
                                <img src="<?= $profilePic ?>" alt="Profile" class="w-full h-full object-cover">
                            </div>
                        </button>

                        <div id="profile-dropdown" class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-50">
                            <div class="px-4 py-3 border-b border-slate-50">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Signed in as</p>
                                <p class="text-xs font-bold text-slate-700 truncate"><?= htmlspecialchars($userEmail) ?></p>
                            </div>
                            <a href="userDashboard.php" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-brand transition">
                                <i class="bi bi-speedometer2 text-lg"></i> Dashboard
                            </a>
                            <hr class="my-1 border-slate-50">
                            <a href="../profile/login/index.php" class="flex items-center gap-3 px-4 py-3 text-sm font-semibold text-red-500 hover:bg-red-50 transition">
                                <i class="bi bi-box-arrow-right text-lg"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="../profile/login/index.php" class="text-sm font-bold text-slate-700 hover:text-brand transition flex items-center gap-1">
                        <i class="bi bi-person-circle text-lg"></i> Sign In
                    </a>
                <?php endif; ?>

            </div>
        </div>
    </nav>

    <header class="bg-white border-b border-slate-100 py-10">
        <div class="max-w-7xl mx-auto px-6">
            <form action="" method="GET" class="flex flex-col md:flex-row gap-4 mb-8">
                <div class="flex-1 relative">
                    <i class="bi bi-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($searchTerm) ?>" placeholder="Search address, city, or resort name..." class="w-full bg-slate-50 border-none rounded-2xl py-5 pl-14 pr-4 font-semibold shadow-sm focus:ring-2 focus:ring-brand">
                </div>
                <div class="w-full md:w-64 relative">
                    <select name="country" onchange="this.form.submit()" class="w-full bg-slate-50 border-none rounded-2xl py-5 px-6 font-bold appearance-none shadow-sm cursor-pointer">
                        <option value="">Popular Countries</option>
                        <?php foreach($popularCountries as $c): ?>
                            <option value="<?= $c ?>" <?= ($filterCountry == $c) ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="bg-brand text-white px-10 py-5 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl shadow-blue-100">Search</button>
            </form>

            <div class="flex items-center gap-4 overflow-x-auto pb-2 no-scrollbar">
                <a href="viewAllProduct.php?country=<?= $filterCountry ?>&search=<?= $searchTerm ?>" 
                   class="px-8 py-3 rounded-full text-sm font-bold whitespace-nowrap transition <?= empty($filterType) ? 'bg-dark text-white shadow-lg' : 'bg-slate-50 text-slate-500 hover:bg-slate-100' ?>">
                    All Destinantions
                </a>
                <?php foreach(['Travel-Packages','Luxury-Hotels', 'Vehicle-Rental', 'Room-Rentals','Tour-Guides'] as $type): ?>
                    <a href="viewAllProduct.php?type=<?= $type ?>&country=<?= $filterCountry ?>&search=<?= $searchTerm ?>" 
                       class="px-8 py-3 rounded-full text-sm font-bold whitespace-nowrap transition <?= ($filterType == $type) ? 'bg-dark text-white shadow-lg' : 'bg-slate-50 text-slate-500 hover:bg-slate-100' ?>">
                        <?= $type ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </header>

<main class="max-w-7xl mx-auto px-6 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
        <?php if (!empty($spots)): ?>
            <?php foreach ($spots as $spot): 
               $img = (filter_var($spot['photo'], FILTER_VALIDATE_URL)) 
        ? $spot['photo'] 
        : "../database/imgs/" . $spot['photo'];

                $isAvailable = (strcasecmp($spot['status'], 'available') == 0);
            ?>
            <div class="bg-white rounded-[2.5rem] overflow-hidden border border-slate-100 group flex flex-col hover:shadow-2xl hover:shadow-slate-200/50 transition-all duration-500">
                <div class="relative h-64 overflow-hidden">
                    <img src="<?= htmlspecialchars($img) ?>" 
                         alt="<?= htmlspecialchars($spot['name']) ?>" 
                         class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                    
                    <div class="absolute bottom-5 left-5">
                        <span class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest <?= $isAvailable ? 'bg-green-500 text-white' : 'bg-red-500 text-white' ?> shadow-lg">
                            <i class="bi <?= $isAvailable ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?>"></i>
                            <?= htmlspecialchars($spot['status']) ?>
                        </span>
                    </div>
                </div>
                
                <div class="p-8 flex-1 flex flex-col">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">
                        <?= htmlspecialchars($spot['province'] ?? '') ?>, <?= htmlspecialchars($spot['country'] ?? '') ?>
                    </p>
                    
                    <h3 class="text-xl font-extrabold text-slate-900 mb-2 line-clamp-1">
                        <?= htmlspecialchars($spot['name']) ?>
                    </h3>
                    
                    <p class="text-xs text-slate-400 mb-6 line-clamp-1">
                        <i class="bi bi-geo-alt mr-1"></i>
                        <?= htmlspecialchars($spot['street']) ?>, <?= htmlspecialchars($spot['district']) ?>
                    </p>
                    
                    <div class="mt-auto pt-6 border-t border-slate-50 flex justify-between items-center">
                        <div>
                            <p class="text-[9px] font-bold text-slate-300 uppercase">Rate / Night</p>
                            <span class="text-2xl font-black text-slate-900">$<?= number_format($spot['price'], 2) ?></span>
                        </div>
                        
                        <?php if($isAvailable): ?>
                            <a href="productDetail.php?id=<?= (int)$spot['spotID'] ?>" 
                               class="bg-blue-600 text-white px-8 py-3 rounded-2xl font-black text-[11px] uppercase tracking-widest hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200">
                                View Details
                            </a>
                        <?php else: ?>
                            <button disabled class="bg-slate-100 text-slate-400 px-8 py-3 rounded-2xl font-black text-[11px] uppercase tracking-widest cursor-not-allowed">
                                Fully Booked
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full py-20 text-center bg-slate-50 rounded-[2.5rem] border-2 border-dashed border-slate-200">
                <i class="bi bi-search text-4xl text-slate-200 mb-4 block"></i>
                <p class="text-slate-400 font-bold uppercase tracking-widest">No spots match your search</p>
                <a href="?" class="text-blue-600 text-xs font-black uppercase mt-4 inline-block underline">Clear Filters</a>
            </div>
        <?php endif; ?>
    </div>
</main>

</body>
</html>