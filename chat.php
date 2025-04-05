<?php
require_once __DIR__ . '/config/database.php';
session_start();

// Cấu hình Gemini API
define('GEMINI_API_KEY', 'AIzaSyB4K9b9mmKvdt8rpliEVHyyJZuv4Cz-kI0');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent');

class ChatBot
{
    private $pdo;
    private $context;
    private $lastIntent;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->context = isset($_SESSION['chat_context']) ? $_SESSION['chat_context'] : [];
        $this->lastIntent = isset($_SESSION['last_intent']) ? $_SESSION['last_intent'] : null;
    }

    public function processMessage($message)
    {
        try {
            // Phân tích ý định của tin nhắn
            $intent = $this->analyzeIntent($message);

            // Lưu context
            $this->updateContext($message, $intent);

            // Xử lý theo ý định
            $response = $this->handleIntent($intent, $message);

            // Lưu trạng thái
            $this->saveState();

            return $response;
        } catch (Exception $e) {
            error_log("Error processing message: " . $e->getMessage());
            return $this->getErrorResponse();
        }
    }

    private function analyzeIntent($message)
    {
        $message = mb_strtolower(trim($message));
        $intent = [
            'type' => 'unknown',
            'entities' => []
        ];

        // Kiểm tra chào hỏi
        if (preg_match('/(xin chào|hi|hello|hey|chào|alo)/i', $message)) {
            $intent['type'] = 'greeting';
            return $intent;
        }

        // Kiểm tra tư vấn tour
        if (preg_match('/(?:tư vấn|xem|cho|cần).*?(tour)\s*(.*?)$/i', $message, $matches)) {
            $intent['type'] = 'tour_inquiry';
            if (!empty(trim($matches[2]))) {
                $intent['entities']['tour_name'] = trim($matches[2]);
            }
            return $intent;
        }

        // Kiểm tra tư vấn khách sạn
        if (preg_match('/(?:khách sạn|hotel|phòng|nghỉ)\s*(.*?)$/i', $message, $matches)) {
            $intent['type'] = 'hotel_inquiry';
            if (!empty(trim($matches[1]))) {
                $intent['entities']['hotel_name'] = trim($matches[1]);
            }
            return $intent;
        }

        // Kiểm tra hỏi giá
        if (preg_match('/(giá|chi phí|bao nhiêu)/i', $message)) {
            if ($this->lastIntent && $this->lastIntent['type'] === 'tour_inquiry') {
                $intent['type'] = 'price_inquiry';
                $intent['entities'] = $this->lastIntent['entities'];
            }
            return $intent;
        }

        // Kiểm tra đặt tour/phòng
        if (preg_match('/(đặt|book|mua|thanh toán)/i', $message)) {
            if ($this->lastIntent) {
                $intent['type'] = 'booking';
                $intent['entities'] = $this->lastIntent['entities'];
            }
            return $intent;
        }

        return $intent;
    }

    private function handleIntent($intent, $message)
    {
        switch ($intent['type']) {
            case 'greeting':
                return $this->handleGreeting();

            case 'tour_inquiry':
                if (isset($intent['entities']['tour_name'])) {
                    return $this->getTourDetails($intent['entities']['tour_name']);
                }
                return $this->listAvailableTours();

            case 'hotel_inquiry':
                if (isset($intent['entities']['hotel_name'])) {
                    return $this->getHotelDetails($intent['entities']['hotel_name']);
                }
                return $this->listAvailableHotels();

            case 'price_inquiry':
                if (isset($intent['entities']['tour_name'])) {
                    return $this->getTourPricing($intent['entities']['tour_name']);
                }
                return $this->getGenericPricingInfo();

            case 'booking':
                return $this->handleBookingIntent($intent);

            default:
                // Sử dụng Gemini API cho các câu hỏi không rõ ý định
                return $this->callGeminiAPI($message);
        }
    }

    private function getTourDetails($tourName)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM tours 
                WHERE LOWER(name) LIKE LOWER(?) OR LOWER(location) LIKE LOWER(?)
                LIMIT 1
            ");
            $searchTerm = "%{$tourName}%";
            $stmt->execute([$searchTerm, $searchTerm]);
            $tour = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tour) {
                return "Xin lỗi, tôi không tìm thấy tour \"{$tourName}\". Bạn có thể:\n"
                    . "- Xem các tour đang hot\n"
                    . "- Cho tôi biết bạn muốn đi đâu?\n"
                    . "- Hoặc nói rõ hơn về tour bạn cần?";
            }

            $priceVND = number_format($tour['price'] * 23000, 0, ',', '.');

            $response = "🎯 {$tour['name']} | {$tour['location']}\n\n"
                . "⏱️ {$tour['duration']} | 💰 {$priceVND} VNĐ\n"
                . "⭐ {$tour['rating']}/5\n\n"
                . "📝 {$tour['description']}\n\n";

            if (!empty($tour['itinerary'])) {
                $response .= "📅 Lịch trình:\n{$tour['itinerary']}\n\n";
            }

            $response .= "💡 Bạn muốn biết thêm:\n"
                . "- Chi tiết giá và khuyến mãi?\n"
                . "- Lịch khởi hành gần nhất?\n"
                . "- Đặt tour ngay?";

            return $response;
        } catch (Exception $e) {
            error_log("Error in getTourDetails: " . $e->getMessage());
            return $this->getErrorResponse();
        }
    }

    private function listAvailableTours()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT t.*, COUNT(b.id) as booking_count 
                FROM tours t 
                LEFT JOIN bookings b ON t.id = b.tour_id 
                GROUP BY t.id 
                ORDER BY booking_count DESC, t.rating DESC 
                LIMIT 3
            ");
            $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($tours)) {
                return "Xin lỗi, hiện tại chưa có thông tin tour. Vui lòng thử lại sau.";
            }

            $response = "🎯 Top 3 tour du lịch được yêu thích:\n\n";

            foreach ($tours as $tour) {
                $priceVND = number_format($tour['price'] * 23000, 0, ',', '.');
                $response .= "🌟 {$tour['name']}\n"
                    . "📍 {$tour['location']}\n"
                    . "⏱️ {$tour['duration']} | 💰 {$priceVND} VNĐ\n"
                    . "⭐ {$tour['rating']}/5\n"
                    . "📝 {$tour['description']}\n\n";
            }

            $response .= "💡 Để xem chi tiết tour nào, hãy nhắn: 'tư vấn tour [tên tour]'\n"
                . "Ví dụ: tư vấn tour Paris Adventure";

            return $response;
        } catch (Exception $e) {
            error_log("Error in listAvailableTours: " . $e->getMessage());
            return $this->getErrorResponse();
        }
    }

    private function getHotelDetails($hotelName)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM hotels 
                WHERE LOWER(name) LIKE LOWER(?) OR LOWER(location) LIKE LOWER(?)
                LIMIT 1
            ");
            $searchTerm = "%{$hotelName}%";
            $stmt->execute([$searchTerm, $searchTerm]);
            $hotel = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$hotel) {
                return "Xin lỗi, tôi không tìm thấy khách sạn \"{$hotelName}\". Bạn có thể:\n"
                    . "- Xem các khách sạn được đề xuất\n"
                    . "- Cho biết khu vực bạn muốn ở?\n"
                    . "- Hoặc nói rõ hơn về nhu cầu của bạn?";
            }

            $priceVND = number_format($hotel['price'] * 23000, 0, ',', '.');

            $response = "🏨 {$hotel['name']} | {$hotel['location']}\n\n"
                . "📍 {$hotel['address']}\n"
                . "💰 Giá từ: {$priceVND} VNĐ/đêm\n"
                . "⭐ {$hotel['rating']}/5\n\n";

            if (!empty($hotel['amenities'])) {
                $response .= "✨ Tiện nghi:\n{$hotel['amenities']}\n\n";
            }

            $response .= "💡 Bạn muốn biết thêm:\n"
                . "- Các loại phòng và giá?\n"
                . "- Chính sách đặt/hủy phòng?\n"
                . "- Đặt phòng ngay?";

            return $response;
        } catch (Exception $e) {
            error_log("Error in getHotelDetails: " . $e->getMessage());
            return $this->getErrorResponse();
        }
    }

    private function listAvailableHotels()
    {
        try {
            $stmt = $this->pdo->query("
                SELECT h.*, AVG(r.rating) as avg_rating 
                FROM hotels h 
                LEFT JOIN hotel_reviews r ON h.id = r.hotel_id 
                GROUP BY h.id 
                ORDER BY avg_rating DESC 
                LIMIT 3
            ");
            $hotels = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = "🏨 Top 3 khách sạn được đánh giá cao:\n\n";

            foreach ($hotels as $hotel) {
                $priceVND = number_format($hotel['price'] * 23000, 0, ',', '.');
                $response .= "🌟 {$hotel['name']}\n"
                    . "📍 {$hotel['location']}\n"
                    . "💰 Giá từ: {$priceVND} VNĐ/đêm\n"
                    . "⭐ {$hotel['rating']}/5\n"
                    . "✨ {$hotel['amenities']}\n\n";
            }

            $response .= "💡 Để xem chi tiết khách sạn nào, hãy nhắn: 'xem khách sạn [tên khách sạn]'\n"
                . "Hoặc cho tôi biết khu vực bạn muốn ở?";

            return $response;
        } catch (Exception $e) {
            error_log("Error in listAvailableHotels: " . $e->getMessage());
            return $this->getErrorResponse();
        }
    }

    private function getTourPricing($tourName)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM tours 
                WHERE LOWER(name) LIKE LOWER(?)
                LIMIT 1
            ");
            $stmt->execute(["%{$tourName}%"]);
            $tour = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tour) {
                return "Xin lỗi, tôi không tìm thấy thông tin giá của tour này.";
            }

            $priceVND = number_format($tour['price'] * 23000, 0, ',', '.');

            $response = "💰 Chi tiết giá tour {$tour['name']}:\n\n"
                . "- Giá gốc: {$priceVND} VNĐ/người\n";

            if (!empty($tour['price_includes'])) {
                $response .= "- Đã bao gồm:\n{$tour['price_includes']}\n\n";
            }

            if (!empty($tour['price_excludes'])) {
                $response .= "- Chưa bao gồm:\n{$tour['price_excludes']}\n\n";
            }

            $response .= "💡 Bạn muốn:\n"
                . "- Xem lịch khởi hành?\n"
                . "- Tư vấn thêm?\n"
                . "- Đặt tour ngay?";

            return $response;
        } catch (Exception $e) {
            error_log("Error in getTourPricing: " . $e->getMessage());
            return $this->getErrorResponse();
        }
    }

    private function handleGreeting()
    {
        $greetings = [
            "Xin chào! Tôi là Anna, trợ lý du lịch của DirEngine 🌟\n"
                . "Tôi có thể giúp bạn:\n"
                . "- Tư vấn tour du lịch\n"
                . "- Tìm khách sạn phù hợp\n"
                . "- Thông tin giá cả và khuyến mãi\n"
                . "Bạn cần tôi tư vấn gì nào?",

            "Chào bạn! 👋\n"
                . "Tôi là Anna, rất vui được hỗ trợ bạn.\n"
                . "Bạn đang tìm kiếm tour du lịch hay khách sạn?",

            "Hi! Rất vui được gặp bạn 😊\n"
                . "Tôi là Anna, tôi có thể giúp bạn tìm:\n"
                . "- Tour du lịch hấp dẫn\n"
                . "- Khách sạn chất lượng\n"
                . "- Ưu đãi đặc biệt\n"
                . "Bạn cần tư vấn về vấn đề nào?"
        ];

        return $greetings[array_rand($greetings)];
    }

    private function handleBookingIntent($intent)
    {
        if (isset($intent['entities']['tour_name'])) {
            return "Để đặt tour {$intent['entities']['tour_name']}, bạn cần:\n"
                . "1. Chọn ngày khởi hành\n"
                . "2. Số lượng người tham gia\n"
                . "3. Thông tin liên hệ\n\n"
                . "Bạn có thể:\n"
                . "- Gọi 1900xxxx để được hỗ trợ đặt tour\n"
                . "- Hoặc để lại số điện thoại, nhân viên sẽ liên hệ lại";
        }

        if (isset($intent['entities']['hotel_name'])) {
            return "Để đặt phòng tại {$intent['entities']['hotel_name']}, bạn cần:\n"
                . "1. Chọn ngày check-in/check-out\n"
                . "2. Số lượng phòng và người\n"
                . "3. Thông tin liên hệ\n\n"
                . "Bạn có thể:\n"
                . "- Gọi 1900xxxx để được hỗ trợ đặt phòng\n"
                . "- Hoặc để lại số điện thoại, nhân viên sẽ liên hệ lại";
        }

        return "Bạn muốn đặt tour du lịch hay khách sạn? Cho tôi biết thêm chi tiết nhé!";
    }

    private function updateContext($message, $intent)
    {
        // Giới hạn context để không quá lớn
        if (count($this->context) > 5) {
            array_shift($this->context);
        }

        $this->context[] = [
            'message' => $message,
            'intent' => $intent,
            'timestamp' => time()
        ];

        $this->lastIntent = $intent;
    }

    private function saveState()
    {
        $_SESSION['chat_context'] = $this->context;
        $_SESSION['last_intent'] = $this->lastIntent;
    }

    private function getErrorResponse()
    {
        return "Xin lỗi! Hệ thống đang gặp vấn đề. Bạn có thể:\n"
            . "- Thử lại sau ít phút\n"
            . "- Gọi 1900xxxx để được hỗ trợ\n"
            . "- Hoặc để lại số điện thoại, chúng tôi sẽ liên hệ lại";
    }

    private function callGeminiAPI($message)
    {
        try {
            $data = [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => "Bạn là Anna, một trợ lý du lịch thông minh của DirEngine. Hãy trả lời câu hỏi sau một cách thân thiện và chuyên nghiệp: " . $message
                            ]
                        ]
                    ]
                ]
            ];

            $ch = curl_init(GEMINI_API_URL . '?key=' . GEMINI_API_KEY);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($curlError = curl_error($ch)) {
                error_log("Curl Error: " . $curlError);
            }

            curl_close($ch);

            if ($httpCode === 200) {
                $result = json_decode($response, true);
                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    return $result['candidates'][0]['content']['parts'][0]['text'];
                }
            }

            error_log("API Error: HTTP Code " . $httpCode . ", Response: " . $response);
            return $this->getErrorResponse();
        } catch (Exception $e) {
            error_log("Error calling Gemini API: " . $e->getMessage());
            return $this->getErrorResponse();
        }
    }

    private function getGenericPricingInfo()
    {
        try {
            // Lấy thông tin giá trung bình của các tour
            $stmt = $this->pdo->query("
                SELECT 
                    MIN(price) as min_price,
                    MAX(price) as max_price,
                    AVG(price) as avg_price
                FROM tours
            ");
            $pricing = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pricing) {
                return "Xin lỗi, hiện tại chưa có thông tin về giá tour.";
            }

            $minPriceVND = number_format($pricing['min_price'] * 23000, 0, ',', '.');
            $maxPriceVND = number_format($pricing['max_price'] * 23000, 0, ',', '.');
            $avgPriceVND = number_format($pricing['avg_price'] * 23000, 0, ',', '.');

            $response = "💰 Thông tin giá tour du lịch:\n\n"
                . "- Giá thấp nhất: từ {$minPriceVND} VNĐ\n"
                . "- Giá trung bình: khoảng {$avgPriceVND} VNĐ\n"
                . "- Giá cao cấp: đến {$maxPriceVND} VNĐ\n\n"
                . "💡 Bạn muốn:\n"
                . "- Xem tour theo ngân sách?\n"
                . "- Tư vấn tour cụ thể?\n"
                . "- Thông tin khuyến mãi?";

            return $response;
        } catch (Exception $e) {
            error_log("Error in getGenericPricingInfo: " . $e->getMessage());
            return $this->getErrorResponse();
        }
    }
}

// Xử lý request từ frontend
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = file_get_contents('php://input');
        error_log("Received request: " . $input);

        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON data");
        }

        if (empty($data['message'])) {
            throw new Exception("Message is required");
        }

        $chatbot = new ChatBot($pdo);
        $response = $chatbot->processMessage($data['message']);

        header('Content-Type: application/json');
        echo json_encode(['response' => $response]);
    } catch (Exception $e) {
        error_log("Request Error: " . $e->getMessage());
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'message' => 'Xin lỗi! Hệ thống đang bận, vui lòng thử lại sau hoặc gọi 19001234.'
        ]);
    }
}
