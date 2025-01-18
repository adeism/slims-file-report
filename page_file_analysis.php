<?php
/**
 * File Access Analysis (Example Plugin)
 * Menampilkan laporan daftar akses file dan jumlah akses berdasarkan per-file dan per-koleksi.
 */

use Zein\Tpl\Tpl;

// Pastikan file ini tidak diakses langsung
defined('INDEX_AUTH') or die('Direct access not allowed!');

// Jika perlu batasi akses IP
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');

// Inisialisasi session admin SLiMS
require SB . 'admin/default/session.inc.php';

// Load kelas simbio_table
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';

// Styles, dipindahkan ke sini untuk keteraturan // CHANGE
echo '<style>
        .plugin-section-title {
            margin-top: 25px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }
        .infoBox {
            margin-bottom: 20px;
        }
        .card-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .card {
            flex: 1;
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .card-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .card-value {
            font-size: 1.5em;
        }
    </style>';

/**
 * 1. Jumlah Akses Berdasarkan Per-Koleksi (biblio)
 * ------------------------------------------------------
 */
function showJumlahAksesPerKoleksi($dbs) {
    echo '<h3 class="plugin-section-title">' . __('Number of Accesses per Collection') . '</h3>'; // CHANGE

    $sql = "
      SELECT
        b.biblio_id AS BiblioID,
        b.title AS JudulKoleksi,
        COUNT(fr.file_id) AS TotalAkses
      FROM files_read AS fr
        LEFT JOIN files AS f ON f.file_id = fr.file_id
        LEFT JOIN biblio_attachment AS ba ON f.file_id = ba.file_id
        LEFT JOIN biblio AS b ON b.biblio_id = ba.biblio_id
      GROUP BY b.biblio_id, b.title
      ORDER BY TotalAkses DESC
    ";

    $q = $dbs->query($sql);
    if ($q->num_rows > 0) { // CHANGE: Pemeriksaan kondisi lebih sederhana
        $table = new simbio_table();
        $table->table_attr = 'class="s-table table table-bordered table-striped w-100">'; // CHANGE: Menambahkan class table-striped untuk tampilan lebih baik
        $table->setHeader(array(__('Collection Title'), __('Total Accesses'))); // CHANGE
        $table->table_header_attr = 'class="dataListHeader"';

        while ($row = $q->fetch_assoc()) {
            $table->appendTableRow(array(
                $row['JudulKoleksi'],
                $row['TotalAkses']
            ));
        }
        echo '<div class="table-responsive"> ' . $table->printTable() . '</div>';
    } else {
        echo '<p class="text-muted">' . __('No data available.') . '</p>'; // CHANGE
    }
}

/**
 * 2. Jumlah Akses Berdasarkan Per-File
 * ------------------------------------------------------
 */
function showJumlahAksesPerFile($dbs) {
    echo '<h3 class="plugin-section-title">' . __('Number of Accesses per File') . '</h3>'; // CHANGE

    $sql = "
      SELECT
        f.file_title AS JudulFile,
        f.file_name AS NamaFile,
        f.mime_type AS MimeType,
        MAX(ba.access_type) AS TipeAkses,
        MAX(b.biblio_id) AS BiblioID,
        MAX(b.title) AS JudulKoleksi,
        COUNT(fr.file_id) AS TotalAkses
      FROM files_read AS fr
        LEFT JOIN files AS f ON f.file_id = fr.file_id
        LEFT JOIN biblio_attachment AS ba ON f.file_id = ba.file_id
        LEFT JOIN biblio AS b ON b.biblio_id = ba.biblio_id
      GROUP BY f.file_id, f.file_title, f.file_name, f.mime_type
      ORDER BY TotalAkses DESC
    ";

    $q = $dbs->query($sql);
    if ($q->num_rows > 0) { // CHANGE: Pemeriksaan kondisi lebih sederhana
        $table = new simbio_table();
        $table->table_attr = 'class="s-table table table-bordered table-striped w-100">'; // CHANGE: Menambahkan class table-striped
        $table->setHeader(array(
            __('File Title'), __('File Name'), __('MIME Type'), // CHANGE
            __('Access Type'), __('Collection Title'), __('Total Accesses') // CHANGE
        ));
        $table->table_header_attr = 'class="dataListHeader"';

        while ($row = $q->fetch_assoc()) {
            $table->appendTableRow(array(
                $row['JudulFile'],
                $row['NamaFile'],
                $row['MimeType'],
                $row['TipeAkses'],
                $row['JudulKoleksi'],
                $row['TotalAkses']
            ));
        }
        echo '<div class="table-responsive">' . $table->printTable() . '</div>';
    } else {
        echo '<p class="text-muted">' . __('No data available.') . '</p>'; // CHANGE
    }
}

/**
 * 3. Daftar Akses File
 * ------------------------------------------------------
 * Menampilkan data dari tabel files_read, files, biblio_attachment, dan biblio
 */
function showDaftarAksesFile($dbs) {
    echo '<h3 class="plugin-section-title">' . __('File Access List') . '</h3>'; // CHANGE

    $sql = "
      SELECT
        fr.date_read AS WaktuAkses,
        fr.client_ip AS IP,
        f.file_title AS JudulFile,
        f.file_name AS NamaFile,
        f.mime_type AS MimeType,
        ba.access_type AS TipeAkses,
        b.title AS JudulKoleksi
      FROM files_read AS fr
        LEFT JOIN files AS f ON f.file_id = fr.file_id
        LEFT JOIN biblio_attachment AS ba ON f.file_id = ba.file_id
        LEFT JOIN biblio AS b ON b.biblio_id = ba.biblio_id
      ORDER BY fr.date_read DESC
    ";

    $q = $dbs->query($sql);
    if ($q->num_rows > 0) { // CHANGE: Pemeriksaan kondisi lebih sederhana
        $table = new simbio_table();
        $table->table_attr = 'class="s-table table table-bordered table-striped w-100">'; // CHANGE: Menambahkan class table-striped
        $table->setHeader(array(
            __('Access Time'), __('IP Address'), __('File Title'), __('File Name'), // CHANGE
            __('MIME Type'), __('Access Type'), __('Collection Title') // CHANGE
        ));
        $table->table_header_attr = 'class="dataListHeader"';

        while ($row = $q->fetch_assoc()) {
            $table->appendTableRow(array(
                strftime('%Y-%m-%d %H:%M:%S', strtotime($row['WaktuAkses'])), // CHANGE: Menampilkan format waktu yang lebih baik
                $row['IP'],
                $row['JudulFile'],
                $row['NamaFile'],
                $row['MimeType'],
                $row['TipeAkses'],
                $row['JudulKoleksi']
            ));
        }
        echo '<div class="table-responsive">' . $table->printTable() . '</div>';
    } else {
        echo '<p class="text-muted">' . __('No data available.') . '</p>'; // CHANGE
    }
}

// ---------------------------
// Bagian utama plugin
// ---------------------------
$page_title = __('File Access Analysis'); // CHANGE

// Query untuk mendapatkan Bulan Paling Aktif // CHANGE
$sql_bulan_aktif = "
    SELECT
        DATE_FORMAT(date_read, '%Y-%m') AS bulan,
        COUNT(*) AS total
    FROM files_read
    GROUP BY bulan
    ORDER BY total DESC
    LIMIT 1
";
$bulan_aktif_result = $dbs->query($sql_bulan_aktif);
$bulan_aktif = ($bulan_aktif_result->num_rows > 0) ? $bulan_aktif_result->fetch_assoc()['bulan'] : __('N/A'); // CHANGE

// Query untuk mendapatkan Total Akses // CHANGE
$sql_total_akses = "SELECT COUNT(*) AS total FROM files_read";
$total_akses_result = $dbs->query($sql_total_akses);
$total_akses = ($total_akses_result->num_rows > 0) ? $total_akses_result->fetch_assoc()['total'] : 0; // CHANGE

// Query untuk mendapatkan Total File List // CHANGE
$sql_total_file = "SELECT COUNT(file_id) AS total FROM files";
$total_file_result = $dbs->query($sql_total_file);
$total_file = ($total_file_result->num_rows > 0) ? $total_file_result->fetch_assoc()['total'] : 0; // CHANGE

// -----------------------------------------------------
// Tampilkan di layar
// -----------------------------------------------------
?>
<div class="menuBox">
    <div class="menuBoxInner statisticIcon">
        <div class="per_title">
            <h2><?php echo $page_title; ?></h2>
        </div>
        <!-- info box removed -->
    </div>
</div>

<div class="container">
    <div class="card-container">  <!-- // CHANGE: Container untuk card -->
        <div class="card">  <!-- // CHANGE: Card Bulan Paling Aktif -->
            <div class="card-title"><?php echo __('Most Active Month'); ?></div>  <!-- // CHANGE -->
            <div class="card-value"><?php echo $bulan_aktif; ?></div>  <!-- // CHANGE -->
        </div>
        <div class="card">  <!-- // CHANGE: Card Total Akses -->
            <div class="card-title"><?php echo __('Total Accesses'); ?></div>  <!-- // CHANGE -->
            <div class="card-value"><?php echo $total_akses; ?></div>  <!-- // CHANGE -->
        </div>
        <div class="card">  <!-- // CHANGE: Card Total File List -->
            <div class="card-title"><?php echo __('Total File List'); ?></div>  <!-- // CHANGE -->
            <div class="card-value"><?php echo $total_file; ?></div>  <!-- // CHANGE -->
        </div>
    </div>
    <?php
    // Panggil semua fungsi laporan sesuai urutan yang diinginkan
    showJumlahAksesPerKoleksi($dbs);
    showJumlahAksesPerFile($dbs);
    showDaftarAksesFile($dbs);
    ?>
</div>