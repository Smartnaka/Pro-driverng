<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="ProDrivers - Your Personal Driver, On Demand. Book professional drivers for any occasion.">
  <title>ProDrivers</title>
  <link rel="icon" href="images/driver.webp" type="image/webp">
  <link crossorigin href="https://fonts.gstatic.com/" rel="preconnect"/>
  <link as="style" href="https://fonts.googleapis.com/css2?display=swap&family=Inter:wght@400;500;700;900&family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700" onload="this.rel='stylesheet'" rel="stylesheet"/>
  <!-- Tailwind Fallback: Use local CSS if CDN fails -->
  <link rel="stylesheet" href="assets/css/tailwind.min.css">
  <!-- Tailwind CDN (preferred) -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <noscript><style>.sm\:hidden { display: none !important; }</style><div style="background: #ffc; color: #333; padding: 8px; text-align: center;">JavaScript is required for full site styling. Please enable JavaScript or use a modern browser.</div></noscript>
  <style type="text/tailwindcss">
        :root {
            --primary-color: #003366;
      --secondary-color: #f0f2f5;
      --background-color: #ffffff;
      --text-primary: #333333;
      --text-secondary: #666666;
      --accent-color: #ffc107;
    }
        body {
      font-family: 'Poppins', 'Inter', 'Roboto', sans-serif;
        }
    </style>
</head>
<body class="bg-[var(--background-color)] text-[var(--text-primary)]">
<div class="relative flex size-full min-h-screen flex-col group/design-root overflow-x-hidden">
<div class="layout-container flex h-full grow flex-col">
<header class="sticky top-0 z-10 flex items-center justify-between whitespace-nowrap border-b border-solid border-b-[#f0f2f5] bg-white px-4 sm:px-10 py-4 shadow-sm">
<div class="flex items-center gap-4 text-[var(--primary-color)]">
<svg class="h-8 w-8" fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
<path d="M44 4H30.6666V17.3334H17.3334V30.6666H4V44H44V4Z" fill="currentColor"></path>
</svg>
<h2 class="text-2xl font-bold tracking-tight">ProDrivers</h2>
            </div>
  <!-- Hamburger for mobile -->
  <button id="mobile-menu-toggle" class="sm:hidden p-2 rounded focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)]" aria-label="Open Menu">
    <svg class="h-6 w-6 text-[var(--primary-color)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
  </button>
  <div id="navbar-links" class="hidden sm:flex flex-1 justify-end gap-8">
    <nav class="flex flex-col sm:flex-row items-center gap-4 sm:gap-9 bg-white sm:bg-transparent absolute sm:static top-16 left-0 w-full sm:w-auto shadow sm:shadow-none z-20 p-4 sm:p-0" style="display:none;">
<a class="text-base font-medium text-[var(--text-secondary)] hover:text-[var(--primary-color)] transition-colors" href="#about">About</a>
<a class="text-base font-medium text-[var(--text-secondary)] hover:text-[var(--primary-color)] transition-colors" href="#services">Services</a>
<a class="text-base font-medium text-[var(--text-secondary)] hover:text-[var(--primary-color)] transition-colors" href="#pricing">Pricing</a>
<a class="text-base font-medium text-[var(--text-secondary)] hover:text-[var(--primary-color)] transition-colors" href="#contact">Contact</a>
    </nav>
    <div class="flex flex-col sm:flex-row gap-3 mt-4 sm:mt-0">
<a href="register.php" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-md h-11 px-5 bg-[var(--primary-color)] text-white text-base font-semibold tracking-wide shadow-sm hover:bg-opacity-90 transition-all">
<span class="truncate">Sign Up</span>
</a>
<a href="login.php" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-md h-11 px-5 bg-[var(--secondary-color)] text-[var(--primary-color)] text-base font-semibold tracking-wide shadow-sm hover:bg-gray-200 transition-all">
<span class="truncate">Login</span>
</a>
                    </div>
                </div>
