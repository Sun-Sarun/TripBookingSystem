<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookingMaster</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<header>
    <nav>
        <div class="logo">BookingMaster</div>
        <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#">Destinations</a></li>
            <li><a href="#">Package</a></li>
            <li><a href="#">Rental</a></li>
        </ul>
    </nav>
</header>

<section class="hero">
    <h1>Explore the World</h1>
    <p>Find the best deals on flights and hotels.</p>
    
    <div class="booking-container">
        <form action="process_booking.php" method="POST">
            <input type="text" name="destination" placeholder="Where to?" required>
            <input type="date" name="check_in" required>
            <input type="number" name="guests" placeholder="Guests" min="1" required>
            <button type="submit" name="search">Search Now</button>
        </form>
    </div>
</section>

<section class="destinations">
    <h2>Travel Package</h2>
    <div class="scroll-wrapper">
        <div class="scroll-container">
            <div class="card">
                <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=400" alt="Paris">
                <h3>Paris, France</h3>
                <p>From $499</p>
            </div>
            <div class="card">
                <img src="https://images.unsplash.com/photo-1523906834658-6e24ef2386f9?auto=format&fit=crop&w=400" alt="Venice">
                <h3>Venice, Italy</h3>
                <p>From $550</p>
            </div>
            <div class="card">
                <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=400" alt="Dubai">
                <h3>Dubai, UAE</h3>
                <p>From $800</p>
            </div>
            <div class="card">
                <img src="https://images.unsplash.com/photo-1500835595353-b0ad2e58b8df?auto=format&fit=crop&w=400" alt="Tokyo">
                <h3>Tokyo, Japan</h3>
                <p>From $950</p>
            </div>
            <div class="card">
                <img src="https://images.unsplash.com/photo-1525625239911-4f5d7222eeed?auto=format&fit=crop&w=400" alt="New York">
                <h3>New York, USA</h3>
                <p>From $620</p>
            </div>
            <div class="card">
                <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=400" alt="Bali">
                <h3>Bali, Indonesia</h3>
                <p>From $420</p>
            </div>
        </div>
    </div>
</section>
<section class="destinations">
    <h2>Travel Destinations</h2>
    <div class="scroll-wrapper">
        <div class="scroll-container">
            <div class="card">
                <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=400" alt="Paris">
                <h3>Paris, France</h3>
                <p>From $499</p>
            </div>
            <div class="card">
                <img src="https://images.unsplash.com/photo-1523906834658-6e24ef2386f9?auto=format&fit=crop&w=400" alt="Venice">
                <h3>Venice, Italy</h3>
                <p>From $550</p>
            </div>
            <div class="card">
                <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=400" alt="Dubai">
                <h3>Dubai, UAE</h3>
                <p>From $800</p>
            </div>
            <div class="card">
                <img src="https://images.unsplash.com/photo-1500835595353-b0ad2e58b8df?auto=format&fit=crop&w=400" alt="Tokyo">
                <h3>Tokyo, Japan</h3>
                <p>From $950</p>
            </div>
            <div class="card">
                <img src="https://images.unsplash.com/photo-1525625239911-4f5d7222eeed?auto=format&fit=crop&w=400" alt="New York">
                <h3>New York, USA</h3>
                <p>From $620</p>
            </div>
            <div class="card">
                <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=400" alt="Bali">
                <h3>Bali, Indonesia</h3>
                <p>From $420</p>
            </div>
        </div>
    </div>
</section>
<section class="destinations">
    <h2>Rental</h2>
    <div class="scroll-wrapper">
        <div class="scroll-container">
            <div class="card">
                <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=400" alt="Paris">
                <h3>Paris, France</h3>
                <p>From $499</p>
            </div>
            <div class="card">
                <img src="https://images.unsplash.com/photo-1523906834658-6e24ef2386f9?auto=format&fit=crop&w=400" alt="Venice">
                <h3>Venice, Italy</h3>
                <p>From $550</p>
            </div>
            <div class="card">
                <img src="https://images.unsplash.com/photo-1512453979798-5ea266f8880c?auto=format&fit=crop&w=400" alt="Dubai">
                <h3>Dubai, UAE</h3>
                <p>From $800</p>
            </div>
            <div class="card">
                <img src="https://images.unsplash.com/photo-1500835595353-b0ad2e58b8df?auto=format&fit=crop&w=400" alt="Tokyo">
                <h3>Tokyo, Japan</h3>
                <p>From $950</p>
            </div>
            <div class="card">
                <img src="https://images.unsplash.com/photo-1525625239911-4f5d7222eeed?auto=format&fit=crop&w=400" alt="New York">
                <h3>New York, USA</h3>
                <p>From $620</p>
            </div>
            <div class="card">
                <img src="https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=400" alt="Bali">
                <h3>Bali, Indonesia</h3>
                <p>From $420</p>
            </div>
        </div>
    </div>
</section>
</body>
</html>