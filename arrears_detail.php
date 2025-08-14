<?php
include 'db_connect.php';

// Fetch arrears
$arrears = [];
$query = $conn->query("
    SELECT t.id as tenant_id, t.name as tenant_name, h.name as house_name,
           (h.rent_amount - IFNULL(p.paid,0)) as amount_owed
    FROM tenants t
    JOIN houses h ON t.house_id = h.id
    LEFT JOIN (
        SELECT tenant_id, SUM(amount) as paid
        FROM payments
        GROUP BY tenant_id
    ) p ON p.tenant_id = t.id
    WHERE t.status = 1 AND (h.rent_amount - IFNULL(p.paid,0)) > 0
");
while($row = $query->fetch_assoc()){
    $arrears[] = $row;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <h4 class="mb-3">Tenant Arrears Details</h4>
            <table class="table table-bordered table-striped" id="arrearsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tenant Name</th>
                        <th>House</th>
                        <th>Amount Owed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($arrears as $i => $a): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= htmlspecialchars($a['tenant_name']) ?></td>
                        <td><?= htmlspecialchars($a['house_name']) ?></td>
                        <td><?= number_format($a['amount_owed'],2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#arrearsTable').DataTable({
        "order": [[3, "desc"]] // order by amount owed descending
    });
});
</script>
