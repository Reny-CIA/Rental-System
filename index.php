<!DOCTYPE html>
<html lang="en">
<?php 
session_start(); 
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!isset($_SESSION['login_id'])){
    include 'landing.php';
} else {
    include 'home.php';
}


include('./header.php'); 
?>
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?php echo $_SESSION['system']['name'] ?? '' ?></title>

  <style>
    body {
        background: #80808045;
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Sidebar */
    nav#sidebar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: 220px;
        background-color: #343a40;
        color: white;
        overflow-y: auto;
        padding-top: 1rem;
        z-index: 999;
    }

    nav#sidebar a {
        display: block;
        color: white;
        text-decoration: none;
        padding: 12px 20px;
        transition: background 0.2s;
    }

    nav#sidebar a:hover,
    nav#sidebar a.active {
        background-color: #495057;
    }

    nav#sidebar .icon-field {
        width: 20px;
        display: inline-block;
    }

    /* Main Content */
    #view-panel {
        margin-left: 220px; /* leave space for sidebar */
        padding: 20px;
        min-height: 100vh;
    }

    .card {
        margin-bottom: 20px;
    }

    .toast {
        position: fixed;
        top: 10px;
        right: 10px;
        z-index: 1050;
    }

    .modal-dialog.large { width: 80% !important; max-width: unset; }
    .modal-dialog.mid-large { width: 50% !important; max-width: unset; }

    #viewer_modal .btn-close {
        position: absolute;
        z-index: 999999;
        background: unset;
        color: white;
        border: unset;
        font-size: 27px;
        top: 0;
    }

    #viewer_modal .modal-dialog {
        width: 80%;
        max-width: unset;
        height: calc(90%);
        max-height: unset;
    }
    #viewer_modal .modal-content {
        background: black;
        border: unset;
        height: calc(100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    #viewer_modal img,#viewer_modal video{
        max-height: calc(100%);
        max-width: calc(100%);
    }
  </style>
</head>
<body>

    <!-- Sidebar -->
    <?php include 'navbar.php'; ?>

    <!-- Main content -->
    <div id="view-panel">
        <?php 
        $page = $_GET['page'] ?? 'home';
        include $page.'.php'; 
        ?>
    </div>

    <!-- Toast -->
    <div class="toast" id="alert_toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body text-white"></div>
    </div>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Modals -->
    <div class="modal fade" id="confirm_modal" role='dialog'>
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Confirmation</h5></div>
                <div class="modal-body"><div id="delete_content"></div></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id='confirm' onclick="">Continue</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uni_modal" role='dialog'>
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"></h5></div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id='submit' onclick="$('#uni_modal form').submit()">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewer_modal" role='dialog'>
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <button type="button" class="btn-close" data-dismiss="modal"><span class="fa fa-times"></span></button>
                <img src="" alt="">
            </div>
        </div>
    </div>

<script>
    // Preloader
    window.start_load = function(){
        $('body').prepend('<div id="preloader2"></div>');
    }
    window.end_load = function(){
        $('#preloader2').fadeOut('fast', function() { $(this).remove(); });
    }

    // Viewer Modal
    window.viewer_modal = function($src = ''){
        start_load();
        var t = $src.split('.').pop();
        var view = (t=='mp4') ? $("<video src='"+$src+"' controls autoplay></video>") : $("<img src='"+$src+"' />");
        $('#viewer_modal .modal-content video,#viewer_modal .modal-content img').remove();
        $('#viewer_modal .modal-content').append(view);
        $('#viewer_modal').modal({ show:true, backdrop:'static', keyboard:false, focus:true });
        end_load();
    }

    // Uni modal
    window.uni_modal = function($title = '' , $url='',$size=""){
        start_load();
        $.ajax({
            url:$url,
            error:()=>{ alert("An error occured"); },
            success:function(resp){
                if(resp){
                    $('#uni_modal .modal-title').html($title)
                    $('#uni_modal .modal-body').html(resp)
                    if($size != ''){
                        $('#uni_modal .modal-dialog').addClass($size)
                    }else{
                        $('#uni_modal .modal-dialog').removeAttr("class").addClass("modal-dialog modal-md")
                    }
                    $('#uni_modal').modal({ show:true, backdrop:'static', keyboard:false, focus:true });
                    end_load()
                }
            }
        });
    }

    window._conf = function($msg='',$func='',$params = []){
        $('#confirm_modal #confirm').attr('onclick',$func+"("+$params.join(',')+")")
        $('#confirm_modal .modal-body').html($msg)
        $('#confirm_modal').modal('show')
    }

    window.alert_toast= function($msg = 'TEST',$bg = 'success'){
        $('#alert_toast').removeClass('bg-success bg-danger bg-info bg-warning');
        $('#alert_toast').addClass('bg-'+$bg);
        $('#alert_toast .toast-body').html($msg);
        $('#alert_toast').toast({delay:3000}).toast('show');
    }

    $(document).ready(function(){
        $('#preloader').fadeOut('fast', function() { $(this).remove(); })
        $('.datetimepicker').datetimepicker({ format:'Y/m/d H:i', startDate: '+3d' });
        $('.select2').select2({ placeholder:"Please select here", width: "100%" });
    });
</script>

</body>
</html>
