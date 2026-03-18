    </main>
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>About Us</h3>
                <p>Your premier online bookstore with thousands of titles across all genres. Fast delivery and excellent customer service.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Books</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <li><a href="orders.php">Orders</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Categories</h3>
                <ul>
                    <?php
                    // Footer categories - safe fail
                    if (isset($pdo)) {
                        $footerCats = $pdo->query("SELECT * FROM categories LIMIT 4")->fetchAll();
                        foreach($footerCats as $cat) {
                            echo '<li><a href="products.php?category='.$cat['id'].'">'.$cat['category_name'].'</a></li>';
                        }
                    }
                    ?>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Contact Info</h3>
                <ul class="contact-info">
                    <li><i class="fas fa-map-marker-alt"></i>Mangalore Book Stall</li>
                    <li><i class="fas fa-phone"></i>7259818255</li>
                    <li><i class="fas fa-envelope"></i> chirajkenjila@gmail.com</li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2024 Online Book Store. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="assets/js/main.js"></script>
</body>
</html>