<section class="contact">
     <h3>Opening Hours</h3>
    <div class="contact-details">
        <p>
            <br>
            <b>Monday - Friday:</b> 7:00 AM - 10:00 PM<br>
            <b>Saturday - Sunday:</b> 10:00 AM - 8:00 PM
        </p>
    </div>
    <h3>Reserve a Table</h3>
    <form method="POST" action="process_reservation.php" class="reservation-form">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" required>
        <label for="people">Number of Guests</label>
        <input type="number" id="people" name="people" min="1" required>
        <label for="date">Date</label>
        <input type="date" id="date" name="date" required>
        <label for="feedback">Special Requests</label>
        <textarea id="feedback" name="feedback" rows="4"></textarea>
        <input type="submit" value="Book Your Table">
    </form>
    <div class="map-placeholder">
        <p>Visit us at 123 Coffee Lane, Brewtown<br>[Interactive Map Placeholder]</p>
    </div>
</section>

<!-- Success Modal -->
<div id="success-modal" class="modal" role="dialog" aria-labelledby="modal-title" aria-hidden="true">
    <div class="modal-content">
        <button class="modal-close" aria-label="Close modal">×</button>
        <h3 id="modal-title">Booking Confirmed!</h3>
        <div id="booking-details"></div>
    </div>
</div>

<script>
// Handle modal display based on URL parameters
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const data = urlParams.get('data');

    if (success && data) {
        try {
            const booking = JSON.parse(decodeURIComponent(data));
            const modal = document.getElementById('success-modal');
            const detailsDiv = document.getElementById('booking-details');

            // Populate booking details
            detailsDiv.innerHTML = `
                <p><strong>Name:</strong> ${booking.name}</p>
                <p><strong>Guests:</strong> ${booking.people}</p>
                <p><strong>Date:</strong> ${booking.date}</p>
                <p><strong>Special Requests:</strong> ${booking.feedback || 'None'}</p>
            `;

            // Show modal
            modal.style.display = 'block';
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            // Focus on modal for accessibility
            modal.querySelector('.modal-close').focus();
        } catch (e) {
            console.error('Error parsing booking data:', e);
        }
    }

    // Close modal
    document.querySelector('.modal-close').addEventListener('click', function() {
        const modal = document.getElementById('success-modal');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = 'auto';

        // Clear URL parameters
        window.history.replaceState({}, document.title, window.location.pathname);
    });

    // Close modal on Esc key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('success-modal');
            if (modal.style.display === 'block') {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = 'auto';
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        }
    });
});
</script>