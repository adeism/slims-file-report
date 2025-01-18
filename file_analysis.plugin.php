<?php
/**
 * Plugin Name: File Access Analysis
 * Plugin URI: -
 * Description: Menampilkan analisis akses file dengan grafik dan tabel di halaman admin SLiMS.
 * Version: 0.1
 * Author: Ade Ismail Siregar
 * inspired by SQL from Hendro Wicaksono in SLiMS community WhatsApp 
 */

use SLiMS\Plugins;

// Register halaman di menu admin
$plugin = Plugins::getInstance();
$plugin->registerMenu('system', 'File Access Analysis', __DIR__ . '/page_file_analysis.php');
?>
