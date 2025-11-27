<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Bookkepz - Simplify Your Accounting</title>
    <link rel="stylesheet" href="assets/css/landingpage.css" />
    <link rel="icon" type="image/png" href="assets/img/bookkepz_logo.png" />
</head>
<body>

    <header class="navbar">
        <div class="container nav-container">
            <div class="logo">
                <a href="index" class="logo-link">
                    <img src="assets/img/bookkepz_logo.png" alt="Bookkepz Logo" />
                    <span>Bookkepz</span>
                </a>
            </div>
            <nav>
                <ul class="nav-links">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#whyus">Why Us</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </nav>
            <div class="nav-buttons">
                <a href="login" class="login-btn">Login</a>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-text">
                <h1>
                    Simplify your bookkeeping with <span>Bookkepz</span>
                </h1>
                <p>
                    Designed for Filipino entrepreneurs — manage invoices, track expenses, and
                    automate reports all in one beautiful dashboard.
                </p>
                <div class="hero-buttons">
                    <a href="register" class="btn-primary">Start Free Trial</a>
                </div>
                <p class="trial-note">14-day free trial • No credit card required</p>
            </div>
            <div class="hero-image">
                <img src="assets/img/landingpage.jpg" alt="Bookkepz Accounting Dashboard" />
            </div>
        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <h2>Everything You Need to Run Your Business</h2>
            <p class="subtitle">Smart tools built for Filipino entrepreneurs</p>
            <div class="feature-grid">
                <div class="feature-card">
                    <img src="assets/img/icon-invoice.png" alt="Invoices Icon" />
                    <h3>Professional Invoicing</h3>
                    <p>Create and send invoices in seconds with your logo and branding.</p>
                </div>
                <div class="feature-card">
                    <img src="assets/img/icon-expense.png" alt="Expenses Icon" />
                    <h3>Expense Tracking</h3>
                    <p>Keep tabs on your business expenses and cash flow with ease.</p>
                </div>
                <div class="feature-card">
                    <img src="assets/img/icon-tax.png" alt="Tax Icon" />
                    <h3>Tax-Ready Reports</h3>
                    <p>Generate summaries that make BIR compliance quick and simple.</p>
                </div>
                <div class="feature-card">
                    <img src="assets/img/icon-receipt.png" alt="Receipt OCR Icon" />
                    <h3>Receipt OCR Scanner</h3>
                    <p>Snap a photo of your receipt and let Bookkepz handle the rest.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="whyus" class="why-us">
        <div class="container">
            <h2>Why Businesses Love Bookkepz</h2>
            <div class="why-grid">
                <div class="why-card">
                    <img src="assets/img/peso.png" alt="Peso Currency" />
                    <h3>Made for Filipinos</h3>
                    <p>Localized currency, tax system, and support — all in one tool.</p>
                </div>
                <div class="why-card">
                    <img src="assets/img/sari-sari.png" alt="Small Business Icon" />
                    <h3>Perfect for Small Businesses</h3>
                    <p>From freelancers to sari-sari stores, we scale with your business.</p>
                </div>
                <div class="why-card">
                    <img src="assets/img/jeepney.png" alt="Jeepney for Local Support" />
                    <h3>Local Support</h3>
                    <p>Talk to real people who understand Filipino businesses.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="container">
            <h2>Start your free trial today</h2>
            <p>Join hundreds of Filipino entrepreneurs managing their finances smarter.</p>
            <a href="register" class="btn-glow">Start Free Trial</a>
        </div>
    </section>

    <section id="contact" class="contact">
        <div class="container">
            <h2>Contact Us</h2>
            <p>We’d love to hear from you! Send us a message below.</p>

            <form class="contact-form" action="send_message.php" method="POST">
                <input type="text" name="name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email" required>
                <textarea name="message" rows="4" placeholder="Your Message" required></textarea>
                <button type="submit" class="btn-primary">Send Message</button>
            </form>
        </div>
    </section>

    <footer>
        <div class="footer-top">
            <div class="footer-logo">
                <img src="assets/img/bookkepz_logo.png" alt="Bookkepz Logo">
                <p>Empowering Filipino businesses with smarter accounting solutions.</p>
            </div>

            <div class="footer-section">
                <h4>Company</h4>
                <ul>
                    <li><a href="service">Services</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Resources</h4>
                <ul>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Terms</a></li>
                    <li><a href="#">Privacy</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            © 2025 Bookkepz • All Rights Reserved
        </div>

        <script type="module">
            // Import the functions you need from the SDKs you need
            import { initializeApp } from "firebase/app";
            import { getAnalytics } from "firebase/analytics";
            
            // Your web app's Firebase configuration
            const firebaseConfig = {
                apiKey: "AIzaSyDEiOaKNeH-H9oD4mS9GwvxnRyMzPPnxNQ",
                authDomain: "bookkepzph.firebaseapp.com",
                projectId: "bookkepzph",
                storageBucket: "bookkepzph.firebasestorage.app",
                messagingSenderId: "912770902486",
                appId: "1:912770902486:web:ee6b72f51a2ae89abddc77",
                measurementId: "G-XL2QWZ2Z5Z"
            };

            // Initialize Firebase
            const app = initializeApp(firebaseConfig);
            const analytics = getAnalytics(app);
            
            // You can now export the app and analytics objects here 
            // if you move this code into a separate .js file, 
            // or use them directly in subsequent <script type="module"> blocks.
        </script>
        </footer>
</body>
</html>