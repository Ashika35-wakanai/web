-- Table to track product order counts by date
CREATE TABLE IF NOT EXISTS product_order_count (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    order_date DATE NOT NULL,
    order_count INT NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id)
);
