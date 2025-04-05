<?php
require_once '../config/database.php';
require_once '../config/smtp.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Get POST data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$checkin_date = isset($_POST['checkin_date']) ? date('Y-m-d', strtotime($_POST['checkin_date'])) : '';
$checkout_date = isset($_POST['checkout_date']) ? date('Y-m-d', strtotime($_POST['checkout_date'])) : '';
$guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 0;

// Check if this is a tour booking or hotel booking
$tour_id = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
$hotel_id = isset($_POST['hotel_id']) ? (int)$_POST['hotel_id'] : 0;
$room_type_id = isset($_POST['room_type']) ? (int)$_POST['room_type'] : 0;

// Validate input
if (empty($name) || empty($email) || empty($phone) || empty($checkin_date) || empty($checkout_date) || !$guests) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit();
}

// Validate dates
$checkin = strtotime($checkin_date);
$checkout = strtotime($checkout_date);
$today = strtotime('today');

if (!$checkin || !$checkout) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format']);
    exit();
}

if ($checkin < $today) {
    http_response_code(400);
    echo json_encode(['error' => 'Check-in date cannot be in the past']);
    exit();
}

if ($checkout <= $checkin) {
    http_response_code(400);
    echo json_encode(['error' => 'Check-out date must be after check-in date']);
    exit();
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    if ($tour_id) {
        // Tour booking
        $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
        $stmt->execute([$tour_id]);
        $tour = $stmt->fetch();

        if (!$tour) {
            throw new Exception('Tour not found');
        }

        // Insert booking
        $stmt = $pdo->prepare("INSERT INTO bookings (tour_id, name, email, phone, checkin_date, checkout_date, guests) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$tour_id, $name, $email, $phone, $checkin_date, $checkout_date, $guests]);

        $total_price = $tour['price'] * $guests;
        $item_name = $tour['name'];
        $type = 'tour';
    } else if ($hotel_id && $room_type_id) {
        // Hotel booking
        $stmt = $pdo->prepare("SELECT rt.*, h.name as hotel_name 
                              FROM room_types rt 
                              JOIN hotels h ON rt.hotel_id = h.id 
                              WHERE rt.id = ? AND h.id = ?");
        $stmt->execute([$room_type_id, $hotel_id]);
        $room = $stmt->fetch();

        if (!$room) {
            throw new Exception('Hotel or room type not found');
        }

        if ($guests > $room['capacity']) {
            throw new Exception('Number of guests exceeds room capacity');
        }

        // Calculate number of nights
        $nights = ceil(($checkout - $checkin) / (60 * 60 * 24));
        $total_price = $room['price'] * $nights;

        // Insert booking
        $stmt = $pdo->prepare("INSERT INTO hotel_bookings (hotel_id, room_type_id, name, email, phone, checkin_date, checkout_date, total_price, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$hotel_id, $room_type_id, $name, $email, $phone, $checkin_date, $checkout_date, $total_price]);

        $item_name = $room['hotel_name'] . ' - ' . $room['name'];
        $type = 'hotel';
    } else {
        throw new Exception('Invalid booking type');
    }

    // Send confirmation email
    $to = $email;
    $subject = "Booking Confirmation - " . $item_name;
    $message = "
    <html>
    <head>
        <title>Booking Confirmation</title>
    </head>
    <body>
        <h2>Thank you for your booking!</h2>
        <p>Dear $name,</p>
        <p>Your booking for {$item_name} has been received and is currently pending confirmation.</p>
        <h3>Booking Details:</h3>
        <ul>
            <li>" . ($type == 'tour' ? 'Tour' : 'Hotel/Room') . ": {$item_name}</li>
            <li>Check-in: $checkin_date</li>
            <li>Check-out: $checkout_date</li>
            <li>Number of Guests: $guests</li>
            <li>Total Price: $$total_price</li>
        </ul>
        <p>We will contact you shortly to confirm your booking.</p>
        <p>Best regards,<br>Your Travel Team</p>
    </body>
    </html>
    ";

    // Try to send email using configured SMTP
    $email_sent = sendEmail($to, $subject, $message);

    // Commit transaction
    $pdo->commit();

    // Return success response
    $response = [
        'success' => true,
        'message' => 'Your booking has been successfully created.'
    ];

    if (!$email_sent) {
        $response['email_status'] = 'Could not send confirmation email. Please save your booking reference.';
    } else {
        $response['message'] .= ' Please check your email for confirmation details.';
    }

    echo json_encode($response);
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();

    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
