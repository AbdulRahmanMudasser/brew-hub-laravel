<section class="contact">
    <h2>Opening Hours</h2>
    <div class="contact-details">
        <p><br>
            <b>Monday</b> - <b>Friday</b>: 7:00 AM - 10:00 PM<br>
            <b>Saturday</b> - <b>Sunday</b>: 10:00 AM - 8:00 PM
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
        <p>Visit us at 123 Coffee Lane, Brewtown</p>
    </div>
</section>