<?php
// Start session for potential future use
session_start();

// Initialize error array
$errors = [];

// Validate form inputs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $people = (int)($_POST['people'] ?? 0);
    $date = $_POST['date'] ?? '';
    $feedback = trim($_POST['feedback'] ?? '');

    // Sanitize inputs
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $feedback = htmlspecialchars($feedback, ENT_QUOTES, 'UTF-8');

    // Validation rules
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if ($people < 1) {
        $errors[] = "Number of guests must be at least 1.";
    }
    if (empty($date)) {
        $errors[] = "Date is required.";
    } else {
        $selected_date = strtotime($date);
        $today = strtotime(date('Y-m-d'));
        if ($selected_date < $today) {
            $errors[] = "Date must be today or in the future.";
        }
    }

    // If no errors, process the reservation
    if (empty($errors)) {
        // Simulate saving to a file (replace with database in production)
        $reservation = [
            'name' => $name,
            'people' => $people,
            'date' => $date,
            'feedback' => $feedback,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        $log_entry = json_encode($reservation) . PHP_EOL;
        file_put_contents('reservations.log', $log_entry, FILE_APPEND);

        // Prepare success message and redirect
        $success_data = urlencode(json_encode([
            'name' => $name,
            'people' => $people,
            'date' => $date,
            'feedback' => $feedback
        ]));
        header("Location: contact.php?success=1&data=$success_data");
        exit;
    } else {
        // Redirect with errors (for simplicity; in production, handle errors on form)
        $_SESSION['form_errors'] = $errors;
        header("Location: contact.php");
        exit;
    }
} else {
    // Invalid request method
    header("Location: contact.php");
    exit;
}
?>