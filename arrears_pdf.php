<?php
require 'vendor/autoload.php';
include 'db_connect.php';
use Dompdf\Dompdf;

// Get date range from GET request
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

if(!$start_date || !$end_date){
    die("Start date and End date are required.");
}

// Fetch tenants with arrears in the date range
$arrearsQuery = $conn->prepare("
  SELECT t.name as tenant_name, h.name as house_name, p.amount_due, p.amount_paid, p.date_created
  FROM payments p
  JOIN tenants t ON p.tenant_id = t.id
  JOIN houses h ON t.house_id = h.id
  WHERE p.amount_due > p.amount_paid
    AND DATE(p.date_created) BETWEEN ? AND ?
  ORDER BY t.name
");
$arrearsQuery->bind_param("ss", $start_date, $end_date);
$arrearsQuery->execute();
$result = $arrearsQuery->get_result();

// Build HTML for PDF
$html = '<h2 style="text-align:center;">Tenant Arrears Report</h2>';
$html .= '<p style="text-align:center;">From: '.$start_date.' To: '.$end_date.'</p>';
$html .= '<table border="1" cellspacing="0" cellpadding="5" width="100%">';
$html .= '<thead>
            <tr style="background-color:#f2f2f2;">
              <th>Tenant</th>
              <th>House</th>
              <th>Amount Due</th>
              <th>Amount Paid</th>
              <th>Arrears</th>
              <th>Date</th>
            </tr>
          </thead><tbody>';

while($row = $result->fetch_assoc()){
    $arrears = $row['amount_due'] - $row['amount_paid'];
    $date = date("Y-m-d", strtotime($row['date_created']));
    $html .= "<tr>
                <td>{$row['tenant_name']}</td>
                <td>{$row['house_name']}</td>
                <td>".number_format($row['amount_due'],2)."</td>
                <td>".number_format($row['amount_paid'],2)."</td>
                <td>".number_format($arrears,2)."</td>
                <td>{$date}</td>
              </tr>";
}

$html .= '</tbody></table>';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("arrears_report_{$start_date}_to_{$end_date}.pdf", ["Attachment" => true]);