</header>
<script>
  // Mobile menu toggle
  const toggleBtn = document.getElementById('mobile-menu-toggle');
  const navLinks = document.getElementById('navbar-links');
  if (toggleBtn && navLinks) {
    toggleBtn.addEventListener('click', () => {
      navLinks.classList.toggle('hidden');
      const nav = navLinks.querySelector('nav');
      if (nav) nav.style.display = navLinks.classList.contains('hidden') ? 'none' : 'block';
    });
  }
</script>
<main class="flex flex-1 justify-center py-5">
<div class="layout-content-container flex flex-col max-w-6xl flex-1">
<section class="relative rounded-xl overflow-hidden min-h-[320px] sm:min-h-[520px] flex flex-col items-start justify-end p-4 sm:p-12" style='background-image: linear-gradient(to top, rgba(0, 0, 0, 0.6) 0%, rgba(0, 0, 0, 0) 100%), url("images/driver2.jpg"); background-size: cover; background-position: center;'>
<div class="flex flex-col gap-4 text-left max-w-2xl">
<h1 class="text-white text-3xl sm:text-5xl font-bold leading-tight tracking-tighter">Your Personal Driver, On Demand</h1>
<p class="text-white text-base sm:text-lg font-light leading-relaxed">
Experience the convenience and luxury of having a professional driver at your service. Book rides for any occasion, from airport transfers to corporate events.
</p>
                </div>
<div class="flex flex-col sm:flex-row flex-wrap gap-4 mt-8 w-full sm:w-auto">
<a href="register.php" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-md h-12 px-6 bg-[var(--primary-color)] text-white text-base font-semibold tracking-wide shadow-lg hover:bg-opacity-90 transition-all transform hover:-translate-y-1">
<span class="truncate">Book a Ride</span>
</a>
<a href="#about" class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-md h-12 px-6 bg-[var(--secondary-color)] text-[var(--primary-color)] text-base font-semibold tracking-wide shadow-lg hover:bg-gray-200 transition-all transform hover:-translate-y-1">
<span class="truncate">Learn More</span>
</a>
        </div>
    </section>

<!-- About Section -->
<section id="about" class="px-4 py-20 bg-white border-b border-gray-100">
  <div class="max-w-4xl mx-auto text-center">
    <h2 class="text-4xl font-bold mb-4 text-[var(--primary-color)]">About ProDrivers</h2>
    <p class="text-lg text-[var(--text-secondary)] mb-6">ProDrivers is Nigeria’s leading professional driver service, dedicated to providing safe, reliable, and premium transportation for individuals and businesses. With a focus on customer satisfaction, we make every journey seamless, comfortable, and secure.</p>
    <p class="text-base text-[var(--text-secondary)]">Our mission is to redefine urban mobility by connecting clients with thoroughly vetted, experienced drivers for airport transfers, corporate travel, events, and daily commutes. We are committed to punctuality, professionalism, and the highest safety standards.</p>
            </div>
</section>

