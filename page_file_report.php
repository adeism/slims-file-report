<?php
/**
 * File Report by Ade Ismail Siregar github.com/adeism
 * Displays reports on file access counts per file and collection.
 */

// Prevent direct access
defined('INDEX_AUTH') or die('Direct access not allowed!');

// IP access restriction
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');

// Initialize admin session
require SB . 'admin/default/session.inc.php';

/**
 * Number of Accesses per Collection.
 */
function showJumlahAksesPerKoleksi($dbs) {
    echo '<h3 class="mb-3 border-bottom pb-2">' . __('Number of Accesses per Collection') . '</h3>';

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
    if ($q->num_rows > 0) {
        echo '<div class="table-responsive">';
        echo '<table class="table table-hover table-bordered w-100">'; // Using table-hover
        echo '<thead class="thead-light"><tr><th>' . __('Collection Title') . '</th><th>' . __('Total Accesses') . '</th></tr></thead>';
        echo '<tbody>';
        while ($row = $q->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['JudulKoleksi'] . '</td>';
            echo '<td>' . $row['TotalAkses'] . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<p class="text-muted">' . __('No data available.') . '</p>';
    }
}


/**
 * Number of Accesses per File.
 */
function showJumlahAksesPerFile($dbs) {
    echo '<h3 class="mb-3 border-bottom pb-2">' . __('Number of Accesses per File') . '</h3>';

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
    if ($q->num_rows > 0) {
          echo '<div class="table-responsive">';
          echo '<table class="table table-hover table-bordered w-100">';  // Using table-hover
          echo '<thead class="thead-light"><tr><th>' . __('File Title') . '</th><th>' . __('File Name') . '</th><th>' . __('MIME Type') . '</th><th>' . __('Access Type') . '</th><th>' . __('Collection Title') . '</th><th>' . __('Total Accesses') . '</th></tr></thead>';
          echo '<tbody>';
          while ($row = $q->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['JudulFile'] . '</td>';
            echo '<td>' . $row['NamaFile'] . '</td>';
            echo '<td>' . $row['MimeType'] . '</td>';
            echo '<td>' . $row['TipeAkses'] . '</td>';
            echo '<td>' . $row['JudulKoleksi'] . '</td>';
            echo '<td>' . $row['TotalAkses'] . '</td>';
            echo '</tr>';
        }
          echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<p class="text-muted">' . __('No data available.') . '</p>';
    }
}


/**
 * File Access List.
 * Displays data from files_read, files, biblio_attachment, and biblio tables.
 */
function showDaftarAksesFile($dbs) {
    echo '<h3 class="mb-3 border-bottom pb-2">' . __('File Access List') . '</h3>';

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
        LEFT JOIN biblio_attachment AS ba ON ba.file_id = f.file_id
        LEFT JOIN biblio AS b ON b.biblio_id = ba.biblio_id
      ORDER BY fr.date_read DESC
    ";

    $q = $dbs->query($sql);
    if ($q->num_rows > 0) {
        echo '<div class="table-responsive">';
         echo '<table class="table table-hover table-bordered w-100">';  // Using table-hover
         echo '<thead class="thead-light"><tr><th>' . __('Access Time') . '</th><th>' . __('IP Address') . '</th><th>' . __('File Title') . '</th><th>' . __('File Name') . '</th><th>' . __('MIME Type') . '</th><th>' . __('Access Type') . '</th><th>' . __('Collection Title') . '</th></tr></thead>';
         echo '<tbody>';
        while ($row = $q->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . strftime('%Y-%m-%d %H:%M:%S', strtotime($row['WaktuAkses'])) . '</td>';
            echo '<td>' . $row['IP'] . '</td>';
            echo '<td>' . $row['JudulFile'] . '</td>';
            echo '<td>' . $row['NamaFile'] . '</td>';
            echo '<td>' . $row['MimeType'] . '</td>';
            echo '<td>' . $row['TipeAkses'] . '</td>';
            echo '<td>' . $row['JudulKoleksi'] . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';

    } else {
        echo '<p class="text-muted">' . __('No data available.') . '</p>';
    }
}


// Main plugin execution
$page_title = __('File Report');

// Query for most active month
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
$bulan_aktif = ($bulan_aktif_result->num_rows > 0) ? $bulan_aktif_result->fetch_assoc()['bulan'] : __('N/A');

// Query for total accesses
$sql_total_akses = "SELECT COUNT(*) AS total FROM files_read";
$total_akses_result = $dbs->query($sql_total_akses);
$total_akses = ($total_akses_result->num_rows > 0) ? $total_akses_result->fetch_assoc()['total'] : 0;

// Query for total file list
$sql_total_file = "SELECT COUNT(file_id) AS total FROM files";
$total_file_result = $dbs->query($sql_total_file);
$total_file = ($total_file_result->num_rows > 0) ? $total_file_result->fetch_assoc()['total'] : 0;

// Display on screen
?>
<div class="bg-light p-3 mb-4">
    <div class="menuBoxInner statisticIcon">
        <div class="per_title">
            <h2><?php echo $page_title; ?></h2>
        </div>
        <!-- info box removed -->
    </div>
</div>

<div class="container">
    <div class="row row-cols-1 row-cols-md-3 g-3 mb-3">
        <div class="col">
            <div class="card bg-light shadow-sm h-100">
                <div class="card-body p-3">
                    <h5 class="card-title"><?php echo __('Most Active Month'); ?></h5>
                    <p class="card-text fs-5"><?php echo $bulan_aktif; ?></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card bg-light shadow-sm h-100">
                <div class="card-body p-3">
                    <h5 class="card-title"><?php echo __('Total Accesses'); ?></h5>
                    <p class="card-text fs-5"><?php echo $total_akses; ?></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card bg-light shadow-sm h-100">
                <div class="card-body p-3">
                    <h5 class="card-title"><?php echo __('Total File List'); ?></h5>
                    <p class="card-text fs-5"><?php echo $total_file; ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php
    // Display all report functions
    showJumlahAksesPerKoleksi($dbs);
    showJumlahAksesPerFile($dbs);
    showDaftarAksesFile($dbs);
    ?>
</div>
