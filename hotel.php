<?php
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Get filter parameters
$location = isset($_GET['location']) ? $_GET['location'] : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 10000;
$rating = isset($_GET['rating']) ? (float)$_GET['rating'] : 0;

// Build query based on filters
$query = "SELECT h.*, MIN(r.price) as min_price, MAX(r.price) as max_price 
          FROM hotels h 
          LEFT JOIN room_types r ON h.id = r.hotel_id 
          WHERE 1=1";
$params = [];

if ($location) {
    $query .= " AND (h.location LIKE ? OR h.name LIKE ?)";
    $params[] = "%$location%";
    $params[] = "%$location%";
}
if ($rating > 0) {
    $query .= " AND h.rating >= ?";
    $params[] = $rating;
}

$query .= " GROUP BY h.id HAVING 1=1";

if ($min_price > 0) {
    $query .= " AND min_price >= ?";
    $params[] = $min_price;
}
if ($max_price < 10000) {
    $query .= " AND max_price <= ?";
    $params[] = $max_price;
}
?>

<div class="hero-wrap js-fullheight" style="background-image: url('images/bg_5.jpg');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center" data-scrollax-parent="true">
            <div class="col-md-9 text-center ftco-animate" data-scrollax=" properties: { translateY: '70%' }">
                <p class="breadcrumbs" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }">
                    <span class="mr-2"><a href="index.php">Home</a></span>
                    <span>Hotels</span>
                </p>
                <h1 class="mb-3 bread" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }">Hotels</h1>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section ftco-degree-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 sidebar">
                <div class="sidebar-wrap bg-light ftco-animate">
                    <h3 class="heading mb-4">Find Hotels</h3>
                    <form action="hotel.php" method="get">
                        <div class="fields">
                            <div class="form-group">
                                <input type="text" name="location" class="form-control" placeholder="Location, Hotel name" value="<?php echo htmlspecialchars($location); ?>">
                            </div>
                            <div class="form-group">
                                <input type="text" name="min_price" class="form-control" placeholder="Min Price" value="<?php echo $min_price > 0 ? $min_price : ''; ?>">
                            </div>
                            <div class="form-group">
                                <input type="text" name="max_price" class="form-control" placeholder="Max Price" value="<?php echo $max_price < 10000 ? $max_price : ''; ?>">
                            </div>
                            <div class="form-group">
                                <div class="range-slider">
                                    <span>
                                        Rating
                                        <input type="number" name="rating" value="<?php echo $rating; ?>" min="0" max="5" step="0.5" />
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="submit" value="Search" class="btn btn-primary py-3 px-5">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="row">
                    <?php
                    // Execute query with filters
                    $stmt = $pdo->prepare($query);
                    $stmt->execute($params);

                    while ($hotel = $stmt->fetch()) {
                        // Convert amenities string to array
                        $amenities = explode(',', $hotel['amenities']);
                    ?>
                        <div class="col-md-4 ftco-animate">
                            <div class="destination">
                                <a href="hotel-single.php?id=<?php echo $hotel['id']; ?>" class="img img-2 d-flex justify-content-center align-items-center" style="background-image: url(images/<?php echo $hotel['image']; ?>);">
                                    <div class="icon d-flex justify-content-center align-items-center">
                                        <span class="icon-search2"></span>
                                    </div>
                                </a>
                                <div class="text p-3">
                                    <div class="d-flex">
                                        <div class="one">
                                            <h3><a href="hotel-single.php?id=<?php echo $hotel['id']; ?>"><?php echo $hotel['name']; ?></a></h3>
                                            <p class="rate">
                                                <?php
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $hotel['rating']) {
                                                        echo '<i class="icon-star"></i>';
                                                    } else {
                                                        echo '<i class="icon-star-o"></i>';
                                                    }
                                                }
                                                ?>
                                                <span><?php echo $hotel['rating']; ?> Rating</span>
                                            </p>
                                        </div>
                                        <div class="two">
                                            <span class="price per-price">$<?php echo $hotel['min_price']; ?><br><small>/night</small></span>
                                        </div>
                                    </div>
                                    <p><?php echo substr($hotel['description'], 0, 100); ?>...</p>
                                    <hr>
                                    <p class="bottom-area d-flex">
                                        <span><i class="icon-map-o"></i> <?php echo $hotel['location']; ?></span>
                                        <span class="ml-auto"><a href="hotel-single.php?id=<?php echo $hotel['id']; ?>">Details</a></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include 'components/chatbot.php';
include 'includes/footer.php';
?>