<?php
session_start();
include '../../admin/config.php'; // Fixed the space error

$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $check_stmt = $conn->prepare("SELECT email FROM account WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $error_msg = "This email is already registered.";
    } else {
        $stmt = $conn->prepare("INSERT INTO account (email, password, permission) VALUES (?, ?,'user')");
        $stmt->bind_param("ss", $email, $password);
        if ($stmt->execute()) {
            $_SESSION['user_email'] = $email; // Store email for the next page
            header("Location: infoGrabber.php");
            exit();
        } else {
            $error_msg = "Database error.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookingMaster | Create Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
        .glass-panel { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .bg-image { background: linear-gradient(rgba(15, 23, 42, 0.5), rgba(15, 23, 42, 0.5)), url('https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1920'); background-size: cover; background-position: center; }
        .floating-label:focus-within label, .floating-label input:not(:placeholder-shown) + label { transform: translateY(-1.5rem) scale(0.85); color: #3B82F6; }
    </style>
</head>
<body class="bg-dark font-sans antialiased">

    <div class="bg-image min-h-screen flex items-center justify-center p-6">
        <div class="glass-panel w-full max-w-md rounded-[2.5rem] p-8 md:p-10 shadow-2xl relative overflow-hidden">
            
            <div class="text-center mb-8">
                <a href="../../home/">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-brand/10 text-brand rounded-2xl mb-4">
                        <i class="bi bi-person-plus-fill text-2xl"></i>
                    </div>
                </a>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Create Account</h2>
                <p class="text-slate-500 mt-2 font-medium">Join BookingMaster to start your journey</p>
                <?php if($error_msg): ?>
                    <p class="text-red-500 text-sm mt-2 bg-red-50 p-2 rounded-lg"><?php echo $error_msg; ?></p>
                <?php endif; ?>
            </div>

            <form id="registrationForm" class="space-y-5" method="POST" >
                
                <div class="relative floating-label">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <input type="email" id="email" name="email" placeholder=" " required
                        class="block w-full pl-11 pr-4 py-3.5 bg-white/50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all peer">
                    <label for="email" class="absolute left-11 top-3.5 text-slate-400 pointer-events-none transition-all origin-left">Email Address</label>
                </div>

                <div class="relative floating-label">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="bi bi-lock"></i>
                    </div>
                    <input type="password" id="password" name="password" placeholder=" " required
                        class="block w-full pl-11 pr-12 py-3.5 bg-white/50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all peer">
                    <label for="password" class="absolute left-11 top-3.5 text-slate-400 pointer-events-none transition-all origin-left">Password</label>
                    <button type="button" onclick="togglePassword('password', 'eyeIcon1')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-brand transition">
                        <i id="eyeIcon1" class="bi bi-eye"></i>
                    </button>
                </div>

                <div class="relative floating-label">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <input type="password" id="confirmPassword" placeholder=" " required
                        class="block w-full pl-11 pr-12 py-3.5 bg-white/50 border border-slate-200 rounded-2xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all peer">
                    <label for="confirmPassword" class="absolute left-11 top-3.5 text-slate-400 pointer-events-none transition-all origin-left">Confirm Password</label>
                    <button type="button" onclick="togglePassword('confirmPassword', 'eyeIcon2')" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-brand transition">
                        <i id="eyeIcon2" class="bi bi-eye"></i>
                    </button>
                </div>

                <div class="flex items-start space-x-2 px-1">
                    <input type="checkbox" id="terms" required class="mt-1 w-4 h-4 rounded border-gray-300 text-brand focus:ring-brand">
                    <label for="terms" class="text-xs text-slate-500 leading-tight">
                        I agree to the <a href="#" class="text-brand font-bold hover:underline">Terms of Service</a> and <a href="#" class="text-brand font-bold hover:underline">Privacy Policy</a>.
                    </label>
                </div>

                <button type="submit" id="submitBtn" class="w-full bg-brand text-white py-4 rounded-2xl font-bold text-lg shadow-lg shadow-blue-200 hover:bg-blue-600 active:scale-[0.98] transition-all flex items-center justify-center gap-2 mt-4">
                    <span>Create Account</span>
                    <div id="loader" class="hidden animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></div>
                </button>
            </form>

            <p class="text-center mt-8 text-slate-500 font-medium">
                Already have an account? <a href="../login/" class="text-brand font-bold hover:underline ml-1">Log in</a>
            </p>

            <div id="successOverlay" class="<?php echo $success ? '' : 'hidden'; ?> absolute inset-0 bg-white flex flex-col items-center justify-center text-center p-8 z-50">
                <div class="w-20 h-20 bg-green-100 text-green-500 rounded-full flex items-center justify-center text-4xl mb-6 animate-bounce">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-900">Account Created!</h3>
                <p class="text-slate-500 mt-2">Redirecting to login...</p>
            </div>

        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const pwd = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }

        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const pass = document.getElementById('password').value;
            const confirm = document.getElementById('confirmPassword').value;

            if (pass !== confirm) {
                e.preventDefault();
                alert("Passwords do not match!");
                return;
            }

            // Show UI feedback, then allow standard form submission to PHP
            const btn = document.getElementById('submitBtn');
            const loader = document.getElementById('loader');
            btn.disabled = true;
            btn.querySelector('span').innerText = 'Processing...';
            loader.classList.remove('hidden');
        });

        // Redirect if success overlay is visible
        <?php if($success): ?>
        setTimeout(() => {
            window.location.href = '../login/'; 
        }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>