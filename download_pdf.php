<?php
require __DIR__ . '/vendor/autoload.php';
 // make sure you installed dompdf via composer
include 'db_connect.php';

use Dompdf\Dompdf;

$type = $_GET['type'] ?? '';

$html = "<h2 style='text-align:center;'>";

if ($type === 'payment') {
    $html .= "Monthly Payments Report</h2>";
    $query = "SELECT * FROM payments"; // adjust your query
} elseif ($type === 'balance') {
    $html .= "Rental Balances Report</h2>";
    $query = "SELECT * FROM balances"; // adjust your query
} else {
    die("Invalid report type.");
}

$result = $conn->query($query);
$html .= "<table border='1' width='100%' cellspacing='0' cellpadding='5'>";
$html .= "<tr>";

if ($type === 'payment') {
    $html .= "<th>Payment ID</th><th>Tenant</th><th>Amount</th><th>Date</th>";
} elseif ($type === 'balance') {
    $html .= "<th>Tenant ID</th><th>Tenant Name</th><th>Balance</th>";
}

$html .= "</tr>";

while ($row = $result->fetch_assoc()) {
    $html .= "<tr>";
    foreach ($row as $value) {
        $html .= "<td>".htmlspecialchars($value)."</td>";
    }
    $html .= "</tr>";
}
$html .= "</table>";

// create Dompdf instance
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// download PDF
$filename = ($type === 'payment') ? 'monthly_payments_report.pdf' : 'rental_balances_report.pdf';
$dompdf->stream($filename, ["Attachment" => 1]);
exit;
