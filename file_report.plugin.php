<?php
/**
 * Plugin Name: File Report
 * Plugin URI: https://github.com/adeism
 * Description: Displays file reports on the SLiMS admin page.
 * Version: 1.0.0
 * Author: Ade Ismail Siregar, inspired by Hendro Wicaksono's SQL code from the SLiMS WhatsApp group
 */

use SLiMS\Plugins;

// Register pages in the admin menu
$plugin = Plugins::getInstance();
$plugin->registerMenu('reporting', 'File Report', __DIR__ . '/page_file_report.php');
$plugin->registerMenu('reporting', 'UIANA Report', __DIR__ . '/uiana_report.php');
?>
