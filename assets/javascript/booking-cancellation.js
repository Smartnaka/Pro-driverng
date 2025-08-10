// Booking cancellation functionality
let cancelBookingId = null;
let cancelling = false;

function calculateHoursUntilPickup(pickupDate, pickupTime) {
    const pickup = new Date(pickupDate + ' ' + pickupTime);
    const now = new Date();
    return (pickup - now) / (1000 * 60 * 60);
}

function cancelBooking(bookingId, pickupDate, pickupTime) {
    if (cancelling) return;
    
    cancelBookingId = bookingId;
    const hoursUntil = calculateHoursUntilPickup(pickupDate, pickupTime);
    const feeEstimateEl = document.getElementById('cancellationFeeEstimate');
    const refundEstimateEl = document.getElementById('refundEstimate');
    
    let feePercentage = 0;
    if (hoursUntil < 2) {
        feePercentage = 100;
    } else if (hoursUntil < 24) {
        feePercentage = 50;
    } else {
        feePercentage = 10;
    }
    
    if (hoursUntil < 0) {
        feeEstimateEl.textContent = 'This booking is in the past and cannot be cancelled.';
        refundEstimateEl.textContent = '';
        document.getElementById('cancelModalYes').disabled = true;
        document.getElementById('cancelModalYes').classList.add('opacity-50');
    } else {
        feeEstimateEl.textContent = `Estimated cancellation fee: ${feePercentage}% of booking amount`;
        if (feePercentage < 100) {
            refundEstimateEl.textContent = `You will receive a ${100 - feePercentage}% refund`;
        } else {
            refundEstimateEl.textContent = 'No refund will be issued';
        }
        document.getElementById('cancelModalYes').disabled = false;
        document.getElementById('cancelModalYes').classList.remove('opacity-50');
    }
    
    document.getElementById('cancelModal').classList.remove('hidden');
}

document.getElementById('cancelModalNo').onclick = function() {
    if (cancelling) return;
    document.getElementById('cancelModal').classList.add('hidden');
    cancelBookingId = null;
};

function showToast(message, type = 'success') {
    // Create toast container if it doesn't exist
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-6 right-6 z-50 flex flex-col gap-2 items-end';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `px-4 py-3 rounded-lg shadow-lg text-white text-sm mb-2 flex items-center gap-2 ${
        type === 'success' ? 'bg-green-600' : 'bg-red-600'
    }`;
    
    const icon = document.createElement('i');
    icon.className = `fas fa-${type === 'success' ? 'check-circle' : 'times-circle'}`;
    toast.appendChild(icon);
    
    const text = document.createElement('span');
    text.textContent = message;
    toast.appendChild(text);
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

document.getElementById('cancelModalYes').onclick = function() {
    if (!cancelBookingId || cancelling) return;
    cancelling = true;
    
    const button = document.getElementById('cancelModalYes');
    const buttonNo = document.getElementById('cancelModalNo');
    const spinner = document.getElementById('cancelModalSpinner');
    
    button.disabled = true;
    buttonNo.disabled = true;
    spinner.classList.remove('hidden');
    
    fetch('api/cancel_booking.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: 'booking_id=' + encodeURIComponent(cancelBookingId)
    })
    .then(response => response.json())
    .then(data => {
        cancelling = false;
        button.disabled = false;
        buttonNo.disabled = false;
        spinner.classList.add('hidden');
        document.getElementById('cancelModal').classList.add('hidden');
        cancelBookingId = null;
        
        if (data.success) {
            showToast('Booking cancelled successfully.', 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast(data.message || 'Failed to cancel booking.', 'error');
        }
    })
    .catch(() => {
        cancelling = false;
        button.disabled = false;
        buttonNo.disabled = false;
        spinner.classList.add('hidden');
        document.getElementById('cancelModal').classList.add('hidden');
        cancelBookingId = null;
        showToast('Network error. Please try again.', 'error');
    });
};
