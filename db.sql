USE mysql;
DELETE FROM user WHERE password = '' AND user != 'root';

DROP DATABASE IF EXISTS eshop;
CREATE DATABASE eshop DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE USER IF NOT EXISTS admin@localhost identified by '123';
GRANT SELECT, INSERT, UPDATE, DELETE, ALTER
ON eshop.* to admin@localhost identified by '123';

USE eshop;

CREATE TABLE users (
    id            int(10) NOT NULL AUTO_INCREMENT,
    username      varchar(20) NULL UNIQUE,
    email         varchar(30) NULL UNIQUE,
    password      varchar(64) NULL,
    type          varchar(5) NOT NULL DEFAULT 'user',
    registeredAt  datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

CREATE TABLE shipping (
    userId  int(10) NOT NULL AUTO_INCREMENT,
    name    varchar(40) NOT NULL,
    phone   varchar(15) NOT NULL,
    address varchar(100) NOT NULL,
    PRIMARY KEY (userId)
);

CREATE TABLE orders (
    id          int(10) NOT NULL AUTO_INCREMENT,
    userId      int(10) NOT NULL,
    date        datetime DEFAULT CURRENT_TIMESTAMP,
    status      varchar(10) NOT NULL,
    deliveryId  int(10) NOT NULL,
    paymentId   int(10) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE deliveries (
    id          int(10) NOT NULL AUTO_INCREMENT,
    name        varchar(20) NOT NULL UNIQUE,
    description varchar(100) NOT NULL,
    price       numeric(4,2) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE payments (
    id          int(10) NOT NULL AUTO_INCREMENT,
    name        varchar(20) NOT NULL UNIQUE,
    description varchar(100) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE categories (
    id          int(10) NOT NULL AUTO_INCREMENT,
    name        varchar(20) NOT NULL UNIQUE,
    description varchar(100) NULL,
    uri         varchar(20) NOT NULL UNIQUE,
    PRIMARY KEY (id)
);

CREATE TABLE specs (
    id          int(10) NOT NULL AUTO_INCREMENT,
    name        varchar(20) NOT NULL UNIQUE,
    type        varchar(15) NOT NULL,
    isRequired  boolean NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE spec_cats (
    specId  int(10) NOT NULL,
    catId   int(10) NOT NULL,
    PRIMARY KEY (specId, catId)
);

CREATE TABLE products (
    id           int(10) NOT NULL AUTO_INCREMENT,
    name         varchar(50) NOT NULL UNIQUE,
    description  text NULL,
    catId        int(10) NOT NULL,
    price        numeric(6,2) NOT NULL,
    mainPic      varchar(50) NULL,
    PRIMARY KEY (id)
);

CREATE TABLE sections (
    id          int(10) NOT NULL AUTO_INCREMENT,
    name        varchar(20) NOT NULL UNIQUE,
    description varchar(100) NULL,
    maxProducts int(2) NULL,
    paramId     int(10) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE section_params (
    paramId int(10) NOT NULL AUTO_INCREMENT,
    name    varchar(10) NOT NULL UNIQUE,
    PRIMARY KEY (paramId)
);

CREATE TABLE order_items (
    orderId int(10) NOT NULL,
    prodId  int(10) NOT NULL,
    PRIMARY KEY (orderId, prodId)
);

CREATE TABLE featured_products (
    sectionId   int(10) NOT NULL,
    prodId      int(10) NOT NULL,
    PRIMARY KEY (sectionId, prodId)
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
    PRIMARY KEY (prodId, specId)
);


INSERT INTO categories VALUES
    (1, 'Keyboards', 'Ergonomic keyboards', 'keyboards'),
    (2, 'Mice', 'Ergonomic mice', 'mice'),
    (3, 'Accessories', 'Various accessories', 'accessories');

INSERT INTO products VALUES
    (1, 'Ergo Pro Quiet Mac Ergonomic Keyboard', '', 1, 330, '/uploads/kb/kb1.jpg'),
    (2, 'Full Size Ergonomic Backlit Hub Keyboard', '', 1, 160, '/uploads/kb/kb2.jpg'),
    (3, 'Wireless Sculpt Ergonomic Desktop', '', 1, 220, '/uploads/kb/kb3.jpg'),
    (4, 'Truly Ergonomic 227 Mechanical Keyboard', '', 1, 300, '/uploads/kb/kb4.jpg'),
    (5, 'Black Goldtouch V2 Adjustable Comfort Keyboard', '', 1, 190, '/uploads/kb/kb5.jpg'),
    (6, 'Maltron, Ergonomic, Single Left-Handed Keyboard, USB', '', 1, 400, '/uploads/kb/kb6.jpg'),
    (7, 'Fully Adjustable Split-Keyfield Ergonomic RSI Keyboard<', '', 1, 210, '/uploads/kb/kb7.jpg'),
    (8, 'Maltron, Ergonomic Two-Handed Trackball Keyboard Black USB', '', 1, 500, '/uploads/kb/kb8.jpg'),
    (9, 'Wireless super mini keyboard with built-in touchpad', '', 1, 90, '/uploads/kb/kb9.jpg'),
    (10, 'Black Left-Handed Keypad Keyboard', '', 1, 120, '/uploads/kb/kb10.jpg'),
    (11, 'Evoluent Reduced Reach Right-Hand Keyboard', '', 1, 160, '/uploads/kb/kb11.jpg'),
    (12, 'Goldtouch Go!2 Foreign Language Mobile Keyboards', '', 1, 250, '/uploads/kb/kb12.jpeg'),
    (13, 'Goldtouch V2 Adjustable Comfort Keyboard ', '', 1, 230, '/uploads/kb/kb13.jpg'),

    (14, 'Evoluent Vertical Mouse Bluetooth Right Handed White', '', 2, 220, '/uploads/ms/ms1.jpg'),
    (15, 'OrthoMouse Orthopaedic Ergonomic and Adjustable Wireless Laser Mouse', '', 2, 250, '/uploads/ms/ms2.jpg'),
    (16, 'Evoluent Vertical C Mouse Right Handed Silver', '', 2, 230, '/uploads/ms/ms3.jpg'),
    (17, 'Handshoe Mouse Left Handed Small', '', 2, 240, '/uploads/ms/ms4.jpg'),
    (18, 'Handshoe Mouse Left Handed Large', '', 2, 260, '/uploads/ms/ms5.jpg'),
    (19, 'Logitech Wireless Trackball M570', '', 2, 160, '/uploads/ms/ms6.jpg'),
    (20, 'Contour Mouse, Grey Metal, Left Handed, Large', '', 2, 200, '/uploads/ms/ms7.jpg'),
    (21, 'E-Quill-AirO2bic Mouse, Pearl, Left Handed and Clickless Software Bundle', '', 2, 290, '/uploads/ms/ms8.jpg'),
    (22, 'Vertical Grip Mouse, Optical, USB', '', 2, 100, '/uploads/ms/ms9.jpg'),
    (23, 'Evoluent VerticalMouse 3, Right Handed, Optical, USB', '', 2, 150, '/uploads/ms/ms10.jpg');

INSERT INTO product_discount VALUES
    (1, 0.15),
    (4, 0.5),
    (8, 0.2),
    (11, 0.25),

    (16, 0.3),
    (21, 0.15),
    (23, 0.05);
