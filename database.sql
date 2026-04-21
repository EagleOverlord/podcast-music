-- ============================================================
-- Greenfield Local Hub — Database Setup
-- Import via phpMyAdmin or: mysql -u root < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS greenfield_hub
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE greenfield_hub;

-- ── Users ────────────────────────────────────────────────────
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    first_name  VARCHAR(100) NOT NULL,
    last_name   VARCHAR(100) NOT NULL,
    email       VARCHAR(255) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('customer','producer') DEFAULT 'customer',
    address     TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Categories ───────────────────────────────────────────────
CREATE TABLE categories (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- ── Products ─────────────────────────────────────────────────
CREATE TABLE products (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(255) NOT NULL,
    description    TEXT,
    price          DECIMAL(10,2) NOT NULL,
    image          VARCHAR(255),
    category_id    INT,
    producer_id    INT,
    stock_quantity INT DEFAULT 100,
    featured       TINYINT(1) DEFAULT 0,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (producer_id) REFERENCES users(id)
);

-- ── Orders ───────────────────────────────────────────────────
CREATE TABLE orders (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    customer_id      INT NOT NULL,
    status           ENUM('ordered','processing','delivered') DEFAULT 'ordered',
    delivery_type    ENUM('pickup','delivery') DEFAULT 'pickup',
    total_price      DECIMAL(10,2) NOT NULL,
    delivery_address TEXT,
    card_name        VARCHAR(255),
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id)
);

-- ── Order Items ──────────────────────────────────────────────
CREATE TABLE order_items (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT NOT NULL,
    product_id INT NOT NULL,
    quantity   INT NOT NULL,
    price      DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- ============================================================
-- Sample Data
-- ============================================================

-- Users  (passwords stored as plain text per project requirements)
INSERT INTO users (first_name, last_name, email, password, role, address) VALUES
('Farm',  'Producer', 'producer@greenfield.com', 'producer123', 'producer', NULL),
('Jane',  'Doe',      'jane@example.com',         'customer123', 'customer', '123 Main Street, Greenfield, GL1 2AB'),
('John',  'Smith',    'john@example.com',          'customer123', 'customer', '45 Oak Avenue, Greenfield, GL2 5CD');

-- Categories
INSERT INTO categories (name) VALUES
('Vegetables'),
('Fruit'),
('Dairy');

-- Products  (producer_id = 1 throughout — the single producer account)
INSERT INTO products (name, description, price, image, category_id, producer_id, stock_quantity, featured) VALUES
('Free-range Eggs',
 'Fresh from local farms, our free-range eggs come from hens raised with space to roam outdoors and a natural, well-balanced diet. This results in rich golden yolks, firm whites, and a full flavour that\'s perfect for everything from hearty breakfasts to baking and cooking.\n\nEach egg is carefully collected and supplied by trusted local producers who prioritise animal welfare and sustainable farming practices. By choosing these eggs, you\'re supporting nearby farms, reducing food miles, and enjoying produce that is as fresh and transparent as possible.',
 2.50, 'assets/eggs.png', 3, 1, 120, 0),

('Organic Tomatoes',
 'Vine-ripened organic tomatoes grown without pesticides. Sweet, juicy and full of flavour — perfect for salads, pasta sauces and Sunday roasts. Sourced directly from local growers who care about the land as much as the produce.',
 1.99, 'assets/tomatoes.png', 1, 1, 85, 0),

('Organic Grapes',
 'Plump and sweet organic grapes, hand-picked at peak ripeness. A perfect healthy snack for all the family, or a premium addition to any cheese board. Grown without synthetic pesticides to keep every bunch as natural as possible.',
 0.99, 'assets/grapes.png', 2, 1, 60, 0),

('Oranges',
 'Freshly harvested juicy oranges bursting with natural vitamin C. Great for fresh-squeezed juice, snacking, or adding a citrus burst to your cooking. These beauties are picked at their sweetest and delivered within days.',
 0.50, 'assets/oranges.png', 2, 1, 200, 0),

('Apples',
 'Crisp and refreshing locally grown apples with the perfect balance of sweet and sharp. Ideal for lunchboxes, crumbles, cider, or simply enjoyed as a fresh healthy snack straight from the farm.',
 0.50, 'assets/apples.png', 2, 1, 150, 0),

('Blueberries',
 'Antioxidant-rich blueberries hand-picked from local bushes at their most vibrant. Perfect tumbled into porridge, blended into smoothies, or eaten by the handful as a guilt-free treat.',
 0.99, 'assets/blueberries.png', 2, 1, 8, 0),

('Organic Carrots',
 'Sweet and crunchy organic carrots pulled fresh from the earth. Packed with vitamins A and K — perfect for roasting until caramelised, enriching soups and stews, or simply dipping into hummus.',
 1.50, 'assets/carrots.png', 1, 1, 90, 1),

('Artisan Cheese',
 'Handcrafted artisan cheese from local dairies, aged to perfection. Rich, creamy and full of character — a centrepiece for any cheese board and a secret weapon in the kitchen for elevating pasta, risottos and more.',
 3.50, 'assets/cheese.png', 3, 1, 45, 1);

-- Sample orders
INSERT INTO orders (customer_id, status, delivery_type, total_price, delivery_address, card_name, created_at) VALUES
(2, 'delivered',  'delivery', 21.30, '123 Main Street, Greenfield, GL1 2AB', 'Jane Doe',  '2025-02-27 10:30:00'),
(2, 'processing', 'delivery',  9.97, '123 Main Street, Greenfield, GL1 2AB', 'Jane Doe',  '2025-03-01 14:20:00'),
(3, 'ordered',    'pickup',    5.48, NULL,                                    'John Smith','2025-03-02 09:15:00');

INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 3, 2.50),
(1, 8, 2, 3.50),
(1, 7, 4, 1.50),
(2, 3, 3, 0.99),
(2, 5, 2, 0.50),
(3, 2, 2, 1.99),
(3, 4, 3, 0.50);