<!-- Services Section -->
<section id="services" class="px-2 sm:px-4 py-10 sm:py-20 bg-[var(--secondary-color)] border-b border-gray-100">
  <div class="max-w-5xl mx-auto text-center">
    <h2 class="text-2xl sm:text-4xl font-bold mb-4 text-[var(--primary-color)]">Our Services</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8 mt-6 sm:mt-10">
      <div class="bg-white rounded-xl shadow p-8 flex flex-col items-center">
        <svg class="h-10 w-10 text-[var(--primary-color)] mb-4" fill="none" viewBox="0 0 48 48"><circle cx="24" cy="24" r="22" stroke="currentColor" stroke-width="4" fill="none"/><path d="M16 32l8-8 8 8" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <h3 class="font-semibold text-lg mb-2">Airport Transfers</h3>
        <p class="text-[var(--text-secondary)]">On-time pickups and drop-offs to and from all major airports in Lagos and Abuja. Enjoy stress-free travel with our professional drivers.</p>
                        </div>
      <div class="bg-white rounded-xl shadow p-8 flex flex-col items-center">
        <svg class="h-10 w-10 text-[var(--primary-color)] mb-4" fill="none" viewBox="0 0 48 48"><rect x="8" y="16" width="32" height="16" rx="8" stroke="currentColor" stroke-width="4" fill="none"/><path d="M16 24h16" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
        <h3 class="font-semibold text-lg mb-2">Corporate Chauffeur</h3>
        <p class="text-[var(--text-secondary)]">Professional drivers for executives, business meetings, and company events. Arrive in style and on schedule, every time.</p>
                    </div>
      <div class="bg-white rounded-xl shadow p-8 flex flex-col items-center">
        <svg class="h-10 w-10 text-[var(--primary-color)] mb-4" fill="none" viewBox="0 0 48 48"><rect x="12" y="12" width="24" height="24" rx="12" stroke="currentColor" stroke-width="4" fill="none"/><path d="M24 16v8l6 6" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <h3 class="font-semibold text-lg mb-2">Event & Hourly Hire</h3>
        <p class="text-[var(--text-secondary)]">Hire a driver for weddings, parties, or by the hour. Flexible packages to suit your schedule and needs.</p>
                    </div>
                </div>
                        </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="px-2 sm:px-4 py-10 sm:py-20 bg-white border-b border-gray-100">
  <div class="max-w-4xl mx-auto text-center">
    <h2 class="text-2xl sm:text-4xl font-bold mb-4 text-[var(--primary-color)]">Pricing</h2>
    <p class="text-base sm:text-lg text-[var(--text-secondary)] mb-8">Transparent, competitive rates. No hidden fees. Contact us for custom packages or long-term contracts.</p>
    <div class="flex flex-col md:flex-row justify-center gap-6 sm:gap-8">
      <div class="bg-[var(--secondary-color)] rounded-xl shadow p-8 flex-1">
        <h3 class="font-semibold text-xl mb-2">Airport Transfer</h3>
        <p class="text-3xl font-bold text-[var(--primary-color)] mb-2">₦8,000<span class="text-base font-normal">/trip</span></p>
        <ul class="text-[var(--text-secondary)] mb-4 text-left list-disc list-inside">
          <li>Pickup & drop-off</li>
          <li>Professional driver</li>
          <li>Flight tracking</li>
        </ul>
        <a href="book-driver.php" class="inline-block mt-2 px-6 py-2 rounded bg-[var(--primary-color)] text-white font-semibold hover:bg-opacity-90 transition">Book Now</a>
                        </div>
      <div class="bg-[var(--primary-color)] text-white rounded-xl shadow p-8 flex-1">
        <h3 class="font-semibold text-xl mb-2">Corporate Chauffeur</h3>
        <p class="text-3xl font-bold mb-2">₦12,000<span class="text-base font-normal">/day</span></p>
        <ul class="mb-4 text-left list-disc list-inside">
          <li>8 hours service</li>
          <li>Executive vehicle</li>
          <li>Priority support</li>
        </ul>
        <a href="book-driver.php" class="inline-block mt-2 px-6 py-2 rounded bg-[var(--accent-color)] text-[var(--primary-color)] font-semibold hover:bg-opacity-90 transition">Book Now</a>
                    </div>
      <div class="bg-[var(--secondary-color)] rounded-xl shadow p-8 flex-1 mt-8 md:mt-0">
        <h3 class="font-semibold text-xl mb-2">Hourly Hire</h3>
        <p class="text-3xl font-bold text-[var(--primary-color)] mb-2">₦2,000<span class="text-base font-normal">/hour</span></p>
        <ul class="text-[var(--text-secondary)] mb-4 text-left list-disc list-inside">
          <li>Flexible hours</li>
          <li>Any occasion</li>
          <li>Minimum 2 hours</li>
        </ul>
        <a href="book-driver.php" class="inline-block mt-2 px-6 py-2 rounded bg-[var(--primary-color)] text-white font-semibold hover:bg-opacity-90 transition">Book Now</a>
                </div>
            </div>
        </div>
    </section>

