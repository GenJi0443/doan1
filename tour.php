<?php
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 10000;
$rating = isset($_GET['rating']) ? (float)$_GET['rating'] : 0;

// Build query based on filters
$query = "SELECT * FROM tours WHERE 1=1";
$params = [];

if ($category) {
    $query .= " AND category = ?";
    $params[] = $category;
}
if ($location) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location%";
}
if ($min_price > 0) {
    $query .= " AND price >= ?";
    $params[] = $min_price;
}
if ($max_price < 10000) {
    $query .= " AND price <= ?";
    $params[] = $max_price;
}
if ($rating > 0) {
    $query .= " AND rating >= ?";
    $params[] = $rating;
}
?>

<div class="hero-wrap js-fullheight" style="background-image: url('images/bg_5.jpg');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center" data-scrollax-parent="true">
            <div class="col-md-9 text-center ftco-animate" data-scrollax=" properties: { translateY: '70%' }">
                <p class="breadcrumbs" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }">
                    <span class="mr-2"><a href="index.php">Home</a></span>
                    <span>Tour</span>
                </p>
                <h1 class="mb-3 bread" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }">Destination</h1>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section ftco-degree-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 sidebar">
                <div class="sidebar-wrap bg-light ftco-animate">
                    <h3 class="heading mb-4">Find City</h3>
                    <form action="tour.php" method="get">
                        <div class="fields">
                            <div class="form-group">
                                <input type="text" name="location" class="form-control" placeholder="Destination, City" value="<?php echo htmlspecialchars($location); ?>">
                            </div>
                            <div class="form-group">
                                <div class="select-wrap one-third">
                                    <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                                    <select name="category" class="form-control">
                                        <option value="">Select Category</option>
                                        <option value="restaurant" <?php echo $category == 'restaurant' ? 'selected' : ''; ?>>Restaurant</option>
                                        <option value="hotel" <?php echo $category == 'hotel' ? 'selected' : ''; ?>>Hotel</option>
                                        <option value="place" <?php echo $category == 'place' ? 'selected' : ''; ?>>Places</option>
                                    </select>
                                </div>
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

                    while ($tour = $stmt->fetch()) {
                    ?>
                        <div class="col-md-4 ftco-animate">
                            <div class="destination">
                                <a href="tour-single.php?id=<?php echo $tour['id']; ?>" class="img img-2 d-flex justify-content-center align-items-center" style="background-image: url(images/<?php echo $tour['image']; ?>);">
                                    <div class="icon d-flex justify-content-center align-items-center">
                                        <span class="icon-search2"></span>
                                    </div>
                                </a>
                                <div class="text p-3">
                                    <div class="d-flex">
                                        <div class="one">
                                            <h3><a href="tour-single.php?id=<?php echo $tour['id']; ?>"><?php echo $tour['name']; ?></a></h3>
                                            <p class="rate">
                                                <?php
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $tour['rating']) {
                                                        echo '<i class="icon-star"></i>';
                                                    } else {
                                                        echo '<i class="icon-star-o"></i>';
                                                    }
                                                }
                                                ?>
                                                <span><?php echo $tour['rating']; ?> Rating</span>
                                            </p>
                                        </div>
                                        <div class="two">
                                            <span class="price">$<?php echo $tour['price']; ?></span>
                                        </div>
                                    </div>
                                    <p><?php echo substr($tour['description'], 0, 100); ?>...</p>
                                    <p class="days"><span><?php echo $tour['duration']; ?></span></p>
                                    <hr>
                                    <p class="bottom-area d-flex">
                                        <span><i class="icon-map-o"></i> <?php echo $tour['location']; ?></span>
                                        <span class="ml-auto"><a href="tour-single.php?id=<?php echo $tour['id']; ?>">Discover</a></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="row mt-5">
                    <div class="col text-center">
                        <div class="block-27">
                            <ul>
                                <li><a href="#">&lt;</a></li>
                                <li class="active"><span>1</span></li>
                                <li><a href="#">2</a></li>
                                <li><a href="#">3</a></li>
                                <li><a href="#">4</a></li>
                                <li><a href="#">5</a></li>
                                <li><a href="#">&gt;</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
include 'components/chatbot.php';
include 'includes/footer.php';
?>