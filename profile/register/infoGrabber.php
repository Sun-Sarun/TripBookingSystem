<?php
session_start();
include '../../admin/config.php'; // Ensure this defines $conn

// Redirect back if they haven't come from the registration page
if (!isset($_SESSION['user_email'])) {
    header("Location: ../../home/index.php");
    exit();
}

$user_email = $_SESSION['user_email'];

// Handle the form submission
if (isset($_POST['btnadd'])) {
    // 1. Get the accountID from the account table based on the session email
    $acc_query = $conn->prepare("SELECT accountID FROM account WHERE email = ?");
    $acc_query->bind_param("s", $user_email);
    $acc_query->execute();
    $acc_result = $acc_query->get_result();
    $acc_data = $acc_result->fetch_assoc();
    $accountID = $acc_data['accountID'];

    // 2. Collect form data matching userinfo table columns
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $createdDate = date('Y-m-d');

    // 3. Handle Image Upload (mapping to 'profile' column in SQL)
    $profile_pic = "default.png";
    if (!empty($_FILES['pfp']['name'])) {
        $profile_pic = time() . '_' . $_FILES['pfp']['name'];
        // Ensure this directory exists and is writable
        move_uploaded_file($_FILES['pfp']['tmp_name'], "../../uploads/" . $profile_pic);
    }

    // 4. Insert into userinfo table
    // Note: accountID is a UNIQUE foreign key in your schema
    $stmt = $conn->prepare("INSERT INTO userinfo (accountID, FName, LName, gender, DOB, phone, email, createdDate, profile, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssssss", $accountID, $fname, $lname, $gender, $dob, $phone, $user_email, $createdDate, $profile_pic, $address);

    if ($stmt->execute()) {
        header("Location: ../../home/index.php"); // Forward to home.php as requested
        exit();
    } else {
        $error_msg = "Error saving profile details: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookingMaster | Complete Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Plus Jakarta Sans', 'sans-serif'] }, colors: { brand: '#3B82F6', dark: '#0F172A' } } }
        }
    </script>
    <style>
        .glass-panel { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .bg-image { background: linear-gradient(rgba(15, 23, 42, 0.05), rgba(15, 23, 42, 0.05)), url('https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=1920'); background-size: cover; background-attachment: fixed; }
        .floating-label:focus-within label, .floating-label input:not(:placeholder-shown) + label { transform: translateY(-1.5rem) scale(0.85); color: #3B82F6; }
    </style>
</head>
<body class="bg-image min-h-screen font-sans antialiased text-slate-900">

    <main class="container mx-auto px-6 py-12 flex justify-center">
        <div class="glass-panel w-full max-w-2xl rounded-[2.5rem] p-8 md:p-12 shadow-2xl">
            
            <div class="mb-10 flex items-center space-x-6">
                <div class="relative group">
                    <div class="w-24 h-24 rounded-3xl bg-slate-200 overflow-hidden border-4 border-white shadow-lg">
                        <img id="preview" src="https://i.pravatar.cc/150?u=profile" class="w-full h-full object-cover">
                    </div>
                    <label for="pfp" class="absolute -bottom-2 -right-2 w-8 h-8 bg-brand text-white rounded-xl flex items-center justify-center cursor-pointer hover:bg-blue-600 transition shadow-lg">
                        <i class="bi bi-camera-fill text-sm"></i>
                    </label>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Personal Details</h1>
                    <p class="text-slate-500 font-medium">Complete your profile to continue</p>
                </div>
            </div>

            <?php if(isset($error_msg)) echo "<p class='text-red-500 mb-4'>$error_msg</p>"; ?>

            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="file" name="pfp" id="pfp" class="hidden" onchange="previewImage(this)">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="relative floating-label">
                        <input type="text" name="fname" placeholder=" " required class="block w-full px-5 py-4 bg-white/50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all peer">
                        <label class="absolute left-5 top-4 text-slate-400 pointer-events-none transition-all origin-left font-medium">First Name</label>
                    </div>
                    <div class="relative floating-label">
                        <input type="text" name="lname" placeholder=" " required class="block w-full px-5 py-4 bg-white/50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all peer">
                        <label class="absolute left-5 top-4 text-slate-400 pointer-events-none transition-all origin-left font-medium">Last Name</label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="relative">
                        <label class="block text-[10px] uppercase tracking-widest font-bold text-slate-400 mb-2 ml-4">Gender</label>
                        <select name="gender" class="block w-full px-5 py-4 bg-white/50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all appearance-none cursor-pointer">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="relative">
                        <label class="block text-[10px] uppercase tracking-widest font-bold text-slate-400 mb-2 ml-4">Birth Date</label>
                        <input type="date" name="dob" required class="block w-full px-5 py-4 bg-white/50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all">
                    </div>
                </div>

                <div class="relative floating-label">
                    <input type="tel" name="phone" placeholder=" " required class="block w-full px-5 py-4 bg-white/50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all peer">
                    <label class="absolute left-5 top-4 text-slate-400 pointer-events-none transition-all origin-left font-medium">Phone Number</label>
                </div>

                <div class="relative floating-label">
                    <input type="text" name="address" placeholder=" " required class="block w-full px-5 py-4 bg-white/50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all peer">
                    <label class="absolute left-5 top-4 text-slate-400 pointer-events-none transition-all origin-left font-medium">Residential Address</label>
                </div>

                <button type="submit" name="btnadd" class="w-full bg-brand text-white py-4 rounded-2xl font-bold text-lg shadow-lg hover:bg-blue-600 transition-all">
                    Complete Registration
                </button>
            </form>
        </div>
    </main>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) { document.getElementById('preview').src = e.target.result; }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>