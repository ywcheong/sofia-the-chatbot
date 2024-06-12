<?php
    session_start();
    if($_SESSION['admin'] != TRUE){
        header('Location: ../home/index.php');
    }

    if($_SESSION['viewonly']){
        die("관리자만 접근 가능");
    }
    
    if(file_exists($_SERVER['DOCUMENT_ROOT']."/data/buffered.txt")){
        $_SESSION['NEED_FILESET'] = TRUE;
        header('Location: dictHandle.php');
    }else{
        $_SESSION['NEED_FILESET'] = FALSE;
    }

    require_once('../../structure.php');
    $MASTER = new MasterAction("WEB");
    require_once('../../const.php');
    require_once('table.php');
    require_once('../supply/head.php');
    require_once('phase.php');
?>

</div>
<div class="break"></div>
<?php echo $notify; ?>
<div class="simb"></div>
<a href="dict.php" class="myButton"><h3>가이드라인으로 돌아가기</h3></a>
<div class="simb"></div>
<form action="dictHandle.php" method="post" enctype="multipart/form-data" name="dicfile">
  <div class="input-file-container">  
    <input class="input-file" name="newDictData" id="tracefile" type="file">
    <label tabindex="0" for="tracefile" class="input-file-trigger myButtonForFile file-return">번역 가이드라인 파일 선택...</label>
  </div>
  <input type="hidden" name="formsend" value="yes" />
</form>
<a class="myButton turnoff" id="uploadtrack" onclick='document.forms["dicfile"].submit(); return false; '><h3>업로드하기</h3></a>
<div class="break"></div>
<div class="wrap-table100" id="datatable">
<script>

    document.querySelector("html").classList.add('js');
    var fileInput  = document.querySelector( ".input-file" ),  
        button     = document.querySelector( ".input-file-trigger" ),
        the_return = document.querySelector(".file-return");
        
    button.addEventListener( "keydown", function( event ) {  
        if ( event.keyCode == 13 || event.keyCode == 32 ) {  
            fileInput.focus();  
        }  
    });

    button.addEventListener( "click", function( event ) {
    fileInput.focus();
    return false;
    });  

    fileInput.addEventListener( "change", function( event ) {  
        var fullPath = this.value;
        if (fullPath) {
            var startIndex = (fullPath.indexOf('\\') >= 0 ? fullPath.lastIndexOf('\\') : fullPath.lastIndexOf('/'));
            var filename = fullPath.substring(startIndex);
            if (filename.indexOf('\\') === 0 || filename.indexOf('/') === 0) {
                filename = filename.substring(1);
            }
        }
        the_return.innerHTML = filename + "을 선택함...";  
        $('#uploadtrack').removeClass("turnoff");
        $('.input-file-trigger').css("font-style", "italic");
    });  
</script>
<?php
    require('../supply/foot.php');
?>