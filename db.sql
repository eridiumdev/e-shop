USE mysql;
DELETE FROM user WHERE password = '' AND user != 'root';

DROP DATABASE IF EXISTS eshop;
CREATE DATABASE eshop DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE USER IF NOT EXISTS admin@localhost identified by '123';
GRANT SELECT, INSERT, UPDATE, DELETE, ALTER, DROP, CREATE
ON eshop.* to admin@localhost identified by '123';

USE eshop;

CREATE TABLE users (
    id            int(10) NOT NULL AUTO_INCREMENT,
    username      varchar(30) NULL UNIQUE,
    email         varchar(30) NULL UNIQUE,
    password      varchar(64) NULL,
    type          varchar(5) NOT NULL DEFAULT 'user',
    registeredAt  datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

CREATE TABLE shipping (
    id      int(10) NOT NULL AUTO_INCREMENT,
    name    varchar(30) NOT NULL,
    phone   varchar(15) NOT NULL,
    address varchar(100) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE user_shipping (
    userId  int(10) NOT NULL,
    shipId  int(10) NOT NULL,
    PRIMARY KEY (userId, shipId)
);

CREATE TABLE orders (
    id          int(10) NOT NULL AUTO_INCREMENT,
    userId      int(10) NOT NULL,
    shipId      int(10) NOT NULL,
    date        datetime DEFAULT CURRENT_TIMESTAMP,
    statusId    int(10) NOT NULL,
    deliveryId  int(10) NOT NULL,
    paymentId   int(10) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE order_statuses (
    id     int(10) NOT NULL AUTO_INCREMENT,
    name   varchar(30) NOT NULL UNIQUE,
    PRIMARY KEY (id)
);

CREATE TABLE deliveries (
    id          int(10) NOT NULL AUTO_INCREMENT,
    name        varchar(30) NOT NULL UNIQUE,
    description varchar(100) NOT NULL,
    price       numeric(4,2) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE payments (
    id          int(10) NOT NULL AUTO_INCREMENT,
    name        varchar(30) NOT NULL UNIQUE,
    description varchar(100) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE categories (
    id          int(10) NOT NULL AUTO_INCREMENT,
    name        varchar(60) NOT NULL UNIQUE,
    description varchar(100) NULL,
    uri         varchar(20) NOT NULL UNIQUE,
    PRIMARY KEY (id)
);

CREATE TABLE specs (
    id          int(10) NOT NULL AUTO_INCREMENT,
    name        varchar(60) NOT NULL,
    type        varchar(15) NOT NULL,
    isRequired  boolean NOT NULL,
    isFilter    boolean NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE spec_cats (
    specId  int(10) NOT NULL,
    catId   int(10) NOT NULL,
    PRIMARY KEY (specId, catId)
);

CREATE TABLE products (
    id           int(10) NOT NULL AUTO_INCREMENT,
    name         varchar(100) NOT NULL UNIQUE,
    description  text NULL,
    catId        int(10) NOT NULL,
    price        numeric(6,2) NOT NULL,
    mainPic      varchar(50) NULL,
    PRIMARY KEY (id)
);

CREATE TABLE sections (
    id          int(10) NOT NULL AUTO_INCREMENT,
    name        varchar(50) NOT NULL UNIQUE,
    uri         varchar(40) NOT NULL UNIQUE,
    description varchar(100) NULL,
    maxProducts int(4) NULL,
    PRIMARY KEY (id)
);

CREATE TABLE section_params (
    sectId      int(10) NOT NULL,
    paramId     int(10) NOT NULL AUTO_INCREMENT,
    paramName   varchar(20) NOT NULL UNIQUE,
    PRIMARY KEY (paramId)
);

CREATE TABLE order_items (
    orderId int(10) NOT NULL,
    prodId  int(10) NOT NULL,
    qty     int(3) NOT NULL,
    PRIMARY KEY (orderId, prodId)
);

CREATE TABLE featured_products (
    prodId      int(10) NOT NULL,
    PRIMARY KEY (prodId)
);

CREATE TABLE product_pics (
    prodId  int(10) NOT NULL,
    path    varchar(30) NOT NULL,
    PRIMARY KEY (prodId, path)
);

CREATE TABLE product_discount (
    prodId  int(10) NOT NULL,
    amount  numeric(3,2) NOT NULL,
    PRIMARY KEY (prodId)
);

CREATE TABLE product_specs (
    prodId  int(10) NOT NULL,
    specId  int(10) NOT NULL,
    value   varchar(40) NOT NULL,
    PRIMARY KEY (prodId, specId)
);

CREATE TABLE cart (
    userId  int(10) NOT NULL,
    prodId  int(10) NOT NULL,
    qty     int(3) NOT NULL,
    PRIMARY KEY(userId, prodId)
);


INSERT INTO categories VALUES
    (1, 'Keyboards', 'Ergonomic keyboards', 'keyboards'),
    (2, 'Mice', 'Ergonomic mice', 'mice'),
    (3, 'Accessories', 'Various accessories', 'accessories');

INSERT INTO products VALUES
    (1, 'Ergo Pro Quiet Mac Ergonomic Keyboard', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 1, 330, '/uploads/kb1.jpg'),
    (2, 'Full Size Ergonomic Backlit Hub Keyboard', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 1, 160, '/uploads/kb2.jpg'),
    (3, 'Wireless Sculpt Ergonomic Desktop', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 1, 220, '/uploads/kb3.jpg'),
    (4, 'Truly Ergonomic 227 Mechanical Keyboard', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 1, 300, '/uploads/kb4.jpg'),
    (5, 'Black Goldtouch V2 Adjustable Comfort Keyboard', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 1, 190, '/uploads/kb5.jpg'),
    (6, 'Maltron, Ergonomic, Single Left-Handed Keyboard, USB', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 1, 400, '/uploads/kb6.jpg'),
    (7, 'Fully Adjustable Split-Keyfield Ergonomic RSI Keyboard', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 1, 210, '/uploads/kb7.jpg'),
    (8, 'Maltron, Ergonomic Two-Handed Trackball Keyboard Black USB', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 1, 500, '/uploads/kb8.jpg'),
    (9, 'Wireless super mini keyboard with built-in touchpad', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 1, 90, '/uploads/kb9.jpg'),
    (10, 'Black Left-Handed Keypad Keyboard', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 1, 120, '/uploads/kb10.jpg'),
    (11, 'Evoluent Reduced Reach Right-Hand Keyboard', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 1, 160, '/uploads/kb11.jpg'),
    (12, 'Goldtouch Go!2 Foreign Language Mobile Keyboards', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 1, 250, '/uploads/kb12.jpeg'),
    (13, 'Goldtouch V2 Adjustable Comfort Keyboard ', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 1, 230, '/uploads/kb13.jpg'),

    (14, 'Evoluent Vertical Mouse Bluetooth Right Handed White', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 2, 220, '/uploads/ms1.jpg'),
    (15, 'OrthoMouse Orthopaedic Ergonomic and Adjustable Wireless Laser Mouse', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 2, 250, '/uploads/ms2.jpg'),
    (16, 'Evoluent Vertical C Mouse Right Handed Silver', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 2, 230, '/uploads/ms3.jpg'),
    (17, 'Handshoe Mouse Left Handed Small', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 2, 240, '/uploads/ms4.jpg'),
    (18, 'Handshoe Mouse Left Handed Large', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 2, 260, '/uploads/ms5.jpg'),
    (19, 'Logitech Wireless Trackball M570', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 2, 160, '/uploads/ms6.jpg'),
    (20, 'Contour Mouse, Grey Metal, Left Handed, Large', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 2, 200, '/uploads/ms7.jpg'),
    (21, 'E-Quill-AirO2bic Mouse, Pearl, Left Handed and Clickless Software Bundle', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 2, 290, '/uploads/ms8.jpg'),
    (22, 'Vertical Grip Mouse, Optical, USB', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 2, 100, '/uploads/ms9.jpg'),
    (23, 'Evoluent VerticalMouse 3, Right Handed, Optical, USB', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 2, 150, '/uploads/ms10.jpg');

INSERT INTO product_discount VALUES
    (1, 0.15),
    (4, 0.5),
    (8, 0.2),
    (11, 0.25),

    (16, 0.3),
    (21, 0.15),
    (23, 0.05);

INSERT INTO specs VALUES
    (1, 'Model', 'text', 1, 0),
    (2, 'Brand', 'text', 1, 1),
    (3, 'Color', 'text', 1, 1),
    (4, 'Connection', 'text', 0, 1),
    (5, 'Length', 'text', 0, 0),
    (6, 'Height', 'text', 0, 0),
    (7, 'Weight', 'text', 0, 0),
    (8, 'Hand', 'text', 0, 1),
    (9, 'Layout', 'text', 0, 1);

INSERT INTO spec_cats VALUES
    (1, 1),
    (2, 1),
    (3, 1),
    (4, 1),
    (5, 1),
    (6, 1),
    (7, 1),
    (9, 1),

    (1, 2),
    (2, 2),
    (3, 2),
    (4, 2),
    (5, 2),
    (6, 2),
    (7, 2),
    (8, 2),

    (1, 3),
    (2, 3),
    (3, 3),
    (5, 3),
    (6, 3),
    (7, 3);

INSERT INTO product_specs VALUES
    (1, 1, 'ABC-01'),
    (1, 2, 'Ergo Pro'),
    (1, 3, 'Black'),
    (1, 4, 'Wireless'),
    (1, 9, 'QWERTY'),

    (2, 1, 'CVB-21'),
    (2, 2, 'Perixx'),
    (2, 3, 'Black'),
    (2, 4, 'USB'),
    (2, 9, 'QWERTY'),

    (3, 1, 'ASD-42'),
    (3, 2, 'Microsoft'),
    (3, 3, 'Black'),
    (3, 4, 'Wireless'),
    (3, 9, 'QWERTY'),

    (4, 1, 'SDF-53'),
    (4, 2, 'Truly Ergonomic'),
    (4, 3, 'Black'),
    (4, 4, 'Wireless'),
    (4, 9, 'Dvorak'),

    (5, 1, 'HDF-51'),
    (5, 2, 'Goldtouch'),
    (5, 3, 'Black'),
    (5, 4, 'USB'),
    (5, 9, 'QWERTY'),

    (6, 1, 'HDF-52'),
    (6, 2, 'Microsoft'),
    (6, 3, 'Grey'),
    (6, 4, 'USB'),
    (6, 9, 'QWERTY'),

    (7, 1, 'HDF-53'),
    (7, 2, 'Truly Ergonomic'),
    (7, 3, 'Grey'),
    (7, 4, 'Wireless'),
    (7, 9, 'QWERTY'),

    (8, 1, 'HDF-54'),
    (8, 2, 'Goldtouch'),
    (8, 3, 'Black&White'),
    (8, 4, 'USB'),
    (8, 9, 'QWERTY'),

    (9, 1, 'HDF-55'),
    (9, 2, 'Perixx'),
    (9, 3, 'Black'),
    (9, 4, 'Wireless'),
    (9, 9, 'Dvorak'),


    (14, 1, 'HDF-515'),
    (14, 2, 'Evoluent'),
    (14, 3, 'White'),
    (14, 4, 'Wireless'),
    (14, 8, 'Right'),

    (15, 1, 'HDF-516'),
    (15, 2, 'OrthoMouse'),
    (15, 3, 'Black'),
    (15, 4, 'Wireless'),
    (15, 8, 'Right'),

    (16, 1, 'HDF-517'),
    (16, 2, 'Evoluent'),
    (16, 3, 'Black'),
    (16, 4, 'USB'),
    (16, 8, 'Right'),

    (17, 1, 'HDF-518'),
    (17, 2, 'Handshoe'),
    (17, 3, 'Black'),
    (17, 4, 'Wireless'),
    (17, 8, 'Left'),

    (18, 1, 'HDF-519'),
    (18, 2, 'Handshoe'),
    (18, 3, 'Black'),
    (18, 4, 'USB'),
    (18, 8, 'Left'),

    (19, 1, 'HDF-520'),
    (19, 2, 'Logitech'),
    (19, 3, 'Black'),
    (19, 4, 'Wireless'),
    (19, 8, 'Right');

INSERT INTO sections VALUES
    (1, 'Featured', 'featured', 'Currently featured products', 100),
    (2, 'New Arrivals', 'new', 'Selection of newest products on the market', 10),
    (3, 'Best Sellers', 'best', 'Our shop best-sellers', 10),
    (4, 'On Sale', 'sale', 'Currently on sale', 10);

INSERT INTO section_params VALUES
    (1, 1, 'featured'),
    (2, 2, 'new'),
    (3, 3, 'best'),
    (4, 4, 'sale');

INSERT INTO featured_products VALUES
    (1),
    (2),
    (3),
    (4),
    (5),
    (14),
    (15),
    (16);

-- INSERT INTO order_items VALUES
--     (1, 1, 1),
--     (2, 1, 2),
--     (3, 1, 3),
--     (4, 1, 1),
--     (5, 2, 2),
--     (6, 2, 3),
--     (7, 2, 1),
--     (8, 3, 2),
--     (9, 3, 3),
--     (10, 4, 10);

INSERT INTO order_statuses VALUES
    (1, 'pending'),
    (2, 'confirmed'),
    (3, 'sent'),
    (4, 'delivered'),
    (5, 'finished'),
    (6, 'cancelled');

INSERT INTO deliveries VALUES
    (1, 'China Post', 'Deliver using regular post service. Cheaper than express delivery, but also slower', 10.0),
    (2, 'Express delivery', 'Deliver goods using fast express service. Delivery time is two to three days', 20.0);

INSERT INTO payments VALUES
    (1, 'Union Pay', 'Pay using your debit or credit bank card'),
    (2, 'Alipay', 'Pay using your ALipay account'),
    (3, 'Wechat Payments', 'Pay using your Wechat account');