<section id="testimonials" class="px-2 sm:px-4 py-10 sm:py-20">
<h2 class="text-center text-[var(--text-primary)] text-2xl sm:text-4xl font-bold tracking-tight mb-8 sm:mb-12">What Our Clients Say</h2>
<div class="flex overflow-x-auto snap-x snap-mandatory [-ms-scrollbar-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden pb-8">
<div class="flex items-stretch p-2 sm:p-4 gap-4 sm:gap-8">
<div class="flex flex-col gap-6 rounded-xl min-w-80 w-80 bg-white p-8 shadow-lg snap-center">
<div class="flex-1">
<p class="text-[var(--text-secondary)] text-base italic">"ProDrivers made my business trips in Lagos effortless. The drivers are always on time, courteous, and the cars are spotless. Highly recommended!"</p>
</div>
<div class="flex items-center gap-4">
<img alt="Chinedu Okafor" class="size-14 rounded-full object-cover" src="images/testimonial-chinedu.jpg">
<div>
<p class="text-[var(--text-primary)] text-base font-semibold">Chinedu Okafor</p>
<p class="text-[var(--accent-color)] text-sm font-medium">Operations Manager, Zenith Bank</p>
            </div>
                    </div>
                </div>
<div class="flex flex-col gap-6 rounded-xl min-w-80 w-80 bg-white p-8 shadow-lg snap-center">
<div class="flex-1">
<p class="text-[var(--text-secondary)] text-base italic">"I booked ProDrivers for my wedding and everything was perfect. The driver was professional and made sure we arrived everywhere on time."</p>
</div>
<div class="flex items-center gap-4">
<img alt="Amina Bello" class="size-14 rounded-full object-cover" src="images/testimonial-amina.jpg">
<div>
<p class="text-[var(--text-primary)] text-base font-semibold">Amina Bello</p>
<p class="text-[var(--accent-color)] text-sm font-medium">Bride</p>
                    </div>
                </div>
                    </div>
<div class="flex flex-col gap-6 rounded-xl min-w-80 w-80 bg-white p-8 shadow-lg snap-center">
<div class="flex-1">
<p class="text-[var(--text-secondary)] text-base italic">"Excellent service for our executives. Booking is easy and the drivers are always professional. We use ProDrivers for all our corporate needs."</p>
                </div>
<div class="flex items-center gap-4">
<img alt="Tunde Alabi" class="size-14 rounded-full object-cover" src="images/testimonial-tunde.jpg">
<div>
<p class="text-[var(--text-primary)] text-base font-semibold">Tunde Alabi</p>
<p class="text-[var(--accent-color)] text-sm font-medium">Admin Lead, GTBank</p>
</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<section class="bg-[var(--primary-color)] text-white px-4 py-20">
<div class="flex flex-col items-center justify-center gap-6 text-center">
<h2 class="text-4xl font-bold tracking-tight">Ready to get started?</h2>
<div class="flex flex-wrap gap-4 justify-center">
<a href="book-driver.php" class="flex min-w-[180px] cursor-pointer items-center justify-center overflow-hidden rounded-md h-12 px-6 bg-[var(--accent-color)] text-[var(--primary-color)] text-base font-semibold shadow-lg hover:bg-opacity-90 transition-all transform hover:-translate-y-1">
<span class="truncate">Book a Ride</span>
</a>
<a href="driver/register.php" class="flex min-w-[180px] cursor-pointer items-center justify-center overflow-hidden rounded-md h-12 px-6 bg-white text-[var(--primary-color)] text-base font-semibold shadow-lg hover:bg-gray-100 transition-all transform hover:-translate-y-1">
<span class="truncate">Register as a Driver</span>
</a>
            </div>
        </div>
    </section>
                    </div>
