<?php
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Get hotel ID from URL
$hotel_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch hotel details
$stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
$stmt->execute([$hotel_id]);
$hotel = $stmt->fetch();

if (!$hotel) {
    header("Location: hotel.php");
    exit();
}

// Fetch room types for this hotel
$stmt = $pdo->prepare("SELECT * FROM room_types WHERE hotel_id = ?");
$stmt->execute([$hotel_id]);
$rooms = $stmt->fetchAll();

// Convert amenities string to array
$amenities = explode(',', $hotel['amenities']);
?>

<div class="hero-wrap js-fullheight" style="background-image: url('images/<?php echo $hotel['image']; ?>');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center" data-scrollax-parent="true">
            <div class="col-md-9 text-center ftco-animate" data-scrollax=" properties: { translateY: '70%' }">
                <p class="breadcrumbs" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }">
                    <span class="mr-2"><a href="index.php">Home</a></span>
                    <span class="mr-2"><a href="hotel.php">Hotels</a></span>
                    <span><?php echo $hotel['name']; ?></span>
                </p>
                <h1 class="mb-3 bread" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }"><?php echo $hotel['name']; ?></h1>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section ftco-degree-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-md-12 ftco-animate">
                        <div class="single-slider owl-carousel">
                            <div class="item">
                                <div class="hotel-img" style="background-image: url(images/<?php echo $hotel['image']; ?>);"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 hotel-single mt-4 mb-5 ftco-animate">
                        <span><?php echo $hotel['location']; ?></span>
                        <h2><?php echo $hotel['name']; ?></h2>
                        <p class="rate mb-5">
                            <span class="loc"><a href="#"><i class="icon-map"></i> <?php echo $hotel['address']; ?></a></span>
                            <span class="star">
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
                            </span>
                        </p>
                        <p><?php echo $hotel['description']; ?></p>

                        <div class="mt-5">
                            <h4 class="mb-4">Available Amenities</h4>
                            <div class="row">
                                <?php foreach ($amenities as $amenity) : ?>
                                    <div class="col-md-4">
                                        <p><i class="icon-check"></i> <?php echo trim($amenity); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 hotel-single ftco-animate mb-5 mt-5">
                        <h4 class="mb-4">Available Rooms</h4>
                        <div class="row">
                            <?php foreach ($rooms as $room) :
                                $room_amenities = explode(',', $room['amenities']);
                            ?>
                                <div class="col-md-6 mb-4">
                                    <div class="destination">
                                        <a href="#" class="img img-2" style="background-image: url(images/<?php echo $room['image']; ?>);"></a>
                                        <div class="text p-3">
                                            <div class="d-flex">
                                                <div class="one">
                                                    <h3><?php echo $room['name']; ?></h3>
                                                    <p>Capacity: <?php echo $room['capacity']; ?> persons</p>
                                                </div>
                                                <div class="two">
                                                    <span class="price per-price">$<?php echo $room['price']; ?><br><small>/night</small></span>
                                                </div>
                                            </div>
                                            <p><?php echo $room['description']; ?></p>
                                            <hr>
                                            <p class="bottom-area d-flex">
                                                <span>
                                                    <?php
                                                    $amenities_count = count($room_amenities);
                                                    echo implode(', ', array_slice($room_amenities, 0, 2));
                                                    if ($amenities_count > 2) {
                                                        echo " +" . ($amenities_count - 2) . " more";
                                                    }
                                                    ?>
                                                </span>
                                                <span class="ml-auto"><a href="#" class="btn-custom" data-toggle="modal" data-target="#bookingModal" data-room="<?php echo $room['id']; ?>" data-name="<?php echo $room['name']; ?>" data-price="<?php echo $room['price']; ?>">Book Now</a></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="col-md-12 hotel-single ftco-animate mb-5 mt-4">
                        <h4 class="mb-4">Location & Nearby</h4>
                        <div class="block-16">
                            <div id="map" style="width: 100%; height: 400px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4 sidebar">
                <div class="sidebar-wrap bg-light ftco-animate">
                    <h3 class="heading mb-4">Quick Room Booking</h3>
                    <form action="api/booking.php" method="post">
                        <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
                        <div class="fields">
                            <div class="form-group">
                                <input type="text" name="name" class="form-control" placeholder="Name" required>
                            </div>
                            <div class="form-group">
                                <input type="email" name="email" class="form-control" placeholder="Email" required>
                            </div>
                            <div class="form-group">
                                <input type="text" name="phone" class="form-control" placeholder="Phone" required>
                            </div>
                            <div class="form-group">
                                <input type="text" id="checkin_date" name="checkin_date" class="form-control" placeholder="Check-in date" required>
                            </div>
                            <div class="form-group">
                                <input type="text" id="checkout_date" name="checkout_date" class="form-control" placeholder="Check-out date" required>
                            </div>
                            <div class="form-group">
                                <div class="select-wrap one-third">
                                    <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                                    <select name="room_type" class="form-control" required>
                                        <option value="">Select Room Type</option>
                                        <?php foreach ($rooms as $room) : ?>
                                            <option value="<?php echo $room['id']; ?>"><?php echo $room['name']; ?> ($<?php echo $room['price']; ?>/night)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="select-wrap one-third">
                                    <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                                    <select name="guests" class="form-control" required>
                                        <option value="">Number of Guests</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="submit" value="Book Now" class="btn btn-primary py-3 px-5">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookingModalLabel">Book Room</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="api/booking.php" method="post" id="modalBookingForm">
                    <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
                    <input type="hidden" name="room_type" id="modal_room_type">
                    <div class="form-group">
                        <label>Room Type</label>
                        <p id="modal_room_name"></p>
                    </div>
                    <div class="form-group">
                        <label>Price per Night</label>
                        <p id="modal_room_price"></p>
                    </div>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Check-in Date</label>
                        <input type="text" name="checkin_date" class="form-control datepicker" required>
                    </div>
                    <div class="form-group">
                        <label>Check-out Date</label>
                        <input type="text" name="checkout_date" class="form-control datepicker" required>
                    </div>
                    <div class="form-group">
                        <label>Number of Guests</label>
                        <select name="guests" class="form-control" required>
                            <option value="">Select</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" form="modalBookingForm" class="btn btn-primary">Book Now</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize datepickers
    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            startDate: 'today',
            autoclose: true
        });

        // Handle booking modal
        $('#bookingModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var roomId = button.data('room');
            var roomName = button.data('name');
            var roomPrice = button.data('price');

            var modal = $(this);
            modal.find('#modal_room_type').val(roomId);
            modal.find('#modal_room_name').text(roomName);
            modal.find('#modal_room_price').text('$' + roomPrice + ' per night');
        });
    });

    // Initialize Google Maps
    function initMap() {
        var hotel = {
            lat: YOUR_LATITUDE,
            lng: YOUR_LONGITUDE
        };
        var map = new google.maps.Map(document.getElementById('map'), {
            zoom: 15,
            center: hotel
        });
        var marker = new google.maps.Marker({
            position: hotel,
            map: map,
            title: '<?php echo $hotel['name']; ?>'
        });
    }
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap"></script>

<?php
include 'components/chatbot.php';
include 'includes/footer.php';
?>