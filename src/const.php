<?php
    require_once('valid.php');
    
    $MASTER->setPrimary();
    if(!$MASTER->isPrimary()){
        //5회 재시도 후 작업진행권 획득 실패 시 SELF KILL
        $MASTER->setError('PRIMARY_FAIL');
        $MASTER->setAction('PRIMARY_FAIL', TRUE);
    }else{
        $MASTER->setAction('PRIMARY_FAIL', FALSE);
    }

    $MASTER->setAction('INACTIVE_PERIOD', 0);
    $MASTER->setAction('ADMISSION_PERIOD', 1);
    $MASTER->setAction('TRANSLATION_PERIOD', 2);
    $MASTER->setAction('CALCULATION_PERIOD', 3);   

    // *************************** 여기서부터 사용자 설정 구간 ***************************

    // LATE_THRESHOLD = 번역 작업이 등록된 시점부터 지각이 되기까지 걸리는 시간(초) (기본값=172800)
    $MASTER->setAction('LATE_THRESHOLD', 172800);   

    // DICT_ACCEPT_MAX_LENGTH = Sofia 챗봇의 번역 도우미 기능에서 받아들이는 최대 글자수 (기본값=10000)
    $MASTER->setAction('DICT_ACCEPT_MAX_LENGTH', 10000);   

    // ADMIN_SESSION_TIMEOUT = Sofia 웹페이지 로그인 후 자동 로그아웃까지 걸리는 시간(초) (기본값=3600)
    $MASTER->setAction('ADMIN_SESSION_TIMEOUT', 3600);
    
    // LETTER_TIME_CURRENCY = 번역 1글자(공백 제외)당 환산되는 봉사시간(초) (기본값=3.942) 
    $MASTER->setAction('LETTER_TIME_CURRENCY', 3.942);

    // WEB_ROOT = structure.php가 속한 폴더의 웹 경로 (예시=http://example.com/)
    // !! For security reason, this content is replaced with '!!HIDDEN!!' placeholder
    $MASTER->setAction('WEB_ROOT', "!!HIDDEN!!");

    // KAKAO_LINK = Sofia 챗봇의 플러스친구 등록 웹 경로 (예시=http://pf.kakao.com/_xocQIxb)
    // !! For security reason, this content is replaced with '!!HIDDEN!!' placeholder
    $MASTER->setAction('KAKAO_LINK', '!!HIDDEN!!');

    // ALLOW_INITIATION = action.php를 통한 Sofia 시스템의 강제 초기화 허용 옵션 (기본값=FALSE)
    $MASTER->setAction('ALLOW_INITIATION', FALSE);

    // *************************** 여기까지가 사용자 설정 구간 ***************************

    $MASTER->setAction('REQUIRED_METADATA', "EDITOR,DATE");
    $MASTER->setAction('userClass', loadUser());
    $MASTER->setAction('teamClass', loadTeam());
    $MASTER->setAction('workClass', loadWork());
    $MASTER->setAction('phase', loadPhase());
    $MASTER->setAction('dictClass', loadDict());

    if(!$MASTER->getAction('userClass')) $MASTER->setError("PARSE_FAIL_USER", TRUE);
    if(!$MASTER->getAction('teamClass')) $MASTER->setError("PARSE_FAIL_TEAM", TRUE);
    if(!$MASTER->getAction('workClass')) $MASTER->setError("PARSE_FAIL_WORK", TRUE);
    if(!$MASTER->getAction('phase')) $MASTER->setError("PARSE_FAIL_PHASE", TRUE);
    if(is_numeric($MASTER->getAction('dictClass'))) $MASTER->setError("DICT_FORMAT_ERROR (line ".$MASTER->getAction('dictClass').")", TRUE);
?>