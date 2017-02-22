<?php
// Define queries inside of the reports array.
// !! IMPORTANT: Make sure queries do not have a semicolon ';' at the end so the pagination will work.
$reports = array();

$reports['default'] = "SELECT username,full_name,email,date_created,active,login_date from users";