</main>

<!-- Contact Section -->
<section id="contact" class="px-2 sm:px-4 py-10 sm:py-20 bg-[var(--secondary-color)]">
  <div class="max-w-3xl mx-auto text-center">
    <h2 class="text-2xl sm:text-4xl font-bold mb-4 text-[var(--primary-color)]">Contact Us</h2>
    <p class="text-base sm:text-lg text-[var(--text-secondary)] mb-8">Have questions or need help? Reach out to our team and we’ll get back to you promptly.</p>
    <form class="grid grid-cols-1 gap-4 sm:gap-6 max-w-xl mx-auto" action="contact-us.php" method="POST">
      <input type="text" name="name" placeholder="Your Name" required class="rounded border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)]">
      <input type="email" name="email" placeholder="Your Email" required class="rounded border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)]">
      <textarea name="message" placeholder="Your Message" required rows="4" class="rounded border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)]"></textarea>
      <button type="submit" class="w-full rounded bg-[var(--primary-color)] text-white font-semibold py-3 hover:bg-opacity-90 transition">Send Message</button>
    </form>
    <div class="mt-8 text-base text-[var(--text-secondary)]">
      <p><strong>Phone:</strong> <a href="tel:+2348000000000" class="text-[var(--primary-color)] hover:underline">+234 800 000 0000</a></p>
      <p><strong>Email:</strong> <a href="mailto:support@prodrivers.ng" class="text-[var(--primary-color)] hover:underline">support@prodrivers.ng</a></p>
      <p><strong>Office:</strong> 12B Adeola Odeku Street, Victoria Island, Lagos, Nigeria</p>
                    </div>
                </div>
</section>

<footer class="bg-[var(--secondary-color)]">
<div class="max-w-6xl mx-auto px-2 sm:px-5 py-8 sm:py-10">
<div class="flex flex-col md:flex-row items-center justify-between gap-6 sm:gap-8">
<div class="flex items-center gap-4 text-[var(--primary-color)]">
<svg class="h-8 w-8" fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
<path d="M44 4H30.6666V17.3334H17.3334V30.6666H4V44H44V4Z" fill="currentColor"></path>
</svg>
<h2 class="text-2xl font-bold tracking-tight">ProDrivers</h2>
                </div>
<div class="flex flex-wrap items-center justify-center gap-x-8 gap-y-4">
<a class="text-[var(--text-secondary)] text-base font-medium hover:text-[var(--primary-color)] transition-colors" href="#about">About Us</a>
<a class="text-[var(--text-secondary)] text-base font-medium hover:text-[var(--primary-color)] transition-colors" href="#contact">Contact</a>
<a class="text-[var(--text-secondary)] text-base font-medium hover:text-[var(--primary-color)] transition-colors" href="#">Terms of Service</a>
<a class="text-[var(--text-secondary)] text-base font-medium hover:text-[var(--primary-color)] transition-colors" href="#">Privacy Policy</a>
                </div>
<div class="flex flex-col gap-2 text-[var(--text-secondary)] text-sm">
  <span><strong>Phone:</strong> <a href="tel:+2348000000000" class="text-[var(--primary-color)] hover:underline">+234 800 000 0000</a></span>
  <span><strong>Email:</strong> <a href="mailto:support@prodrivers.ng" class="text-[var(--primary-color)] hover:underline">support@prodrivers.ng</a></span>
  <span><strong>Office:</strong> 12B Adeola Odeku Street, Victoria Island, Lagos, Nigeria</span>
                </div>
