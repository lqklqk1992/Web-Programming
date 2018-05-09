# Web-Programming
a functional shopping cart website using paypal

PHASE 1: LOOK AND FEEL 
A designer has provided you with a draft layout as follows, which outlines the fundamental features of a shopping
website. In this phase, you will create a mock-up by hardcoding the website with dummy categories and products.

1. HTML: Make good use of semantic HTML throughout the whole assign.
o <header>, <nav>, <footer>, <article>, <section>, <ul>, <li>
2. CSS: Clean separation of HTML, CSS and JS code and files throughout the whole assign. 
o No inline CSS and JS are allowed
o No HTML for styling use, e.g. <center>, align="center", etc
o Tolerance: < 5 exceptions
3. Main page demonstrates the use of “CSS tableless” product list
o Each product has at least its own thumbnail, name, price and addToCart button
o When the thumbnail or name is clicked, redirect to the corresponding product page
4. Main page demonstrates the use of “CSS hover” shopping list
o When displayed, it will cover any elements behind
o Input boxes are used for inputting quantity of each selected product
o A checkout button is used to submit the list to PayPal
o The shopping list is displayed in both main and product pages
5. Product page provides product details 
o To show a full-size or bigger image, name, description, price, and addToCart button
6. Both main and product pages should include a hierarchical navigation menu 
o e.g. Home or Home > Category1 or Home > Category1 > Product1
o They are hyperlinks that can redirect users to an upper level of the hierarchy
  
PHASE 2A: SECURE SERVER SETUP
In this phase, you are required to setup a secure server for later development. Some guidance will be given in tutorial.
1. Instantiate a free Amazon EC2 Virtual Machine 
o Details of the Free Usage Tier: http://aws.amazon.com/free/
o With a Linux distribution, install only Apache, PHP and SQLite (or MySQL)
 To minimize attack surfaces, always install only what you need
2. Apply necessary security configurations
o Apply proper firewall settings at Amazon: block all ports but 22, 80 and 443 only
o Apply proper updates for the server software packages in a regular manner
o Hide the versions of OS, Apache and PHP in HTTP respons e headers
3
o Do not display any PHP warnings and errors to the end users
o Disable directory index in Apache
3. Configure the VM so that your website is accessible at http://www.shop1[01-97].ierg4210.org 
o Apply for an elastic public IP, and ALWAYS associate it with the instantiated VM

PHASE 2B: DATA PRESENTATION & MANAGEMENT 
In this phase, you will implement the core functions of the website with mainly PHP and SQL.
1. SQL: Create a database with the following structures 
o A table for categories
 Required columns: catid (primary key), name
 Data: at least 2 categories of your choice
o A table for products
 Required columns: pid (primary key), catid, name, price, description
 Data: at least 2 products for each category
2. HTML, PHP & SQL: Create an admin panel
o Design several HTML forms to manage* productsin DB
 Dropdown menu to select catid according to its name
 Input fields for inputting name, price
 Textarea for inputting description
 ^ File field for uploading an image (format: jpg/gif/png, size: <=10MB)
o Design several HTML forms to manage* categoriesin DB 
* In terms of manage, it includes the capabilities of insert, update and delete
^ For the file uploaded, store it with its name based on the unique lastInsertId()
3. HTML, PHP, SQL: Update the main page created in Phase 1
o Populate the category list from DB
o Based on the category picked by user, populate the corresponding product list from DB
 The catid=[x] is reflected as a query string in the URL
4. HTML, PHP & SQL: Update the product details page created in Phase 1 
o Display the details of a product according to its DB record

PHASE 3: AJAX SHOPPING LIST
In this phase, you will implement the shopping list which allows users to shop around your products.This phase is
designed to let you practise Javascript programming.
1. JS: Dynamically update#
the shopping list (to be covered in tutorial)
o When the addToCart button of a product is clicked, add it to the shopping list 
 Adding the same product twice will display only one row of record
o Once a product is added,
 Users are allowed to update its quantity and delete it with a number input, or 
 two buttons for increment and decrement
 Store its pid and quantity in the browser’s localStorage
 Get the name and price over AJAX (with pid as input) 
 Calculate and display the total amount at the client-side
o Once the page is reloaded, the shopping list is restored 
 Page reloads when users browse another category or visit the product detail page
 Populate and retrieve the stored products from the localStorage
# The whole process of shopping list management must be done without a page load

