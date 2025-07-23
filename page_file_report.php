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

// Load simbio_table class and simbio_paging class
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';

// Define current page and records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20; // You can adjust the number of records per page

/**
 * Number of Accesses per Collection with pagination.
 * Displays Biblio Title and Total Access Count.
 */
function showAccessCountPerCollection($dbs, $page, $perPage) {
    echo '<h3 class="mb-3 border-bottom pb-2">' . __('Access Count per Collection') . '</h3>';

    $sql = "
      SELECT
        SQL_CALC_FOUND_ROWS
        b.biblio_id AS BiblioID,
        b.title AS collection_title, -- Database aligned column name
        COUNT(fr.file_id) AS total_accesses -- Database aligned column name
      FROM files_read AS fr
        LEFT JOIN files AS f ON f.file_id = fr.file_id
        LEFT JOIN biblio_attachment AS ba ON f.file_id = ba.file_id
        LEFT JOIN biblio AS b ON b.biblio_id = ba.biblio_id
      GROUP BY b.biblio_id, b.title
      ORDER BY total_accesses DESC
    ";

    $offset = ($page - 1) * $perPage;
    $sqlPaged = $sql . " LIMIT " . $perPage . " OFFSET " . $offset;

    $q = $dbs->query($sqlPaged);
    // Get total records for pagination
    $totalRecordsResult = $dbs->query("SELECT FOUND_ROWS() as total");
    $totalRecords = $totalRecordsResult->fetch_assoc()['total'] ?? 0;

    if ($q && $q->num_rows > 0) {
        echo '<div class="table-responsive">';
        echo '<table class="table table-hover table-bordered w-100">';
        echo '<thead class="thead-light"><tr><th>' . __('Collection Title') . '</th><th>' . __('Total Accesses') . '</th></tr></thead>';
        echo '<tbody>';
        while ($row = $q->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['collection_title'] . '</td>';
            echo '<td>' . $row['total_accesses'] . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        // Print pagination links
        echo simbio_paging::paging($totalRecords, $perPage, 5);
    } else {
        echo '<p class="text-muted">' . __('No data available.') . '</p>';
    }
}

/**
 * Number of Accesses per File with pagination.
 * Displays File Details and Access Count.
 */
function showAccessCountPerFile($dbs, $page, $perPage) {
    echo '<h3 class="mb-3 border-bottom pb-2">' . __('Access Count per File') . '</h3>';

    $sql = "
      SELECT
        SQL_CALC_FOUND_ROWS
        f.file_title AS file_title, -- Database aligned column name
        f.file_name AS file_name, -- Database aligned column name
        f.mime_type AS mime_type, -- Database aligned column name
        MAX(ba.access_type) AS access_type, -- Database aligned column name
        MAX(b.biblio_id) AS BiblioID,
        MAX(b.title) AS collection_title, -- Database aligned column name
        COUNT(fr.file_id) AS total_accesses  -- Database aligned column name
      FROM files_read AS fr
        LEFT JOIN files AS f ON f.file_id = fr.file_id
        LEFT JOIN biblio_attachment AS ba ON f.file_id = ba.file_id
        LEFT JOIN biblio AS b ON b.biblio_id = ba.biblio_id
      GROUP BY f.file_id, f.file_title, f.file_name, f.mime_type
      ORDER BY total_accesses DESC
    ";

    $offset = ($page - 1) * $perPage;
    $sqlPaged = $sql . " LIMIT " . $perPage . " OFFSET " . $offset;

    $q = $dbs->query($sqlPaged);
    // Get total records for pagination
    $totalRecordsResult = $dbs->query("SELECT FOUND_ROWS() as total");
    $totalRecords = $totalRecordsResult->fetch_assoc()['total'] ?? 0;

    if ($q && $q->num_rows > 0) {
        echo '<div class="table-responsive">';
        echo '<table class="table table-hover table-bordered w-100">';
        echo '<thead class="thead-light"><tr><th>' . __('File Title') . '</th><th>' . __('File Name') . '</th><th>' . __('MIME Type') . '</th><th>' . __('Access Type') . '</th><th>' . __('Collection Title') . '</th><th>' . __('Total Accesses') . '</th></tr></thead>';
        echo '<tbody>';
        while ($row = $q->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['file_title'] . '</td>';
            echo '<td>' . $row['file_name'] . '</td>';
            echo '<td>' . $row['mime_type'] . '</td>';
            echo '<td>' . $row['access_type'] . '</td>';
            echo '<td>' . $row['collection_title'] . '</td>';
            echo '<td>' . $row['total_accesses'] . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        // Print pagination links
        echo simbio_paging::paging($totalRecords, $perPage, 5);
    } else {
        echo '<p class="text-muted">' . __('No data available.') . '</p>';
    }
}

/**
 * File Access List with pagination.
 * Displays detailed file access information.
 */
function showFileAccessList($dbs, $page, $perPage) {
    echo '<h3 class="mb-3 border-bottom pb-2">' . __('File Access List') . '</h3>';

    $sql = "
      SELECT
        SQL_CALC_FOUND_ROWS
        fr.date_read AS access_time, -- Database aligned column name
        fr.client_ip AS ip_address, -- Database aligned column name
        f.file_title AS file_title, -- Database aligned column name
        f.file_name AS file_name, -- Database aligned column name
        f.mime_type AS mime_type, -- Database aligned column name
        ba.access_type AS access_type, -- Database aligned column name
        b.title AS collection_title  -- Database aligned column name
      FROM files_read AS fr
        LEFT JOIN files AS f ON f.file_id = fr.file_id
        LEFT JOIN biblio_attachment AS ba ON ba.file_id = f.file_id
        LEFT JOIN biblio AS b ON b.biblio_id = ba.biblio_id
      ORDER BY fr.date_read DESC
    ";

    $offset = ($page - 1) * $perPage;
    $sqlPaged = $sql . " LIMIT " . $perPage . " OFFSET " . $offset;

    $q = $dbs->query($sqlPaged);
    // Get total records for pagination
    $totalRecordsResult = $dbs->query("SELECT FOUND_ROWS() as total");
    $totalRecords = $totalRecordsResult->fetch_assoc()['total'] ?? 0;

    if ($q && $q->num_rows > 0) {
        echo '<div class="table-responsive">';
        echo '<table class="table table-hover table-bordered w-100">';
        echo '<thead class="thead-light"><tr><th>' . __('Access Time') . '</th><th>' . __('IP Address') . '</th><th>' . __('File Title') . '</th><th>' . __('File Name') . '</th><th>' . __('MIME Type') . '</th><th>' . __('Access Type') . '</th><th>' . __('Collection Title') . '</th></tr></thead>';
        echo '<tbody>';
        while ($row = $q->fetch_assoc()) {
            echo '<tr>';
            // echo '<td>' . strftime('%Y-%m-%d %H:%M:%S', strtotime($row['access_time'])) . '</td>';
            echo '<td>' . date('Y-m-d H:i:s', strtotime($row['access_time'])) . '</td>' // more compatible with php 8.2+
            echo '<td>' . $row['ip_address'] . '</td>';
            echo '<td>' . $row['file_title'] . '</td>';
            echo '<td>' . $row['file_name'] . '</td>';
            echo '<td>' . $row['mime_type'] . '</td>';
            echo '<td>' . $row['access_type'] . '</td>';
            echo '<td>' . $row['collection_title'] . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        // Print pagination links
        echo simbio_paging::paging($totalRecords, $perPage, 2);

    } else {
        echo '<p class="text-muted">' . __('No data available.') . '</p>';
    }
}

// Main plugin execution
$page_title = __('File Access Reports'); // More descriptive page title

// Query for most active month
$sql_most_active_month = "
    SELECT
        DATE_FORMAT(date_read, '%Y-%m') AS active_month, -- Database aligned column name
        COUNT(*) AS total
    FROM files_read
    GROUP BY active_month
    ORDER BY total DESC
    LIMIT 1
";
$mostActiveMonthResult = $dbs->query($sql_most_active_month);
$mostActiveMonth = ($mostActiveMonthResult->num_rows > 0) ? $mostActiveMonthResult->fetch_assoc()['active_month'] : __('N/A');

// Query for total accesses
$sql_total_accesses = "SELECT COUNT(*) AS total_accesses FROM files_read"; // Database aligned column name
$totalAccessesResult = $dbs->query($sql_total_accesses);
$totalAccesses = ($totalAccessesResult->num_rows > 0) ? $totalAccessesResult->fetch_assoc()['total_accesses'] : 0;

// Query for total file list
$sql_total_files = "SELECT COUNT(file_id) AS total_files FROM files"; // Database aligned column name
$totalFilesResult = $dbs->query($sql_total_files);
$totalFiles = ($totalFilesResult->num_rows > 0) ? $totalFilesResult->fetch_assoc()['total_files'] : 0;
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
                    <p class="card-text fs-5"><?php echo $mostActiveMonth; ?></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card bg-light shadow-sm h-100">
                <div class="card-body p-3">
                    <h5 class="card-title"><?php echo __('Total Accesses'); ?></h5>
                    <p class="card-text fs-5"><?php echo $totalAccesses; ?></p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card bg-light shadow-sm h-100">
                <div class="card-body p-3">
                    <h5 class="card-title"><?php echo __('Total Files'); ?></h5>
                    <p class="card-text fs-5"><?php echo $totalFiles; ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php
    // Display all report functions with pagination
    showAccessCountPerCollection($dbs, $page, $perPage);
    showAccessCountPerFile($dbs, $page, $perPage);
    showFileAccessList($dbs, $page, $perPage);
    ?>
</div>
