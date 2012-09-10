<?php
// Include the test framework
$wp_load_path = $_SERVER['DOCUMENT_ROOT'] . '/';
if (!defined('ABSPATH')) require_once($wp_load_path.'wp-config-test.php');
if (!class_exists('Enhance')) require_once('EnhanceTestFramework.php');
global $bootstrapped;
$bootstrapped = 'true';
$path = isset($_GET['abspath']) ? $_GET['abspath'] : '.';
if (isset($_GET['relpath'])) $path = dirname(__FILE__).'/'.$_GET['relpath'];
// Find the tests - '.' is the current folder
\Enhance\Core::discoverTests($path, true);
// Run the tests
\Enhance\Core::runTests();