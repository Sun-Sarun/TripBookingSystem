<?php
session_start();
require_once '../admin/config.php'; 

$isLoggedIn = isset($_SESSION['accountID']);
$profilePic = 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png'; 
$userEmail = '';

if ($isLoggedIn) {
    // Use a prepared statement to prevent SQL Injection
    $accID = $_SESSION['accountID'];
    
    // FIX: JOIN account and userinfo because email is in account table
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
            $profilePic = (filter_var($userData['profile'], FILTER_VALIDATE_URL)) 
                          ? $userData['profile'] 
                          : "../database/imgs/" . htmlspecialchars($userData['profile']);
        }
    }
}

// 2. Spot Query (This part was mostly correct, but using a join is good practice)
$query = "SELECT s.*, a.country, a.province 
          FROM spot s 
          INNER JOIN address a ON s.addressID = a.addressID 
          LIMIT 6";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookingMaster | Luxury Travel & Stays</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
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
    <style>
        .glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
        .hero-gradient {
            background: linear-gradient(rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.6)), 
                        url('https://images.unsplash.com/photo-1469474968028-56623f02e42e?q=80&w=2074&auto=format&fit=crop');
            background-size: cover; background-position: center;
        }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        #profile-dropdown { display: none; }
        #profile-dropdown.show { display: block; }
        .spot-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem; }
        .spot-card { background: white; border-radius: 1.5rem; overflow: hidden; transition: transform 0.3s ease; border: 1px solid #f1f5f9; }
        .spot-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="bg-gray-50 text-slate-900 font-sans">

    <nav class="fixed w-full z-50 transition-all duration-300 glass border-b border-gray-200">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="flex items-center space-x-2 text-2xl font-extrabold text-brand tracking-tighter">
                <i class="bi bi-geo-alt-fill"></i>
                <span>BookingMaster</span>
            </a>
            
            <div class="hidden md:flex space-x-8 font-semibold text-slate-600">
                <a href="index.php" class="hover:text-brand transition">Home</a>
                <a href="#categories" class="hover:text-brand transition">Categories</a>
                <a href="viewAllProduct.php" class="hover:text-brand transition">Destinations</a>
                <a href="about.php" class="hover:text-brand transition">About Us</a>
            </div>

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
                <a href="viewAllProduct.php" class="bg-brand text-white px-6 py-2.5 rounded-full font-bold shadow-lg hover:bg-blue-600 transition">Book Now</a>
            </div>
        </div>
    </nav>

    <header class="hero-gradient h-[85vh] flex flex-col items-center justify-center text-center px-6">
        <div class="max-w-4xl pt-20">
            <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-6 leading-tight">
                Adventure Awaits, <br><span class="text-blue-400">Explore the World.</span>
            </h1>
            <div class="max-w-2xl mx-auto mt-10">
                <form action="viewAllProduct.php" method="GET" class="bg-white p-2 rounded-2xl shadow-2xl flex items-center">
                    <div class="flex-1 flex items-center px-4 border-r border-gray-100">
                        <i class="bi bi-search text-brand mr-3"></i>
                        <input type="text" name="search" placeholder="Where do you want to go?" 
                               class="w-full py-3 bg-transparent border-none focus:ring-0 text-slate-700 font-medium outline-none">
                    </div>
                    <button type="submit" class="bg-brand text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-600 transition">Search</button>
                </form>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-20 space-y-24">
        <section id="categories">
            <h2 class="text-3xl font-extrabold mb-10">Popular Categories</h2>
            <div class="flex overflow-x-auto gap-6 hide-scrollbar pb-4">
                <?php
                $cats = [
                    ['Travel-Packages', 'bi-backpack', 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?q=80&w=500'],
                    ['Luxury-Hotels', 'bi-building', 'https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=500'],
                    ['Vehicle-Rental', 'bi-car-front', 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?q=80&w=500'],
                    ['Tour-Guides', 'bi-person-walking', 'https://images.unsplash.com/photo-1533105079780-92b9be482077?q=80&w=500'],
                    ['Room-Rentals', 'bi-backpack', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS4rOQmE15-oWBT8gfeNLgJglYDLO16cQDRDw&s'],
                ];
                foreach ($cats as $cat): ?>
                <a href="viewAllProduct.php?type=<?= urlencode($cat[0]) ?>" class="min-w-[280px] group block">
                    <div class="relative h-48 rounded-2xl overflow-hidden mb-4 shadow-md">
                        <img src="<?= $cat[2] ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy">
                        <div class="absolute inset-0 bg-black/30 flex items-center justify-center">
                            <i class="bi <?= $cat[1] ?> text-white text-4xl"></i>
                        </div>
                    </div>
                    <h3 class="font-bold text-xl group-hover:text-brand transition"><?= str_replace('-', ' ', $cat[0]) ?></h3>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="packages">
            <div class="flex justify-between items-end mb-10">
                <div>
                    <h2 class="text-3xl font-extrabold">Featured Destinations</h2>
                    <p class="text-slate-500 mt-2">Handpicked places for your next adventure</p>
                </div>
                <a href="viewAllProduct.php" class="text-brand font-bold hover:underline">View All <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="spot-grid">
                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        // Logic for Status
                        $status = htmlspecialchars($row['status'] ?? 'Available');
                        $isAvailable = (strtolower($status) === 'available');
                        $statusClass = $isAvailable ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                    ?>
                        <div class="spot-card flex flex-col">
                           <div class="relative h-56 overflow-hidden">
    <?php 
        // 1. Check if photo exists in DB
        $photoPath = $row['photo'];
        
        // 2. Determine the correct source URL
        if (empty($photoPath)) {
            $displayImg = 'https://via.placeholder.com/400x300';
        } elseif (filter_var($photoPath, FILTER_VALIDATE_URL)) {
            // It's a full URL (e.g., https://...)
            $displayImg = $photoPath;
        } else {
            // It's a local file name, prepend your image directory
            $displayImg = "../database/imgs/" . htmlspecialchars($photoPath);
        }
    ?>
    
    <img src="<?= $displayImg ?>" 
         alt="<?= htmlspecialchars($row['name']) ?>" 
         class="w-full h-full object-cover">
         
    <span class="absolute top-4 right-4 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider shadow-sm <?= $statusClass ?>">
        <?= htmlspecialchars($row['status']) ?>
    </span>
</div>
                            
                            <div class="p-6 flex-1 flex flex-col">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-xs font-bold text-brand uppercase tracking-widest"><?= htmlspecialchars($row['type']) ?></span>
                                </div>
                                <h3 class="text-xl font-bold text-slate-900 mb-1"><?= htmlspecialchars($row['name']) ?></h3>
                                <p class="text-sm text-slate-500 flex items-center gap-1 mb-4">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($row['province']) ?>, <?= htmlspecialchars($row['country']) ?>
                                </p>
                                
                                <div class="mt-auto pt-4 border-t border-slate-50 flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-slate-400 font-semibold uppercase">Starting from</p>
                                        <p class="text-2xl font-black text-slate-900">$<?= number_format($row['price'], 2) ?></p>
                                    </div>
                                    
                                    <?php if ($isAvailable): ?>
                                        <a href="productDetail.php?id=<?= $row['spotID'] ?>" class="bg-brand text-white px-5 py-2.5 rounded-xl font-bold shadow-md hover:bg-blue-600 transition-all">
                                            Book Now
                                        </a>
                                    <?php else: ?>
                                        <button disabled class="bg-slate-100 text-slate-400 px-5 py-2.5 rounded-xl font-bold cursor-not-allowed">
                                            Fully Booked
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-span-full py-20 text-center bg-white rounded-3xl border-2 border-dashed border-slate-200">
                        <i class="bi bi-map text-5xl text-slate-200 mb-4 block"></i>
                        <p class="text-slate-500 font-medium">No destinations found. Check back later!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

<footer class="bg-slate-900 text-slate-300 pt-20 pb-10">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
            
            <div class="space-y-6">
                <a href="index.php" class="flex items-center gap-3 text-white text-2xl font-extrabold italic">
                    <div class="bg-blue-600 p-2 rounded-xl shadow-lg shadow-blue-500/20">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <span>BookingMaster</span>
                </a>
                <p class="text-sm leading-relaxed">
                    Discover your next adventure with BookingMaster. We provide seamless booking experiences for the world's most beautiful destinations.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="h-10 w-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="#" class="h-10 w-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="#" class="h-10 w-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all">
                        <i class="bi bi-twitter-x"></i>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="text-white font-bold mb-6 uppercase text-xs tracking-widest">Explore</h4>
                <ul class="space-y-4 text-sm font-medium">
                    <li><a href="viewAllProduct.php" class="hover:text-blue-500 transition">All Destinations</a></li>
                    <li><a href="#" class="hover:text-blue-500 transition">Special Offers</a></li>
                    <li><a href="#" class="hover:text-blue-500 transition">Travel Guides</a></li>
                    <li><a href="#" class="hover:text-blue-500 transition">Travel Insurance</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-bold mb-6 uppercase text-xs tracking-widest">Support</h4>
                <ul class="space-y-4 text-sm font-medium">
                    <li><a href="userDashboard.php" class="hover:text-blue-500 transition">Your Account</a></li>
                    <li><a href="#" class="hover:text-blue-500 transition">Help Center</a></li>
                    <li><a href="#" class="hover:text-blue-500 transition">Terms of Service</a></li>
                    <li><a href="#" class="hover:text-blue-500 transition">Privacy Policy</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-bold mb-6 uppercase text-xs tracking-widest">Stay Updated</h4>
                <p class="text-sm mb-6">Subscribe to get the latest destination deals and travel tips.</p>
                <form class="space-y-3">
                    <input type="email" placeholder="Email address" class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                    <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold text-sm hover:bg-blue-700 transition shadow-lg shadow-blue-500/20">
                        Subscribe
                    </button>
                </form>
            </div>
        </div>

        <div class="pt-10 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-6">
            <p class="text-xs font-medium text-slate-500">
                &copy; <?= date('Y') ?> BookingMaster POS. All rights reserved.
            </p>
            <div class="flex items-center gap-6">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa" class="h-4 opacity-50 grayscale hover:grayscale-0 transition">
                <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" class="h-6 opacity-50 grayscale hover:grayscale-0 transition">
            </div>
        </div>
    </div>
</footer>

    <script>
        function toggleDropdown() {
            document.getElementById('profile-dropdown').classList.toggle('show');
        }

        window.onclick = function(event) {
            if (!event.target.closest('button')) {
                var dropdowns = document.getElementsByClassName("show");
                for (var i = 0; i < dropdowns.length; i++) {
                    dropdowns[i].classList.remove('show');
                }
            }
        }
    </script>
</body>
</html>