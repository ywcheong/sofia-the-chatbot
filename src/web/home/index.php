<?php
    session_start();
    ob_start();
    require_once('../../structure.php');
    
    //여기서 $_REQUEST['uuid'] 토대로 판정 시작하고 부여
    $MASTER = new MasterAction("WEB");
    $MASTER->setAction('is_public', TRUE);
    require_once('../../const.php');
    
    $userList = $MASTER->getAction('userClass')->allUser();
    $searchAdmin = FALSE;
    for($i=0; $i<count($userList); $i++){
        if($userList[$i]->getKey() == $_REQUEST['key']){
            $searchAdmin = $userList[$i];
            break;
        }
    }
    
    if($searchAdmin){
        if($searchAdmin->isAdmin()){
            $_SESSION['admin'] = TRUE;
            $_SESSION['uuid'] = $searchAdmin->uuid();
            $_SESSION['begintime'] = (new DateTime("now", new DateTimeZone('Asia/Seoul')))->getTimestamp();
        }else if($searchAdmin->accept()){
            $_SESSION['admin'] = TRUE;
            $_SESSION['viewonly'] = TRUE;
            $_SESSION['uuid'] = $searchAdmin->uuid();
            $_SESSION['begintime'] = (new DateTime("now", new DateTimeZone('Asia/Seoul')))->getTimestamp();
        }
    }

    if($_SESSION['admin'] == TRUE && abs($_SESSION['begintime'] - (new DateTime("now", new DateTimeZone('Asia/Seoul')))->getTimestamp()) <= $MASTER->getAction('ADMIN_SESSION_TIMEOUT')){
        $MASTER->endPrimary();
        unset($MASTER);
        header('Location: ../adm/admin.php');
        exit;
    }

    
    
    require('../supply/head.php');

    if($_SESSION['is_timeout']){
        unset($_SESSION['is_timeout']);
    }

    // require('../adm/phase.php');
?>
<div class="break"></div>
<?php
    require('../supply/foottable.php');
    if($_SESSION['admin'] == TRUE){
        header('Location: ../adm/admin.php');
        exit;
    }
    else{
        require('verify.php');
    }

    require('../supply/foot.php');
    ob_end_flush();
?>