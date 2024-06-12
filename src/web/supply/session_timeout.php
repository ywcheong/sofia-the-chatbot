<?php
    require_once('../../valid.php');
    session_start();
    ob_start();
    
    if(!$MASTER->getAction('userClass')->getUserByUUID($_SESSION['uuid'])){
        $_SESSION['admin'] = FALSE; 
    }else if(!$MASTER->getAction('userClass')->getUserByUUID($_SESSION['uuid'])->isAdmin()){
        $_SESSION['viewonly'] = TRUE; 
    }

    if($_SESSION['admin'] !== TRUE){
        if(!$MASTER->getAction('is_public')){
            $MASTER->endPrimary();
            session_destroy();
            header('Location: ../home/index.php');
            exit;
        }
    }

    if(abs($_SESSION['begintime'] - (new DateTime("now", new DateTimeZone('Asia/Seoul')))->getTimestamp()) > $MASTER->getAction('ADMIN_SESSION_TIMEOUT') || !isset($_SESSION['begintime'])){
        if(!$MASTER->getAction('is_public')){
            $MASTER->endPrimary();
            session_destroy();
            session_start();
            $_SESSION['is_timeout'] = TRUE;
            unset($MASTER);
            header('Location: ../home/index.php');
            exit;
        }
    }

    ob_end_flush();
?>