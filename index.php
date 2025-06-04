<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pro-drivers - Premium Driver Services</title>
<script src="https://cdn.tailwindcss.com/3.4.16"></script>
<script>tailwind.config={theme:{extend:{colors:{primary:'#3b82f6',secondary:'#10b981'},borderRadius:{'none':'0px','sm':'4px',DEFAULT:'8px','md':'12px','lg':'16px','xl':'20px','2xl':'24px','3xl':'32px','full':'9999px','button':'8px'}}}}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
<style>
:where([class^="ri-"])::before { content: "\f3c2"; }
body {
font-family: 'Inter', sans-serif;
}
.hero-section {
background-image: url('https://readdy.ai/api/search-image?query=Luxury%20cars%20and%20professional%20chauffeurs%20standing%20in%20front%20of%20high-end%20vehicles.%20The%20image%20shows%20a%20clean%2C%20minimalist%20background%20with%20soft%20lighting%20highlighting%20the%20premium%20vehicles.%20The%20left%20side%20has%20a%20gradient%20transition%20to%20a%20clean%20white%20background%2C%20allowing%20text%20to%20be%20clearly%20visible.%20Professional%20drivers%20in%20formal%20attire%20stand%20confidently.%20Modern%20architecture%20visible%20in%20background.&width=1600&height=800&seq=1&orientation=landscape');
background-size: cover;
background-position: right center;
}
input:focus, select:focus, textarea:focus {
outline: none;
border-color: #3b82f6;
}
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
-webkit-appearance: none;
margin: 0;
}
.custom-checkbox {
display: inline-block;
position: relative;
cursor: pointer;
}
.custom-checkbox input {
position: absolute;
opacity: 0;
cursor: pointer;
}
.checkmark {
position: absolute;
top: 0;
left: 0;
height: 20px;
width: 20px;
background-color: #fff;
border: 2px solid #d1d5db;
border-radius: 4px;
}
.custom-checkbox input:checked ~ .checkmark {
background-color: #3b82f6;
border-color: #3b82f6;
}
.checkmark:after {
content: "";
position: absolute;
display: none;
}
.custom-checkbox input:checked ~ .checkmark:after {
display: block;
}
.custom-checkbox .checkmark:after {
left: 6px;
top: 2px;
width: 5px;
height: 10px;
border: solid white;
border-width: 0 2px 2px 0;
transform: rotate(45deg);
}
.tab-active {
background-color: #3b82f6;
color: white;
}
</style>
</head>
<body class="bg-gray-50">
<!-- Header -->
<header class="bg-white shadow-sm">
<div class="container mx-auto px-4 py-4 flex items-center justify-between">
<div class="flex items-center">
<a href="index.html" id="logo-link" class="text-3xl font-['Pacifico'] text-primary mr-10 flex items-center gap-2">
<div class="w-8 h-8 flex items-center justify-center">
<i class="ri-steering-2-line ri-lg"></i>
</div>
logo
</a>
<nav class="hidden md:flex space-x-8">
<a href="#services" class="text-gray-700 hover:text-primary font-medium">Services</a>
<a href="#become-driver" class="text-gray-700 hover:text-primary font-medium">Become a Driver</a>
<a href="#car-rental" class="text-gray-700 hover:text-primary font-medium">Car Rental</a>
<a href="#valet-parking" class="text-gray-700 hover:text-primary font-medium">Valet Parking</a>
</nav>
</div>
<div class="flex items-center space-x-4">
<div class="hidden md:flex items-center mr-6">
<div class="w-8 h-8 flex items-center justify-center text-primary">
<i class="ri-phone-line ri-lg"></i>
</div>
<span class="text-gray-700 font-medium">+1 (800) 555-0123</span>
</div>
<button onclick="window.location.href='login.php'" class="px-4 py-2 text-primary border border-primary hover:bg-primary hover:text-white transition-colors duration-300 font-medium !rounded-button whitespace-nowrap">Sign In</button>
<button onclick="window.location.href='register.php'" class="px-4 py-2 bg-primary text-white hover:bg-blue-600 transition-colors duration-300 font-medium !rounded-button whitespace-nowrap">Register</button>
</div>
</div>
</header>
<!-- Hero Section -->
<section class="hero-section w-full relative">
<div class="absolute inset-0 bg-gradient-to-r from-white via-white/90 to-transparent"></div>
<div class="container mx-auto px-4 py-24 relative">
<div class="max-w-2xl">
<h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">Premium Driver Services for Every Journey</h1>
<p class="text-lg text-gray-700 mb-8">Experience luxury transportation with our professional drivers, premium car rentals, and convenient valet parking services.</p>
<div class="flex flex-col sm:flex-row gap-4">
<button class="px-6 py-3 bg-primary text-white hover:bg-blue-600 transition-colors duration-300 font-medium text-lg !rounded-button whitespace-nowrap">Hire Our Services</button>
<button class="px-6 py-3 border-2 border-primary text-primary hover:bg-primary hover:text-white transition-colors duration-300 font-medium text-lg !rounded-button whitespace-nowrap">Join As Driver</button>
</div>
</div>
</div>
</section>
<!-- Services Grid -->
<section id="services" class="py-20 bg-white">
<div class="container mx-auto px-4">
<div class="text-center mb-16">
<h2 class="text-3xl font-bold text-gray-900 mb-4">Our Premium Services</h2>
<p class="text-gray-600 max-w-2xl mx-auto">Discover our range of professional transportation services designed to meet your every need with comfort and style.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
<div class="w-16 h-16 flex items-center justify-center bg-blue-100 text-primary rounded-full mb-6">
<i class="ri-user-star-line ri-2x"></i>
</div>
<h3 class="text-xl font-semibold text-gray-900 mb-3">Driver Hiring</h3>
<p class="text-gray-600 mb-6">Our professional drivers are trained to provide exceptional service with punctuality and courtesy. Perfect for business travel, special events, or airport transfers.</p>
<button class="text-primary font-medium flex items-center hover:underline !rounded-button whitespace-nowrap">
Learn More
<div class="w-5 h-5 flex items-center justify-center ml-1">
<i class="ri-arrow-right-line"></i>
</div>
</button>
</div>
<div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
<div class="w-16 h-16 flex items-center justify-center bg-blue-100 text-primary rounded-full mb-6">
<i class="ri-car-line ri-2x"></i>
</div>
<h3 class="text-xl font-semibold text-gray-900 mb-3">Car Rental</h3>
<p class="text-gray-600 mb-6">Choose from our fleet of luxury and economy vehicles for any occasion. All our cars are meticulously maintained and come with 24/7 roadside assistance.</p>
<button class="text-primary font-medium flex items-center hover:underline !rounded-button whitespace-nowrap">
Learn More
<div class="w-5 h-5 flex items-center justify-center ml-1">
<i class="ri-arrow-right-line"></i>
</div>
</button>
</div>
<div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
<div class="w-16 h-16 flex items-center justify-center bg-blue-100 text-primary rounded-full mb-6">
<i class="ri-parking-line ri-2x"></i>
</div>
<h3 class="text-xl font-semibold text-gray-900 mb-3">Valet Parking</h3>
<p class="text-gray-600 mb-6">Let our professional valets handle the parking at your event or venue. We ensure your vehicle is safely parked and readily available when you need it.</p>
<button class="text-primary font-medium flex items-center hover:underline !rounded-button whitespace-nowrap">
Learn More
<div class="w-5 h-5 flex items-center justify-center ml-1">
<i class="ri-arrow-right-line"></i>
</div>
</button>
</div>
</div>
</div>
</section>
<!-- Driver Registration Section -->
<section id="become-driver" class="py-20 bg-gray-50">
<div class="container mx-auto px-4">
<div class="text-center mb-16">
<h2 class="text-3xl font-bold text-gray-900 mb-4">Join Our Elite Driver Team</h2>
<p class="text-gray-600 max-w-2xl mx-auto">Become part of our professional driver network and enjoy competitive pay, flexible hours, and premium clientele.</p>
</div>
<div class="flex flex-col lg:flex-row gap-12">
<div class="flex-1 bg-white rounded-lg shadow-lg p-8">
<h3 class="text-xl font-semibold text-gray-900 mb-6">Benefits of Joining</h3>
<ul class="space-y-4 mb-8">
<li class="flex items-start">
<div class="w-6 h-6 flex items-center justify-center text-primary mt-0.5 mr-2">
<i class="ri-check-line ri-lg"></i>
</div>
<div>
<h4 class="font-medium text-gray-900">Competitive Earnings</h4>
<p class="text-gray-600">Earn up to $35/hour with tips and bonuses for exceptional service</p>
</div>
</li>
<li class="flex items-start">
<div class="w-6 h-6 flex items-center justify-center text-primary mt-0.5 mr-2">
<i class="ri-check-line ri-lg"></i>
</div>
<div>
<h4 class="font-medium text-gray-900">Flexible Schedule</h4>
<p class="text-gray-600">Choose your own hours and work as much or as little as you want</p>
</div>
</li>
<li class="flex items-start">
<div class="w-6 h-6 flex items-center justify-center text-primary mt-0.5 mr-2">
<i class="ri-check-line ri-lg"></i>
</div>
<div>
<h4 class="font-medium text-gray-900">Premium Clientele</h4>
<p class="text-gray-600">Work with high-end clients including executives and VIPs</p>
</div>
</li>
<li class="flex items-start">
<div class="w-6 h-6 flex items-center justify-center text-primary mt-0.5 mr-2">
<i class="ri-check-line ri-lg"></i>
</div>
<div>
<h4 class="font-medium text-gray-900">Professional Development</h4>
<p class="text-gray-600">Access to training programs and career advancement opportunities</p>
</div>
</li>
<li class="flex items-start">
<div class="w-6 h-6 flex items-center justify-center text-primary mt-0.5 mr-2">
<i class="ri-check-line ri-lg"></i>
</div>
<div>
<h4 class="font-medium text-gray-900">Insurance Coverage</h4>
<p class="text-gray-600">Comprehensive insurance coverage while on duty</p>
</div>
</li>
</ul>
<div class="bg-gray-50 p-6 rounded-lg">
<div class="flex items-center mb-4">
<img src="https://readdy.ai/api/search-image?query=Professional%20male%20chauffeur%20in%20formal%20attire%20with%20a%20friendly%20smile%2C%20looking%20confident%20and%20approachable.%20High-quality%20portrait%20with%20neutral%20background%2C%20well-groomed%20appearance%2C%20professional%20lighting.&width=60&height=60&seq=2&orientation=squarish" alt="Driver" class="w-12 h-12 rounded-full object-cover object-top mr-4">
<div>
<h4 class="font-medium text-gray-900">Michael Richardson</h4>
<p class="text-gray-600 text-sm">Driver since 2022</p>
</div>
</div>
<p class="text-gray-700 italic">"Joining Pro-drivers was the best career decision I've made. The flexible schedule allows me to balance work with family time, and the clients are respectful and generous. I've increased my income by 40% compared to my previous driving job."</p>
</div>
</div>
</div>
</div>
</section>
<!-- Car Rental Showcase -->
<section id="car-rental" class="py-20 bg-white">
<div class="container mx-auto px-4">
<div class="text-center mb-16">
<h2 class="text-3xl font-bold text-gray-900 mb-4">Premium Car Rental Fleet</h2>
<p class="text-gray-600 max-w-2xl mx-auto">Choose from our selection of meticulously maintained vehicles for any occasion, from business trips to special events.</p>
</div>
<div class="flex justify-center mb-10">
<div class="inline-flex p-1 bg-gray-100 rounded-full">
<button class="px-6 py-2 rounded-full tab-active !rounded-full whitespace-nowrap">All Cars</button>
<button class="px-6 py-2 rounded-full text-gray-700 hover:bg-gray-200 !rounded-full whitespace-nowrap">Luxury</button>
<button class="px-6 py-2 rounded-full text-gray-700 hover:bg-gray-200 !rounded-full whitespace-nowrap">SUV</button>
<button class="px-6 py-2 rounded-full text-gray-700 hover:bg-gray-200 !rounded-full whitespace-nowrap">Economy</button>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
<div class="h-48 overflow-hidden">
<img src="https://readdy.ai/api/search-image?query=Luxury%20black%20Mercedes%20S-Class%20sedan%20on%20a%20clean%20white%20background.%20Professional%20studio%20lighting%20highlighting%20the%20sleek%20design%20and%20premium%20features.%20High-quality%20detailed%20image%20showing%20the%20elegant%20exterior%20of%20this%20high-end%20vehicle.&width=400&height=250&seq=3&orientation=landscape" alt="Mercedes S-Class" class="w-full h-full object-cover object-top">
</div>
<div class="p-6">
<div class="flex justify-between items-center mb-3">
<h3 class="text-xl font-semibold text-gray-900">Mercedes S-Class</h3>
<!-- <span class="text-primary font-bold">$199/day</span> -->
</div>
<div class="flex flex-wrap gap-3 mb-4">
<span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">Luxury</span>
<span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">5 Seats</span>
<span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">Automatic</span>
</div>
<div class="flex items-center justify-between">
<div class="flex items-center text-yellow-500">
<div class="w-4 h-4 flex items-center justify-center">
<i class="ri-star-fill"></i>
</div>
<div class="w-4 h-4 flex items-center justify-center">
<i class="ri-star-fill"></i>
</div>
<div class="w-4 h-4 flex items-center justify-center">
<i class="ri-star-fill"></i>
</div>
<div class="w-4 h-4 flex items-center justify-center">
<i class="ri-star-fill"></i>
</div>
<div class="w-4 h-4 flex items-center justify-center">
<i class="ri-star-fill"></i>
</div>
<span class="ml-1 text-gray-600 text-sm">(24)</span>
</div>
<button class="px-4 py-2 bg-primary text-white hover:bg-blue-600 transition-colors duration-300 !rounded-button whitespace-nowrap">Book Now</button>
</div>
</div>
</div>
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
<div class="h-48 overflow-hidden">
<img src="https://readdy.ai/api/search-image?query=BMW%20X5%20SUV%20in%20metallic%20blue%20on%20a%20clean%20white%20background.%20Professional%20studio%20lighting%20highlighting%20the%20sleek%20design%20and%20premium%20features.%20High-quality%20detailed%20image%20showing%20the%20elegant%20exterior%20of%20this%20luxury%20SUV.&width=400&height=250&seq=4&orientation=landscape" alt="BMW X5" class="w-full h-full object-cover object-top">
</div>
<div class="p-6">
<div class="flex justify-between items-center mb-3">
<h3 class="text-xl font-semibold text-gray-900">BMW X5</h3>
<!-- <span class="text-primary font-bold">$179/day</span> -->
</div>
<div class="flex flex-wrap gap-3 mb-4">
<span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">SUV</span>
<span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">7 Seats</span>
<span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">Automatic</span>
</div>
<div class="flex items-center justify-between">
<div class="flex items-center text-yellow-500">
<div class="w-4 h-4 flex items-center justify-center">
<i class="ri-star-fill"></i>
</div>
<div class="w-4 h-4 flex items-center justify-center">
<i class="ri-star-fill"></i>
</div>
<div class="w-4 h-4 flex items-center justify-center">
<i class="ri-star-fill"></i>
</div>
<div class="w-4 h-4 flex items-center justify-center">
<i class="ri-star-fill"></i>
</div>
<div class="w-4 h-4 flex items-center justify-center">
<i class="ri-star-half-fill"></i>
</div>
<span class="ml-1 text-gray-600 text-sm">(18)</span>
</div>
<button class="px-4 py-2 bg-primary text-white hover:bg-blue-600 transition-colors duration-300 !rounded-button whitespace-nowrap">Book Now</button>
</div>
</div>
</div>
<div class="bg-white rounded-lg shadow-lg overflow-hidden">
<div class="h-48 overflow-hidden">
<img src="https://readdy.ai/api/search-image?query=Tesla%20Model%203%20in%20pearl%20white%20on%20a%20clean%20white%20background.%20Professional%20studio%20lighting%20highlighting%20the%20sleek%20design%20and%20premium%20features.%20High-quality%20detailed%20image%20showing%20the%20elegant%20exterior%20of%20this%20electric%20vehicle.&width=400&height=250&seq=5&orientation=landscape" alt="Tesla Model 3" class="w-full h-full object-cover object-top">
</div>
<div class="p-6">
<div class="flex justify-between items-center mb-3">
<h3 class="text-xl font-semibold text-gray-900">Tesla Model 3</h3>
<!-- <span class="text-primary font-bold">$159/day</span> -->
</div>
<div class="flex flex-wrap gap-3 mb-4">
<span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">Electric</span>
<span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">5 Seats</span>
<span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">Automatic</span>
</div>
<div class="flex items-center justify-between">
<div class="flex items-center text-yellow-500">
<div class="w-4 h-4 flex items-center justify-center">
<i class="ri-star-fill"></i>
</div>
<div class="w-4 h-4 flex items-center justify-center">
<i class="ri-star-fill"></i>
</div>
<div class="w-4 h-4 flex items-center justify-center">
<i class="ri-star-fill"></i>
</div>
<div class="w-4 h-4 flex items-center justify-center">
<i class="ri-star-fill"></i>
</div>
<div class="w-4 h-4 flex items-center justify-center">
<i class="ri-star-line"></i>
</div>
<span class="ml-1 text-gray-600 text-sm">(31)</span>
</div>
<button class="px-4 py-2 bg-primary text-white hover:bg-blue-600 transition-colors duration-300 !rounded-button whitespace-nowrap">Book Now</button>
</div>
</div>
</div>
</div>
<div class="text-center mt-10">
<button class="px-6 py-3 border-2 border-primary text-primary hover:bg-primary hover:text-white transition-colors duration-300 font-medium !rounded-button whitespace-nowrap">View All Vehicles</button>
</div>
</div>
</section>
<!-- Valet Parking Features -->
<section id="valet-parking" class="py-20 bg-gray-50">
<div class="container mx-auto px-4">
<div class="text-center mb-16">
<h2 class="text-3xl font-bold text-gray-900 mb-4">Premium Valet Parking Services</h2>
<p class="text-gray-600 max-w-2xl mx-auto">Let our professional valets handle the parking at your event or venue, ensuring a seamless experience for you and your guests.</p>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
<div class="bg-white p-8 rounded-lg shadow-lg">
<div class="w-16 h-16 flex items-center justify-center bg-blue-100 text-primary rounded-full mb-6">
<i class="ri-timer-line ri-2x"></i>
</div>
<h3 class="text-xl font-semibold text-gray-900 mb-3">Quick Service</h3>
<p class="text-gray-600">Our valets are trained to park and retrieve vehicles efficiently, minimizing wait times for you and your guests.</p>
</div>
<div class="bg-white p-8 rounded-lg shadow-lg">
<div class="w-16 h-16 flex items-center justify-center bg-blue-100 text-primary rounded-full mb-6">
<i class="ri-shield-check-line ri-2x"></i>
</div>
<h3 class="text-xl font-semibold text-gray-900 mb-3">Vehicle Security</h3>
<p class="text-gray-600">All vehicles are parked in secure, monitored locations with comprehensive insurance coverage for peace of mind.</p>
</div>
<div class="bg-white p-8 rounded-lg shadow-lg">
<div class="w-16 h-16 flex items-center justify-center bg-blue-100 text-primary rounded-full mb-6">
<i class="ri-calendar-check-line ri-2x"></i>
</div>
<h3 class="text-xl font-semibold text-gray-900 mb-3">Event Planning</h3>
<p class="text-gray-600">We work with event planners to create customized parking solutions for weddings, corporate events, and private parties.</p>
</div>
<div class="bg-white p-8 rounded-lg shadow-lg">
<div class="w-16 h-16 flex items-center justify-center bg-blue-100 text-primary rounded-full mb-6">
<i class="ri-smartphone-line ri-2x"></i>
</div>
<h3 class="text-xl font-semibold text-gray-900 mb-3">Mobile App</h3>
<p class="text-gray-600">Request vehicle retrieval through our convenient mobile app, allowing you to have your car ready when you are.</p>
</div>
<div class="bg-white p-8 rounded-lg shadow-lg">
<div class="w-16 h-16 flex items-center justify-center bg-blue-100 text-primary rounded-full mb-6">
<i class="ri-user-star-line ri-2x"></i>
</div>
<h3 class="text-xl font-semibold text-gray-900 mb-3">Professional Staff</h3>
<p class="text-gray-600">Our valets are professionally trained, background-checked, and dressed in formal attire to represent your event with class.</p>
</div>
<div class="bg-white p-8 rounded-lg shadow-lg">
<div class="w-16 h-16 flex items-center justify-center bg-blue-100 text-primary rounded-full mb-6">
<i class="ri-building-line ri-2x"></i>
</div>
<h3 class="text-xl font-semibold text-gray-900 mb-3">Venue Partnerships</h3>
<p class="text-gray-600">We partner with hotels, restaurants, and event venues to provide seamless valet services for their guests.</p>
</div>
</div>
<div class="mt-16 bg-white p-8 rounded-lg shadow-lg">
<div class="flex flex-col md:flex-row items-center">
<div class="md:w-1/2 mb-8 md:mb-0 md:pr-8">
<h3 class="text-2xl font-bold text-gray-900 mb-4">Request Valet Service Quote</h3>
<p class="text-gray-600 mb-6">Fill out the form to receive a customized quote for your event or venue. Our team will respond within 24 hours.</p>
<form>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
<input class="px-4 py-2 border border-gray-300 rounded focus:border-primary" type="text" placeholder="Full Name">
<input class="px-4 py-2 border border-gray-300 rounded focus:border-primary" type="email" placeholder="Email Address">
<input class="px-4 py-2 border border-gray-300 rounded focus:border-primary" type="tel" placeholder="Phone Number">
<input class="px-4 py-2 border border-gray-300 rounded focus:border-primary" type="text" placeholder="Event Type">
<input class="px-4 py-2 border border-gray-300 rounded focus:border-primary" type="text" placeholder="Event Date">
<input class="px-4 py-2 border border-gray-300 rounded focus:border-primary" type="text" placeholder="Expected Guests">
</div>
<textarea class="w-full px-4 py-2 border border-gray-300 rounded focus:border-primary mb-4" rows="3" placeholder="Additional Details"></textarea>
<button type="submit" class="px-6 py-3 bg-primary text-white hover:bg-blue-600 transition-colors duration-300 font-medium !rounded-button whitespace-nowrap">Request Quote</button>
</form>
</div>
<div class="md:w-1/2">
<img src="https://readdy.ai/api/search-image?query=Professional%20valet%20service%20with%20uniformed%20staff%20handling%20luxury%20cars%20at%20an%20upscale%20event%20venue.%20Well-dressed%20valets%20in%20formal%20attire%20attending%20to%20guests%20arriving%20in%20premium%20vehicles.%20Clean%2C%20elegant%20setting%20with%20proper%20lighting%20showing%20the%20professional%20atmosphere.&width=500&height=400&seq=6&orientation=landscape" alt="Valet Service" class="w-full h-auto rounded-lg object-cover object-top">
</div>
</div>
</div>
</div>
</section>
<!-- Call-to-Action Section -->
<section class="py-20 bg-primary bg-opacity-10">
<div class="container mx-auto px-4">
<div class="max-w-4xl mx-auto text-center">
<h2 class="text-3xl font-bold text-gray-900 mb-6">Experience Premium Transportation Services</h2>
<p class="text-lg text-gray-700 mb-8">Join thousands of satisfied customers who trust us for their transportation needs. Our professional drivers, premium vehicles, and exceptional service are just a click away.</p>
<div class="flex flex-col sm:flex-row justify-center gap-4 mb-10">
<button class="px-6 py-3 bg-primary text-white hover:bg-blue-600 transition-colors duration-300 font-medium !rounded-button whitespace-nowrap">Book a Service</button>
<button class="px-6 py-3 border-2 border-primary text-primary hover:bg-primary hover:text-white transition-colors duration-300 font-medium !rounded-button whitespace-nowrap">Contact Sales</button>
</div>
<div class="flex flex-wrap justify-center gap-8">
<div class="flex items-center">
<div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
<i class="ri-shield-check-line"></i>
</div>
<span class="text-gray-700">Fully Insured</span>
</div>
<div class="flex items-center">
<div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
<i class="ri-24-hours-line"></i>
</div>
<span class="text-gray-700">24/7 Support</span>
</div>
<div class="flex items-center">
<div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
<i class="ri-verified-badge-line"></i>
</div>
<span class="text-gray-700">Licensed Drivers</span>
</div>
<div class="flex items-center">
<div class="w-6 h-6 flex items-center justify-center text-primary mr-2">
<i class="ri-thumb-up-line"></i>
</div>
<span class="text-gray-700">4.9/5 Customer Rating</span>
</div>
</div>
</div>
</div>
</section>
<!-- Footer -->
<footer class="bg-gray-900 text-white pt-16 pb-8">
<div class="container mx-auto px-4">
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
<div>
<a href="index.html" id="footer-logo" class="text-3xl font-['Pacifico'] text-white mb-6 inline-flex items-center gap-2">
<div class="w-8 h-8 flex items-center justify-center">
<i class="ri-steering-2-line ri-lg"></i>
</div>
logo
</a>
<p class="text-gray-400 mb-6">Pro-drivers offers premium transportation services for discerning clients. Professional drivers, luxury vehicles, and exceptional service.</p>
<div class="flex space-x-4">
<a href="#" class="w-10 h-10 flex items-center justify-center bg-gray-800 hover:bg-primary rounded-full transition-colors duration-300">
<i class="ri-facebook-fill"></i>
</a>
<a href="#" class="w-10 h-10 flex items-center justify-center bg-gray-800 hover:bg-primary rounded-full transition-colors duration-300">
<i class="ri-twitter-x-fill"></i>
</a>
<a href="#" class="w-10 h-10 flex items-center justify-center bg-gray-800 hover:bg-primary rounded-full transition-colors duration-300">
<i class="ri-instagram-fill"></i>
</a>
<a href="#" class="w-10 h-10 flex items-center justify-center bg-gray-800 hover:bg-primary rounded-full transition-colors duration-300">
<i class="ri-linkedin-fill"></i>
</a>
</div>
</div>
<div>
<h3 class="text-lg font-semibold mb-6">Quick Links</h3>
<ul class="space-y-3">
<li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Home</a></li>
<li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">About Us</a></li>
<li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Services</a></li>
<li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Car Fleet</a></li>
<li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Become a Driver</a></li>
<li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Contact Us</a></li>
</ul>
</div>
<div>
<h3 class="text-lg font-semibold mb-6">Services</h3>
<ul class="space-y-3">
<li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Driver Hiring</a></li>
<li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Car Rental</a></li>
<li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Valet Parking</a></li>
<li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Airport Transfers</a></li>
<li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Corporate Services</a></li>
<li><a href="#" class="text-gray-400 hover:text-white transition-colors duration-300">Event Transportation</a></li>
</ul>
</div>
<div>
<h3 class="text-lg font-semibold mb-6">Contact Us</h3>
<ul class="space-y-3">
<li class="flex items-start">
<div class="w-5 h-5 flex items-center justify-center text-gray-400 mt-0.5 mr-3">
<i class="ri-map-pin-line"></i>
</div>
<span class="text-gray-400">123 Business Avenue, New York, NY 10001</span>
</li>
<li class="flex items-center">
<div class="w-5 h-5 flex items-center justify-center text-gray-400 mr-3">
<i class="ri-phone-line"></i>
</div>
<span class="text-gray-400">+1 (800) 555-0123</span>
</li>
<li class="flex items-center">
<div class="w-5 h-5 flex items-center justify-center text-gray-400 mr-3">
<i class="ri-mail-line"></i>
</div>
<span class="text-gray-400">info@pro-drivers.com</span>
</li>
</ul>
<div class="mt-6">
<h3 class="text-lg font-semibold mb-4">Newsletter</h3>
<form class="flex">
<input type="email" placeholder="Your email" class="px-4 py-2 bg-gray-800 text-white border-none rounded-l focus:outline-none focus:ring-1 focus:ring-primary w-full">
<button type="submit" class="px-4 py-2 bg-primary text-white rounded-r hover:bg-blue-600 transition-colors duration-300 !rounded-button whitespace-nowrap">Subscribe</button>
</form>
</div>
</div>
</div>
<div class="pt-8 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center">
<p class="text-gray-400 text-sm mb-4 md:mb-0">© 2025 Pro-drivers. All rights reserved.</p>
<div class="flex space-x-6">
<a href="#" class="text-gray-400 hover:text-white text-sm transition-colors duration-300">Privacy Policy</a>
<a href="#" class="text-gray-400 hover:text-white text-sm transition-colors duration-300">Terms of Service</a>
<a href="#" class="text-gray-400 hover:text-white text-sm transition-colors duration-300">Cookie Policy</a>
</div>
<div class="flex items-center space-x-4 mt-4 md:mt-0">
<div class="w-8 h-8 flex items-center justify-center text-gray-400">
<i class="ri-visa-fill ri-lg"></i>
</div>
<div class="w-8 h-8 flex items-center justify-center text-gray-400">
<i class="ri-mastercard-fill ri-lg"></i>
</div>
<div class="w-8 h-8 flex items-center justify-center text-gray-400">
<i class="ri-paypal-fill ri-lg"></i>
</div>
<div class="w-8 h-8 flex items-center justify-center text-gray-400">
<i class="ri-apple-fill ri-lg"></i>
</div>
</div>
</div>
</div>
</footer>
<script>
document.addEventListener('DOMContentLoaded', function() {
// Tabs functionality for car rental
const tabs = document.querySelectorAll('.rounded-full');
tabs.forEach(tab => {
tab.addEventListener('click', function() {
tabs.forEach(t => t.classList.remove('tab-active'));
tab.classList.add('tab-active');
});
});
// Logo click tracking
const logoLink = document.getElementById('logo-link');
const footerLogo = document.getElementById('footer-logo');
if(logoLink) {
logoLink.addEventListener('click', function(e) {
console.log('Header logo clicked - navigating to homepage');
});
}
if(footerLogo) {
footerLogo.addEventListener('click', function(e) {
console.log('Footer logo clicked - navigating to homepage');
});
}
});
</script>
</body>
</html>
