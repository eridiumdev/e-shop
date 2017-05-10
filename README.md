# ErgoStore

This is a graduation thesis project @ East China Normal University, Shanghai 2017.

Author: **Anton Starkov** (chinese name: 安东)

## Overview

Application represents an e-commerce platform with most basic functionalities of a modern e-shop, such as product catalog, products and orders management, privileges and authentication control mechanisms, etc.

System is built based on Symfony HTTP-foundation component, using mostly pure PHP + MySQL, frontend built using Bootstrap + Twig templating, as well as custom CSS and jQuery code. The logic is structured around MVC principle, and also Front Controller and Router concepts.

For more information about the project, see thesis abstract (ABSTRACT.md)

## Installation

1. Install and configure Apache server environment, e.g. XAMPP (https://www.apachefriends.org/download.html). Note: PHP 7.1 is required.

2. Put project files inside Apache's **htdocs** directory.

3. Configure document root to point to project's **web** directory (inside Apache httpd.conf file, find and correct DocumentRoot line).

4. Import MySQL database using **db.sql** file in this project root (e.g. using XAMPP shell input `mysql -u root -p < path/to/db.sql` press enter and input your root password, which by default is blank).

5. Now you should be able to access the application via your browser at localhost.