<div class="flex justify-center gap-4 mt-4 md:mt-0">
<a class="text-[var(--text-secondary)] hover:text-[var(--primary-color)] transition-colors" href="https://twitter.com/prodriversng" target="_blank" rel="noopener">
<svg fill="currentColor" height="24px" viewBox="0 0 256 256" width="24px" xmlns="http://www.w3.org/2000/svg">
<path d="M247.39,68.94A8,8,0,0,0,240,64H209.57A48.66,48.66,0,0,0,168.1,40a46.91,46.91,0,0,0-33.75,13.7A47.9,47.9,0,0,0,120,88v6.09C79.74,83.47,46.81,50.72,46.46,50.37a8,8,0,0,0-13.65,4.92c-4.31,47.79,9.57,79.77,22,98.18a110.93,110.93,0,0,0,21.88,24.2c-15.23,17.53-39.21,26.74-39.47,26.84a8,8,0,0,0-3.85,11.93c.75,1.12,3.75,5.05,11.08,8.72C53.51,229.7,65.48,232,80,232c70.67,0,129.72-54.42,135.75-124.44l29.91-29.9A8,8,0,0,0,247.39,68.94Zm-45,29.41a8,8,0,0,0-2.32,5.14C196,166.58,143.28,216,80,216c-10.56,0-18-1.4-23.22-3.08,11.51-6.25,27.56-17,37.88-32.48A8,8,0,0,0,92,169.08c-.47-.27-43.91-26.34-44-96,16,13,45.25,33.17,78.67,38.79A8,8,0,0,0,136,104V88a32,32,0,0,1,9.6-22.92A30.94,30.94,0,0,1,167.9,56c12.66.16,24.49,7.88,29.44,19.21A8,8,0,0,0,204.67,80h16Z"></path>
</svg>
</a>
<a class="text-[var(--text-secondary)] hover:text-[var(--primary-color)] transition-colors" href="https://facebook.com/prodriversng" target="_blank" rel="noopener">
<svg fill="currentColor" height="24px" viewBox="0 0 256 256" width="24px" xmlns="http://www.w3.org/2000/svg">
<path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm8,191.63V152h24a8,8,0,0,0,0-16H136V112a16,16,0,0,1,16-16h16a8,8,0,0,0,0-16H152a32,32,0,0,0-32,32v24H96a8,8,0,0,0,0,16h24v63.63a88,88,0,1,1,16,0Z"></path>
</svg>
</a>
<a class="text-[var(--text-secondary)] hover:text-[var(--primary-color)] transition-colors" href="https://instagram.com/prodriversng" target="_blank" rel="noopener">
<svg fill="currentColor" height="24px" viewBox="0 0 256 256" width="24px" xmlns="http://www.w3.org/2000/svg">
<path d="M128,80a48,48,0,1,0,48,48A48.05,48.05,0,0,0,128,80Zm0,80a32,32,0,1,1,32-32A32,32,0,0,1,128,160ZM176,24H80A56.06,56.06,0,0,0,24,80v96a56.06,56.06,0,0,0,56,56h96a56.06,56.06,0,0,0,56-56V80A56.06,56.06,0,0,0,176,24Zm40,152a40,40,0,0,1-40,40H80a40,40,0,0,1-40-40V80A40,40,0,0,1,80,40h96a40,40,0,0,1,40,40ZM192,76a12,12,0,1,1-12-12A12,12,0,0,1,192,76Z"></path>
</svg>
</a>
<a class="text-[var(--text-secondary)] hover:text-[var(--primary-color)] transition-colors" href="https://linkedin.com/company/prodriversng" target="_blank" rel="noopener">
<svg fill="currentColor" height="24px" viewBox="0 0 256 256" width="24px" xmlns="http://www.w3.org/2000/svg">
<path d="M44 4H30.6666V17.3334H17.3334V30.6666H4V44H44V4Z" fill="currentColor"></path>
</svg>
</a>
                </div>
            </div>
<div class="mt-8 border-t border-gray-300 pt-4 sm:pt-6 text-center">
<p class="text-[var(--text-secondary)] text-xs sm:text-sm">© 2024 ProDrivers. All rights reserved.</p>
            </div>
        </div>
    </footer>
</div>
</div>

</body>
</html> 