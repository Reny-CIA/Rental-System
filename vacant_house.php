<?php
include 'db_connect.php';

// Fetch vacant houses
$vacant = [];
$query = $conn->query("
    SELECT h.house_number, c.name as type, h.price
    FROM houses h
    JOIN categories c ON h.category_id = c.id
    WHERE NOT EXISTS (
        SELECT 1 FROM tenants t WHERE t.house_id = h.id AND t.status = 1
    )
");
while($row = $query->fetch_assoc()){
    $vacant[] = $row;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <h4 class="mb-3">Vacant Houses Details</h4>
            <button id="downloadPdf" class="btn btn-primary mb-3">Download PDF</button>
            <table class="table table-bordered table-striped" id="vacantTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>House Number</th>
                        <th>Type</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($vacant as $i => $v): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= htmlspecialchars($v['house_number']) ?></td>
                        <td><?= htmlspecialchars($v['type']) ?></td>
                        <td><?= number_format($v['price'],2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
<script>
document.getElementById('downloadPdf').addEventListener('click', () => {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.text("Vacant Houses Report", 14, 20);
    doc.autoTable({ html: '#vacantTable', startY: 30 });

    doc.save("vacant_houses_report.pdf");
});
</script>
