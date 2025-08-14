<?php
include 'db_connect.php';

// Fetch vacant houses
$vacant = [];
$query = $conn->query("
    SELECT h.id, h.house_number, c.name as type, h.price
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
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($vacant as $i => $v): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= htmlspecialchars($v['house_number']) ?></td>
                        <td><?= htmlspecialchars($v['type']) ?></td>
                        <td><?= number_format($v['price'],2) ?></td>
                        <td>
                            <button 
                                class="btn btn-info btn-sm view-details" 
                                data-id="<?= $v['id'] ?>"
                                data-number="<?= htmlspecialchars($v['house_number']) ?>"
                                data-type="<?= htmlspecialchars($v['type']) ?>"
                                data-price="<?= number_format($v['price'],2) ?>"
                            >View Details</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="houseModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">House Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>House Number:</strong> <span id="modalNumber"></span></p>
        <p><strong>Type:</strong> <span id="modalType"></span></p>
        <p><strong>Price:</strong> $<span id="modalPrice"></span></p>
      </div>
      <div class="modal-footer">
        <button id="requestBooking" class="btn btn-primary">Request Booking</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script>
let selectedHouseId = null;

$('.view-details').click(function(){
    selectedHouseId = $(this).data('id');
    $('#modalNumber').text($(this).data('number'));
    $('#modalType').text($(this).data('type'));
    $('#modalPrice').text($(this).data('price'));
    $('#houseModal').modal('show');
});

$('#requestBooking').click(function(){
    $.post('request_booking.php', { house_id: selectedHouseId }, function(resp){
        alert(resp);
        $('#houseModal').modal('hide');
    });
});

document.getElementById('downloadPdf').addEventListener('click', () => {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.text("Vacant Houses Report", 14, 20);
    doc.autoTable({ html: '#vacantTable', startY: 30 });
    doc.save("vacant_houses_report.pdf");
});
</script>
