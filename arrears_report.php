<?php
include 'db_connect.php';

// Fetch all tenants with arrears
$arrearsQuery = $conn->query("
  SELECT t.name as tenant_name, h.name as house_name, p.amount_due, p.amount_paid, p.date_created
  FROM payments p
  JOIN tenants t ON p.tenant_id = t.id
  JOIN houses h ON t.house_id = h.id
  WHERE p.amount_due > p.amount_paid
  ORDER BY t.name
");
?>

<div class="container">
  <h3>Tenant Arrears Report</h3>
  <button id="downloadPdf" class="btn btn-primary mb-3">Download PDF</button>
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>Tenant</th>
        <th>House</th>
        <th>Amount Due</th>
        <th>Amount Paid</th>
        <th>Arrears</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $arrearsQuery->fetch_assoc()): ?>
      <tr>
        <td><?= $row['tenant_name'] ?></td>
        <td><?= $row['house_name'] ?></td>
        <td><?= number_format($row['amount_due'],2) ?></td>
        <td><?= number_format($row['amount_paid'],2) ?></td>
        <td><?= number_format($row['amount_due'] - $row['amount_paid'],2) ?></td>
        <td><?= date("Y-m-d", strtotime($row['date_created'])) ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
<script>
document.getElementById('downloadPdf').addEventListener('click', () => {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.text("Tenant Arrears Report", 14, 20);
    doc.autoTable({ html: 'table', startY: 30 });

    doc.save("arrears_report.pdf");
});
</script>
