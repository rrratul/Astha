<?php

session_start();
echo "<h2>Session Debug Information</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
echo "<p>Current URL: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Script Name: " . $_SERVER['SCRIPT_NAME'] . "</p>";
?>