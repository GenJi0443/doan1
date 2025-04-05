<?php
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Get post ID from URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch blog post
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: blog.php");
    exit();
}
?>

<div class="hero-wrap js-fullheight" style="background-image: url('images/<?php echo $post['image']; ?>');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center" data-scrollax-parent="true">
            <div class="col-md-9 ftco-animate text-center" data-scrollax=" properties: { translateY: '70%' }">
                <p class="breadcrumbs" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }">
                    <span class="mr-2"><a href="index.php">Home</a></span>
                    <span class="mr-2"><a href="blog.php">Blog</a></span>
                    <span><?php echo $post['title']; ?></span>
                </p>
                <h1 class="mb-3 bread" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }"><?php echo $post['title']; ?></h1>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section ftco-degree-bg">
    <div class="container">
        <div class="row">
            <div class="col-md-8 ftco-animate">
                <h2 class="mb-3"><?php echo $post['title']; ?></h2>
                <p>
                    <img src="images/<?php echo $post['image']; ?>" alt="" class="img-fluid">
                </p>
                <?php echo $post['content']; ?>

                <div class="tag-widget post-tag-container mb-5 mt-5">
                    <div class="tagcloud">
                        <?php
                        $tags = explode(',', $post['tags']);
                        foreach ($tags as $tag) {
                            echo '<a href="#" class="tag-cloud-link">' . trim($tag) . '</a>';
                        }
                        ?>
                    </div>
                </div>

                <div class="about-author d-flex p-4 bg-light">
                    <div class="bio mr-5">
                        <img src="images/person_1.jpg" alt="Image placeholder" class="img-fluid mb-4">
                    </div>
                    <div class="desc">
                        <h3><?php echo $post['author']; ?></h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ducimus itaque, autem necessitatibus voluptate quod mollitia delectus aut.</p>
                    </div>
                </div>

                <div class="pt-5 mt-5">
                    <h3 class="mb-5"><?php echo $post['comments_count']; ?> Comments</h3>
                    <ul class="comment-list">
                        <?php
                        // Fetch comments for this post
                        $stmt = $pdo->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at DESC");
                        $stmt->execute([$post_id]);
                        while ($comment = $stmt->fetch()) {
                        ?>
                            <li class="comment">
                                <div class="vcard bio">
                                    <img src="images/person_1.jpg" alt="Image placeholder">
                                </div>
                                <div class="comment-body">
                                    <h3><?php echo htmlspecialchars($comment['name']); ?></h3>
                                    <div class="meta"><?php echo date('F j, Y \a\t g:i a', strtotime($comment['created_at'])); ?></div>
                                    <p><?php echo htmlspecialchars($comment['content']); ?></p>
                                    <p><a href="#" class="reply">Reply</a></p>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>

                    <div class="comment-form-wrap pt-5">
                        <h3 class="mb-5">Leave a comment</h3>
                        <form action="api/comment.php" method="post" class="p-5 bg-light">
                            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                            <div class="form-group">
                                <label for="name">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="message">Message</label>
                                <textarea name="message" id="message" cols="30" rows="10" class="form-control" required></textarea>
                            </div>
                            <div class="form-group">
                                <input type="submit" value="Post Comment" class="btn py-3 px-4 btn-primary">
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4 sidebar ftco-animate">
                <div class="sidebar-box">
                    <form action="#" class="search-form">
                        <div class="form-group">
                            <span class="icon fa fa-search"></span>
                            <input type="text" class="form-control" placeholder="Type a keyword and hit enter">
                        </div>
                    </form>
                </div>
                <div class="sidebar-box ftco-animate">
                    <div class="categories">
                        <h3>Categories</h3>
                        <?php
                        // Fetch categories with post counts
                        $stmt = $pdo->query("SELECT category, COUNT(*) as count FROM blog_posts GROUP BY category");
                        while ($category = $stmt->fetch()) {
                            echo '<li><a href="#">' . $category['category'] . ' <span>(' . $category['count'] . ')</span></a></li>';
                        }
                        ?>
                    </div>
                </div>

                <div class="sidebar-box ftco-animate">
                    <h3>Recent Blog</h3>
                    <?php
                    // Fetch recent posts
                    $stmt = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT 3");
                    while ($recent_post = $stmt->fetch()) {
                    ?>
                        <div class="block-21 mb-4 d-flex">
                            <a class="blog-img mr-4" style="background-image: url(images/<?php echo $recent_post['image']; ?>);"></a>
                            <div class="text">
                                <h3 class="heading"><a href="blog-single.php?id=<?php echo $recent_post['id']; ?>"><?php echo $recent_post['title']; ?></a></h3>
                                <div class="meta">
                                    <div><a href="#"><span class="icon-calendar"></span> <?php echo date('M j, Y', strtotime($recent_post['created_at'])); ?></a></div>
                                    <div><a href="#"><span class="icon-person"></span> <?php echo $recent_post['author']; ?></a></div>
                                    <div><a href="#"><span class="icon-chat"></span> <?php echo $recent_post['comments_count']; ?></a></div>
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