PHASE 4: SECURING THE WEBSITE 
In this phase, you will protect your website against many popular web application security threats.
1. No XSS Injection and Parameter Tampering Vulnerabilities in the whole website
o [UI Enhancement Only] Proper and vigorous client-side input restrictions for all forms 
o Proper and vigorous server-side input sanitizations and validations for all forms 
o Proper and vigorous context-dependent output sanitizations 
2. No SQL Injection Vulnerabilities in the whole website 
o Apply parameterized SQL statements with the PDO library
3. No CSRF Vulnerabilities in the whole website 
o Apply and validate secret nonces for every form
o ALL forms must defend against Traditional and Login CSRF
4. Authentication for Admin Panel
o Create a user table (or a separate DB with only one user table)
 Required columns: userid (primary key), email, password
 Data: at least 2 users of your choice, 1 admin
 Security: Passwords must be properly salted and hashed before storage
o Build a login page login.php that requests for email and password
 Upon validated and authenticated, redirect the user to the admin panel or main page
 Indicate user name in your website
 Otherwise, prompt for errors (i.e. either email or password is incorrect)
o Maintain an authentication token using Cookies (with httpOnly)
 Cookie name: auth; value: a hashed token; property: httpOnly
 Cookies persist after browser restart (i.e. 0 < expires < 3 days) 
 No Session Fixation Vulnerabilities (rotate session id upon successful login) 
o Validate the authentication token before revealing and executing admin features 
 If successful, let admin users access the admin panel and execute admin features
 Otherwise (e.g. empty or tampered token), redirect back to the login page or main page
 Security: both admin.html and admin-process.php must validate the auth. token
o PHP & SQL: Provide a logout feature that clears the authentication token 
5. All generated session IDs and nonces are not guessable throughout the whole assign. 
o e.g., the login token must not reveal the original password in plaintext
o e.g., the CSRF nonce when applied in a hidden field must be random
6. Apply SSL certificate for secure.
o Certificate Application 
 When generating a CSR, use CUHK as Organization Name
 Apply a 90-day free certificate at https://www.ssl.com/certificates/free/buy/
 Reminder: the application process can take more than a day, so apply early!!
o Certificate Installation
 Install the issued certificate and apply security configurations in Apache 
 Apply strong algorithms and secure cipher suites
 Host admin panel at https://.../admin.php 
 In the .htaccess, redirect users to the above if come from:
http://[secure...] or http://[www...]/admin.php

PHASE 5: SECURE CHECKOUT FLOW 
This is a tough phase, yet the most critical phase to escalate the professional level of your website to the next level.
(You’ll likely be offered a job if you can demonstrate such a level of web programming skills) The implementation has
already been outlined as below. Be prepared to spend substantial amount of time in debugging.
1. Sign up at https://developer.paypal.com/ and create two test accounts: 
o A merchant account - after logging in to the Sandbox Test Site, modify necessary settings in the Selling
Preferences under Profile
o A buyer account – use it to pay for purchased items in your shopping portal
2. Enclose your shopping cart with a <form> element 
o Use the Cart Upload Command of PayPal Website Payment Standard (cmd=_cart&upload=1)
o Insert additional hidden fields that are required by PayPal (Read the first reference)
 business, charset, currency_code, item_name_X, item_number_X, quantityX
 invoice and custom
o Create a checkout button that submits the form
3. When the checkout button is clicked: 
o Pass ONLY the pid and quantity of every individual product to your server using AJAX and cancel the
default form submission
o Server generates a digest that is composed of at least:
 Currency
 Merchant’s email address
 A random salt
 The pid and quantity of each selected product (Is quantity positive number?)
 The current price of each selected product gathered from DB
 The total price of all selected products
Hint: separate them with a delimiter before passing to a hash function
o Server stores allthe items to generate the digest into a new database table called orders
 The user should be logged in to purchase, store username with order in DB
o Pass the lastInsertId() and the generated digest back to the client by putting them into the hidden fields
of invoice and custom respectively
o Clear the shopping cart at the client-side
o Submit the form now to PayPal using programmatic form submission
4. Setup a Instant Payment Notification (IPN) page to get notified once a payment is complet ed
o Validate the authenticity of data by verifying that it is indeed sent from PayPal 
 Your IPN receiver page is served over HTTPS (using the SSL cert)
 When contacting PayPal for message authenticity check, use SSL and port 443
o Check that txn_id has not been previously processed and txn_type is cart 
o Regenerate a digest with the data provided by PayPal (same order and algorithm) 
o Validate the digest against the one stored in the database table orders 
 If validated, the integrity of the hashed fields is assured
 Save the txn_id and product list (pid, quantity and price) into DB
Debugging Hint: use error_log(print_r($_POST,true)) to print out the parameters passed by PayPal
5. After the buyer has finished paying with PayPal, auto redirect the buyer back to your shop
6. Display the DB orders table in admin panel: product list, payment status…etc.(Phase 6 Item 4)
