<?php
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="hero-wrap js-fullheight" style="background-image: url('images/bg_1.jpg');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-start"
            data-scrollax-parent="true">
            <div class="col-md-9 ftco-animate" data-scrollax=" properties: { translateY: '70%' }">
                <h1 class="mb-4" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }"><strong>Explore
                        <br></strong> your amazing city</h1>
                <p data-scrollax="properties: { translateY: '30%', opacity: 1.6 }">Find great places to stay, eat, shop,
                    or visit from local experts</p>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section services-section bg-light">
    <div class="container">
        <div class="row d-flex">
            <div class="col-md-3 d-flex align-self-stretch ftco-animate">
                <div class="media block-6 services d-block text-center">
                    <div class="d-flex justify-content-center">
                        <div class="icon"><span class="flaticon-guarantee"></span></div>
                    </div>
                    <div class="media-body p-2 mt-2">
                        <h3 class="heading mb-3">Best Price Guarantee</h3>
                        <p>A small river named Duden flows by their place and supplies.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 d-flex align-self-stretch ftco-animate">
                <div class="media block-6 services d-block text-center">
                    <div class="d-flex justify-content-center">
                        <div class="icon"><span class="flaticon-like"></span></div>
                    </div>
                    <div class="media-body p-2 mt-2">
                        <h3 class="heading mb-3">Travellers Love Us</h3>
                        <p>A small river named Duden flows by their place and supplies.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 d-flex align-self-stretch ftco-animate">
                <div class="media block-6 services d-block text-center">
                    <div class="d-flex justify-content-center">
                        <div class="icon"><span class="flaticon-detective"></span></div>
                    </div>
                    <div class="media-body p-2 mt-2">
                        <h3 class="heading mb-3">Best Travel Agent</h3>
                        <p>A small river named Duden flows by their place and supplies.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 d-flex align-self-stretch ftco-animate">
                <div class="media block-6 services d-block text-center">
                    <div class="d-flex justify-content-center">
                        <div class="icon"><span class="flaticon-support"></span></div>
                    </div>
                    <div class="media-body p-2 mt-2">
                        <h3 class="heading mb-3">Our Dedicated Support</h3>
                        <p>A small river named Duden flows by their place and supplies.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Tours Section -->
<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-start mb-5 pb-3">
            <div class="col-md-7 heading-section ftco-animate">
                <span class="subheading">Special Offers</span>
                <h2 class="mb-4"><strong>Top</strong> Tour Packages</h2>
            </div>
        </div>
        <div class="row">
            <?php
            $stmt = $pdo->query("SELECT * FROM tours WHERE rating >= 4.5 LIMIT 3");
            while ($tour = $stmt->fetch()) {
            ?>
                <div class="col-md-4 ftco-animate">
                    <div class="destination">
                        <a href="tour-single.php?id=<?php echo $tour['id']; ?>"
                            class="img img-2 d-flex justify-content-center align-items-center"
                            style="background-image: url(images/<?php echo $tour['image']; ?>);">
                            <div class="icon d-flex justify-content-center align-items-center">
                                <span class="icon-search2"></span>
                            </div>
                        </a>
                        <div class="text p-3">
                            <div class="d-flex">
                                <div class="one">
                                    <h3><a
                                            href="tour-single.php?id=<?php echo $tour['id']; ?>"><?php echo $tour['name']; ?></a>
                                    </h3>
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
                            <hr>
                            <p class="bottom-area d-flex">
                                <span><i class="icon-map-o"></i> <?php echo $tour['location']; ?></span>
                                <span class="ml-auto"><a
                                        href="tour-single.php?id=<?php echo $tour['id']; ?>">Discover</a></span>
                            </p>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</section>

<?php
include 'includes/footer.php';
?>