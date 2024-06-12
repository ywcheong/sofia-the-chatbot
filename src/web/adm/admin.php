<?php
    session_start();
    if($_REQUEST['revoke'] == "REVOKE"){
        session_destroy();
        header('Location: ../home/index.php');
    }
    
    require_once('../../structure.php');
    $MASTER = new MasterAction("WEB");
    require_once('../../const.php');
    require_once('table.php');
    require_once('../supply/head.php');
    require_once('phase.php');
    ?>
<div class="break"></div>
<?php
    require_once('../supply/foottable.php');
    ?></div>
<a href="work.php" class="myButton"><h3><?php if($_SESSION['viewonly']) echo "업무 보기"; else echo "업무 관리" ; ?></h3></a>
<a href="member.php" class="myButton"><h3><?php if($_SESSION['viewonly']) echo "버디 보기"; else echo "버디 관리" ; ?></h3></a>
<?php if(!$_SESSION['viewonly']) echo "<a href=\"phasechange.php\" class=\"myButton\"><h3>Sofia 관리</h3></a>"; ?>
<a href="dict.php" class="myButton <?php if(file_exists($_SERVER['DOCUMENT_ROOT']."/data/buffered.txt") && !$_SESSION['viewonly']) echo "red"; ?>"><h3>가이드라인</h3></a>
<a class="myButton" onclick='document.forms["admin"].submit(); return false; ' href=""><h3>로그아웃</h3></a>
<!-- 띠용 <a href="https://forms.gle/cye17bSZGNfywmSp6" class="myButton"><h3>설문조사</h3></a> -->
<form name="admin" method="POST">
    <input type="hidden" name="revoke" value="REVOKE" />
</form>
<div class="wrap-table100"><div class="break"></div>
<?php
    require('../supply/foot.php');
?>