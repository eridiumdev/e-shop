<?php
date_default_timezone_set("Europe/Moscow");

/**
 * TESTING FLAGS
 *
 * Access rights (privileges) :
 * -1 - default (need to login)
 *  0 - no privileges on all pages
 *  1 - signed in user privileges on all pages
 *  2 - admin privileges on all pages
 */
const ACCESS_RIGHTS = -1;

// Default password when creating users from a batch file
const BATCH_USER_PASSWORD = '123';

// Uploads
const YML_DIRECTORY   = '/yml/';
const PIC_DIRECTORY  = '/uploads/';

// Database population data (inside yml)
const DB_DATA = '/yml/products.yml';

// Chinese characters pattern for regular expression
const PATTERN_CHINESE = "/[\p{Han}]/simu";

// Default status for submitted orders (1 = pending)
const DEFAULT_STATUS = 1;

// Global debug helper-shortcuts
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
