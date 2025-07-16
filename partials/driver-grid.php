<?php
// This partial expects $drivers_result to be set and valid
?>
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8 mb-8">
  <?php if ($drivers_result && $drivers_result->num_rows > 0): ?>
    <?php while($driver = $drivers_result->fetch_assoc()): ?>
      <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-6 flex flex-col items-center">
        <img src="<?= !empty($driver['profile_picture']) ? htmlspecialchars($driver['profile_picture']) : 'images/default-profile.png' ?>" class="w-28 h-28 rounded-xl object-cover border border-gray-200 bg-gray-100 mb-4" alt="Driver Photo">
        <div class="text-center w-full">
          <h4 class="text-lg font-semibold text-gray-900 mb-1"><?= htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']) ?></h4>
          <div class="text-gray-600 text-sm mb-1"><?= htmlspecialchars($driver['experience'] ?? '0') ?> years experience</div>
          <div class="text-gray-500 text-sm mb-1"><?= htmlspecialchars($driver['address'] ?? 'Lagos, Nigeria') ?></div>
          <div class="text-green-600 text-xs mb-2">Available Now</div>
          <!-- Star Rating Placeholder -->
          <div class="flex items-center justify-center gap-1 mb-4">
            <i class="fa fa-star text-yellow-400"></i>
            <i class="fa fa-star text-yellow-400"></i>
            <i class="fa fa-star text-yellow-400"></i>
            <i class="fa fa-star text-yellow-400"></i>
            <i class="fa fa-star-half-alt text-yellow-400"></i>
            <span class="ml-2 text-gray-500 text-xs">4.8</span>
          </div>
          <form action="booking.php" method="GET" class="w-full">
            <input type="hidden" name="driver_id" value="<?= htmlspecialchars($driver['id']) ?>">
            <input type="hidden" name="amount" value="5000"> <!-- Replace with actual amount calculation -->
            <button type="submit" class="w-full rounded-lg bg-blue-900 hover:bg-blue-800 text-white font-semibold py-2.5 text-base shadow-sm transition">Book Now</button>
          </form>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="col-span-full flex flex-col items-center justify-center text-gray-500 py-12">
      <i class="fa fa-info-circle text-3xl mb-2"></i>
      <span class="text-lg">No drivers match your search criteria. Please try different filters.</span>
    </div>
  <?php endif; ?>
</div> 