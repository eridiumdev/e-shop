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
    name         varchar(20) NOT NULL UNIQUE,
    description  text NULL,
    catId        int(10) NOT NULL,
    price        numeric(6,2) NOT NULL,
    mainPic      varchar(20) NULL,
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
