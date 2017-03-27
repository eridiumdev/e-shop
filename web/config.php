<?php
date_default_timezone_set("Asia/Shanghai");

/**
 * TESTING FLAGS
 *
 * Access rights (privileges) - works on all pages :
 * -1 - default (need to login)
 *  0 - guest user
 *  1 - signed user
 *  2 - admin
 */
const ACCESS_RIGHTS = -1;

// Default password when creating users from a batch file
const BATCH_USER_PASSWORD = '123';

// Debug global helper-shortcuts
// print $obj
function p($obj)
{
    print_r($obj);
}

// print $obj and exit
function pe($obj)
{
    print_r($obj);
    exit;
}

// print $obj and new line
function pn($obj)
{
    print_r($obj);
    echo "\n";
}

// print new line
function n()
{
    echo "\n";
}
