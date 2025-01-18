<?php
/**
 * Plugin Name: File Access Analysis
 * Plugin URI: https://example.com/plugin
 * Description: Menampilkan analisis akses file dengan grafik dan tabel di halaman admin SLiMS.
 * Version: 1.0.0
 * Author: Pengembang Plugin
 */

use SLiMS\Plugins;

// Register halaman di menu admin
$plugin = Plugins::getInstance();
$plugin->registerMenu('system', 'File Access Analysis', __DIR__ . '/page_file_analysis.php');
?>
