<?php
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="hero-wrap js-fullheight" style="background-image: url('images/bg_4.jpg');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center" data-scrollax-parent="true">
            <div class="col-md-9 ftco-animate text-center" data-scrollax=" properties: { translateY: '70%' }">
                <p class="breadcrumbs" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }">
                    <span class="mr-2"><a href="index.php">Home</a></span>
                    <span>Blog</span>
                </p>
                <h1 class="mb-3 bread" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }">Tips &amp; Articles</h1>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section bg-light">
    <div class="container">
        <div class="row d-flex">
            <?php
            // Fetch blog posts from database
            $stmt = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT 8");
            while ($post = $stmt->fetch()) {
            ?>
                <div class="col-md-3 d-flex ftco-animate">
                    <div class="blog-entry align-self-stretch">
                        <a href="blog-single.php?id=<?php echo $post['id']; ?>" class="block-20" style="background-image: url('images/<?php echo $post['image']; ?>');">
                        </a>
                        <div class="text p-4 d-block">
                            <span class="tag"><?php echo $post['category']; ?></span>
                            <h3 class="heading mt-3">
                                <a href="blog-single.php?id=<?php echo $post['id']; ?>"><?php echo $post['title']; ?></a>
                            </h3>
                            <div class="meta mb-3">
                                <div><a href="#"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></a></div>
                                <div><a href="#"><?php echo $post['author']; ?></a></div>
                                <div><a href="#" class="meta-chat"><span class="icon-chat"></span> <?php echo $post['comments_count']; ?></a></div>
                            </div>
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
</section>

<?php
include 'components/chatbot.php';
include 'includes/footer.php';
?>