<?php
    //FLOWIN Given AJAX data
    //FLOWOUT Reconstructed table(success) / FALSE(fail)
    session_start();
    header('Content-Type: application/json; charset=UTF-8');
    require_once('../../structure.php');
    $MASTER = new MasterAction("WEB");
    require_once('../../const.php');
    require_once('table.php');

    if(!$MASTER->getAction('userClass')->getUserByUUID($_SESSION['uuid'])){
        $MASTER->endPrimary();
        session_destroy();
        session_start();
        $_SESSION['is_timeout'] = TRUE;
        unset($MASTER);
        ajaxError("세션이 만료되었거나 관리자 권한이 없습니다. 새로고침해주세요.");
    }else if(!$MASTER->getAction('userClass')->getUserByUUID($_SESSION['uuid'])->isAdmin()){
        $_SESSION['viewonly'] = TRUE;
    }

    if(abs($_SESSION['begintime'] - (new DateTime("now", new DateTimeZone('Asia/Seoul')))->getTimestamp()) > $MASTER->getAction('ADMIN_SESSION_TIMEOUT') || !isset($_SESSION['begintime'])){
        $MASTER->endPrimary();
        session_destroy();
        session_start();
        $_SESSION['is_timeout'] = TRUE;
        unset($MASTER);
        ajaxError("세션이 만료되었습니다. 새로고침해주세요.");
    }

    $MASTER->setAction('WEBJSON', $_REQUEST);

    
    $action = $MASTER->getAction('WEBJSON')['action'];
    switch($action){
        case "work":
            work();
        break;
        
        case "member":
            member();
        break;

        case "dict":
            dict();
        break;
        
        default:
        ajaxError("INVALID_ACTION");
        break;
    }
    
function dict(){
    
    global $MASTER;

    $sideaction = $MASTER->getAction('WEBJSON')['sideaction'];
    $moredata = $MASTER->getAction('WEBJSON')['moredata'];

    switch($sideaction){
        case "generate":
            //NOTHING
        break;
        
        default:
        ajaxError('동작 구문이 잘못된 요청입니다. 새로고침 후 다시 시도해 주세요.');
    break;
    }

    $dictJSON = &$MASTER->getAction('dictClass')->JSONify();

    $MASTER->setAnswerWeb($dictJSON, TRUE);

}

function neatdict($arr){
    $rtn = "";
    for($i = 0; $i < count($arr); $i++){
        $rtn .= $arr[$i];
        if($i != count($arr) - 1) $rtn .= " & ";
    }
    return $rtn;
}

function member(){
    global $MASTER;
    
    if($MASTER->isError()){
        $table = new TableElement("오류 발생!", array("오류 코드", "해설", "", "", "", ""));
        
            $table->addContent(array(
                $MASTER->getError()
            ));
            $showTable = new TableList();
            $showTable->addTable($table);
            $MASTER->setAnswerWeb($showTable->generate());
            return;
        }
        
        $sideaction = $MASTER->getAction('WEBJSON')['sideaction'];
        $moredata = $MASTER->getAction('WEBJSON')['moredata'];

        if($sideaction != "generate" && $_SESSION['viewonly']){
            ajaxError("관리자만 접근 가능");
        }

        

        //var_dump($MASTER);
        // SIDEACTION
        // - generate (Anyphase)
        //
        // - requser.[ACTION] << UID-XXX
        // - requser.accept << ru-accept (Phase 1) V
        // - requser.revoke << ru-revoke (Phase 1) V
        //
        // - acpuser.[ACTION] << UID-XXX
        // - acpuser.detail << au-detail V
        // - acpuser.super << au-super (Anyphase, toggle)
        // - acpuser.remove << au-remove (Phase 1, 2)
        // - acpuser.givetime << au-givetime (Phase 2, 3)
        //
        // - team.[ACTION] << TID-XXX
        // // - team.detail << t-detail V
        // // - team.make << T-ID1 T-ID2 T-MAKE (Phase 1) V
        // - team.rest << t-rest (Phase 2, toggle) V
        // - team.warn << t-warnadd (Phase 2) V
        // - team.warn << t-warnsub (Phase 2) V
        // // - team.remove << t-remove (Phase 1) V
        // - team.[ACTION]
        //if($sideaction != "generate") ajaxError($sideaction);

        

        switch($sideaction){
            case "generate":
                //NOTHING
            break;

            case "ru-acpall":
                if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('ADMISSION_PERIOD'))
                    ajaxError("선발 또는 번역 기간이 아닙니다.");
                $userList = &$MASTER->getAction('userClass')->allUser();

                $oldTeamNum = array();
                $teamClass = &$MASTER->getAction('teamClass')->allTeam();
                
                for($i = 0; $i < count($teamClass); $i++){
                    array_push($oldTeamNum, $teamClass[$i]->teamid());
                }
                
                $maxVal = max($oldTeamNum) + 1;

                $allAccept = TRUE;
                for($i=0; $i<count($userList); $i++){
                    if(!($userList[$i]->accept())){
                        $allAccept = FALSE;
                        $userList[$i]->setAccept(TRUE);
                        $MASTER->getAction('teamClass')->addTeam($userList[$i]->uuid(), null, $maxVal++);
                    }
                }
                if($allAccept) ajaxError("승인을 기다리는 사용자가 없습니다.");
            break;

            // case "ru-revall":
            //     if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('ADMISSION_PERIOD'))
            //         ajaxError("선발 또는 번역 기간이 아닙니다.");
            //     $userList = &$MASTER->getAction('userClass')->allUser();
            //     $allAccept = TRUE;
            //     for($i=0; $i<count($userList); $i++){
            //         if(!($userList[$i]->accept())){
            //             $allAccept = FALSE;
            //             $userList[$i]->setAccept(FALSE);
            //         }
            //     }
            //     if($allAccept) ajaxError("승인을 기다리는 사용자가 없습니다.");
            // break;
            
            case "ru-accept":
                if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('ADMISSION_PERIOD'))
                    ajaxError("선발 또는 번역 기간이 아닙니다.");
                if(preg_match('/^UID-([a-f0-9]+)$/', $moredata, $match) === FALSE) ajaxError('요청하신 데이터의 형식이 잘못되었습니다. 새로고침 후 다시 시도해 주세요.');
                $userObj = &$MASTER->getAction('userClass')->getUserByUUID($match[1]);
                if(!$userObj) ajaxError('존재하지 않는 사용자입니다.');
                if($userObj->accept()) ajaxError('이미 승인된 사용자입니다.');

                $oldTeamNum = array();
                $teamClass = &$MASTER->getAction('teamClass')->allTeam();
                
                for($i = 0; $i < count($teamClass); $i++){
                    array_push($oldTeamNum, $teamClass[$i]->teamid());
                }
                
                $maxVal = max($oldTeamNum) + 1;

                $userObj->setAccept(TRUE);
                $MASTER->getAction('teamClass')->addTeam($userObj->uuid(), null, $maxVal);
            break;
            
            case "ru-revoke":
                if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('ADMISSION_PERIOD'))
                    ajaxError("선발 또는 번역 기간이 아닙니다.");
                if(preg_match('/^UID-([a-f0-9]+)$/', $moredata, $match) === FALSE) ajaxError('요청하신 데이터의 형식이 잘못되었습니다. 새로고침 후 다시 시도해 주세요.');
                $userObj = &$MASTER->getAction('userClass')->getUserByUUID($match[1]);
                if(!$userObj) ajaxError('존재하지 않는 사용자입니다.');
                if($userObj->accept()) ajaxError('이미 승인된 사용자입니다.');
                $MASTER->getAction('userClass')->dropUser($match[1]);
            break;

            case "au-detail":
                if(preg_match('/^UID-([a-f0-9]+)$/', $moredata, $match) === FALSE) ajaxError('요청하신 데이터의 형식이 잘못되었습니다. 새로고침 후 다시 시도해 주세요.');
                $MASTER->setAction('FURTHER_INFO_REQUESTED_UUID',$match[1]);
            break;
            
            case "au-super":
                //ACTION;
                if(preg_match('/^UID-([a-f0-9]+)$/', $moredata, $match) === FALSE) ajaxError('요청하신 데이터의 형식이 잘못되었습니다. 새로고침 후 다시 시도해 주세요.');
                $userObj = &$MASTER->getAction('userClass')->getUserByUUID($match[1]);
                if(!$userObj) ajaxError('존재하지 않는 사용자입니다.');
                if(!$userObj->accept()) ajaxError('승인되지 않은 사용자입니다.');
                if($userObj->uuid() == $_SESSION['uuid']) ajaxError('스스로 자신의 관리자 권한을 제거하거나 부여할 수 없습니다.');
                $userObj->setAdmin(!$userObj->isAdmin());
            break;
            
            case "au-remove":
                //ACTION;
                if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('ADMISSION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('INACTIVE_PERIOD'))
                    ajaxError("비활성화, 선발 또는 번역 기간이 아닙니다.");
                if(preg_match('/^UID-([a-f0-9]+)$/', $moredata, $match) === FALSE) ajaxError('요청하신 데이터의 형식이 잘못되었습니다. 새로고침 후 다시 시도해 주세요.');
                $userObj = &$MASTER->getAction('userClass')->getUserByUUID($match[1]);
                if(!$userObj) ajaxError('존재하지 않는 사용자입니다.');
                if(!$userObj->accept()) ajaxError('승인되지 않은 사용자입니다.');

                $teamObj = &$MASTER->getAction('teamClass')->getTeambyUUID($match[1]);
                if(!$teamObj) ajaxError('치명적인 오류: ERROR_NO_INTERNAL_TEAM_ON_USER; 개발자에게 보고 바랍니다.');
                $uuids = $teamObj->uuids();
                if(count($MASTER->getAction('workClass')->findAllWorkByUser($uuids[0], $MASTER->getAction('teamClass')))>0) ajaxError("작업을 배정받았던 사람은 삭제할 수 없습니다. 팀을 삭제하기 전 먼저 작업(완료된 작업 포함)을 삭제하세요.");
                if($uuids[1] !== null && count($MASTER->getAction('workClass')->findAllWorkByUser($uuids[1], $MASTER->getAction('teamClass')))>0) ajaxError("치명적인 오류: ERROR_DOUBLE_TEAM_ON_USER; 개발자에게 보고 바랍니다.");
                $MASTER->getAction('teamClass')->dropTeam($teamObj->teamid());
                if($userObj->uuid() == $_SESSION['uuid']) ajaxError('본인의 계정을 제거할 수 없습니다.');
                if($userObj->isAdmin()) ajaxError('관리자 권한이 있는 상태에서 계정을 제거할 수 없습니다. 계정을 삭제하기 전에 먼저 관리자 권한을 철회해 주세요.');
                $MASTER->getAction('userClass')->dropUser($match[1]);
            break;
            
            case "au-givetime":
                if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('CALCULATION_PERIOD'))
                   ajaxError("번역 또는 정산 기간이 아닙니다.");
                if(preg_match('/^UID-([a-f0-9]+)$/', $moredata, $match) === FALSE) ajaxError('요청하신 데이터의 형식이 잘못되었습니다. 새로고침 후 다시 시도해 주세요.');
                $userObj = &$MASTER->getAction('userClass')->getUserByUUID($match[1]);
                if(!$userObj) ajaxError('존재하지 않는 사용자입니다.');
                if(!$userObj->accept()) ajaxError('승인되지 않은 사용자입니다.');
                $giveCheatLetter = $MASTER->getAction('WEBJSON')['U-SPE'];
                if(!is_numeric($giveCheatLetter)) ajaxError('잘못된 입력 형식입니다.');
                if((int)$giveCheatLetter != $giveCheatLetter) ajaxError('자수는 정수 단위로 부여/차감 가능합니다.');
                $userObj->giveLetterByCheat((int)$giveCheatLetter);
            break;

            case "au-rekey":
                if(preg_match('/^UID-([a-f0-9]+)$/', $moredata, $match) === FALSE) ajaxError('요청하신 데이터의 형식이 잘못되었습니다. 새로고침 후 다시 시도해 주세요.');
                $userObj = &$MASTER->getAction('userClass')->getUserByUUID($match[1]);
                if(!$userObj) ajaxError('존재하지 않는 사용자입니다.');
                if(!$userObj->accept()) ajaxError('승인되지 않은 사용자입니다.');
                $userList = &$MASTER->getAction('userClass')->allUser();
                while(TRUE){
                    $userObj->setKey();
                    $key = $userObj->getKey();
                    $dup = FALSE;
                    for($i=0; $i<count($userList); $i++){
                        if($userList[$i]->getKey() == $key && $userList[$i]->uuid() != $userObj->uuid()){
                            $dup = TRUE;
                            break;
                        }
                    }
                    if(!$dup) break;
                }
            break;

            case "au-warnadd":
                if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('ADMISSION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('CALCULATION_PERIOD'))
                ajaxError("번역 또는 정산 기간이 아닙니다.");
                if(preg_match('/^UID-([a-f0-9]+)$/', $moredata, $match) === FALSE) ajaxError('요청하신 데이터의 형식이 잘못되었습니다. 새로고침 후 다시 시도해 주세요.');
                $userObj = &$MASTER->getAction('userClass')->getUserByUUID($match[1]);
                if(!$userObj) ajaxError("존재하지 않는 사용자입니다.");
                $teamObj = &$MASTER->getAction('teamClass')->getTeambyUUID($userObj->uuid());
                if(!$teamObj) ajaxError('존재하지 않는 사용자입니다.');
                $teamObj->addWarn(1);
                break;
                
            case "au-warnsub":
                if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('ADMISSION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('CALCULATION_PERIOD'))
                    ajaxError("번역 또는 정산 기간이 아닙니다.");
                if(preg_match('/^UID-([a-f0-9]+)$/', $moredata, $match) === FALSE) ajaxError('요청하신 데이터의 형식이 잘못되었습니다. 새로고침 후 다시 시도해 주세요.');
                $userObj = &$MASTER->getAction('userClass')->getUserByUUID($match[1]);
                $teamObj = &$MASTER->getAction('teamClass')->getTeambyUUID($userObj->uuid());
                if(!$teamObj) ajaxError('존재하지 않는 사용자입니다.');
                if($teamObj->getWarn() <= 0) ajaxError('부여된 경고가 없습니다.');
                $teamObj->addWarn(-1);
            break;
            
            // case "t-make":
            //     //ACTION;
            //     if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('ADMISSION_PERIOD'))
            //         ajaxError("선발 또는 번역 기간이 아닙니다.");
            //     $id1 = $MASTER->getAction('WEBJSON')['T-ID1'];
            //     $id2 = $MASTER->getAction('WEBJSON')['T-ID2'];
            //     $teamid = $MASTER->getAction('WEBJSON')['T-UID'];
            //     $userObj1 = &$MASTER->getAction('userClass')->getUserById($id1);
            //     if(!$userObj1) ajaxError('학번 1을 가진 사용자는 존재하지 않습니다.');
            //     $uuid1 = $userObj1->uuid();
            //     if(!$userObj1->accept()) ajaxError('학번 1을 가진 사용자는 아직 승인되지 않았습니다.');
            //     if($MASTER->getAction('teamClass')->getTeambyUUID($uuid1)) ajaxError('학번 1을 가진 사용자는 이미 팀에 소속되어 있습니다.');

            //     if($id2 != ""){
            //     $userObj2 = &$MASTER->getAction('userClass')->getUserById($id2);
            //     if(!$userObj2) ajaxError('학번 2를 가진 사용자는 존재하지 않습니다.');
            //     $uuid2 = $userObj2->uuid();
            //     if(!$userObj2->accept()) ajaxError('학번 2를 가진 사용자는 아직 승인되지 않았습니다.');
            //     if($MASTER->getAction('teamClass')->getTeambyUUID($uuid2)) ajaxError('학번 2를 가진 사용자는 이미 팀에 소속되어 있습니다.');
            //     }else{
            //         $uuid2 = null;
            //     }
                
            //     if((int)$teamid != $teamid) ajaxError("팀 번호는 숫자 형식입니다.");
            //     if($MASTER->getAction('teamClass')->getTeambyTeamId($teamid)) ajaxError("이미 있는 팀 번호는 선택할 수 없습니다.");

            //     $MASTER->getAction('teamClass')->addTeam($uuid1, $uuid2, $teamid);
            // break;
            
            case "au-rest":
                //ACTION;
                if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('ADMISSION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD'))
                    ajaxError("선발 또는 번역 기간이 아닙니다.");
                if(preg_match('/^UID-([a-f0-9]+)$/', $moredata, $match) === FALSE) ajaxError('요청하신 데이터의 형식이 잘못되었습니다. 새로고침 후 다시 시도해 주세요.');
                $userObj = &$MASTER->getAction('userClass')->getUserByUUID($match[1]);
                $teamObj = &$MASTER->getAction('teamClass')->getTeambyUUID($userObj->uuid());
                if(!$teamObj) ajaxError('존재하지 않는 사용자입니다.');
                $teamObj->setRest(!$teamObj->getRest());
                $teamClass = &$MASTER->getAction('teamClass')->allTeam();
                $allOff = TRUE;
                for($i=0; $i<count($teamClass); $i++){
                    if(!$teamClass[$i]->getRest()){
                        $allOff = FALSE;
                        break;
                    }
                }
                if($allOff) ajaxError('모든 사용자가 자동배정에서 제외되면 안 됩니다.');
            break;
            
            default:
            ajaxError('동작 구문이 잘못된 요청입니다. 새로고침 후 다시 시도해 주세요.');
        break;
    }

    
    $display = new TableList();
    
    $reqUserTable = new TableElement("계정 신청자 목록", array("학번", "이름"));
    $reqUserTable->addContent(array(
        '<a class="smallButton ru-acpall" style="color:white">모두 승인</a>'
    ));
    
    $acpUserTable = new TableElement("번역버디 목록", array("학번", "이름", "글자수*", "자동배정", "관리자?"));
    $userList = $MASTER->getAction('userClass')->allUser();
    $teamList = $MASTER->getAction('teamClass')->allTeam();
    
    for($i = 0; $i < count($userList); $i++){
        $user = $userList[$i];
        if(!$user->accept())
        $reqUserTable->addContent(array(
            $user->id(),
            $user->name(),
            "",
            "",
            "",
            '<span id="UID-'.$user->uuid().'"><a class="smallButton ru-accept bright" style="color: white;">승인</a><a class="smallButton ru-revoke" style="color: white;">거부 및 삭제</a></span>'
        ));
        
        else{
            if($MASTER->getAction('phase')->getPhase() == $MASTER->getAction('CALCULATION_PERIOD')){
                $acpTimeDisplay = SecondToText((int)($MASTER->getAction('LETTER_TIME_CURRENCY') * ($user->getLetter() + $user->getLetterByCheat())));
                if($user->getLetterByCheat() > 0){
                    $acpTimeAdditional = $user->getLetter()." <span style=\"color: #00AB8E;\">(+".$user->getLetterByCheat().")</span>";
                }else if($user->getLetterByCheat() < 0){
                    $acpTimeAdditional = $user->getLetter()." <span style=\"color: orange;\">(".$user->getLetterByCheat().")</span>";
                }else{
                    $acpTimeAdditional = $user->getLetter();
                }
            }else{
                if($user->getLetterByCheat() > 0){
                    $acpTimeDisplay = $user->getLetter()." <span style=\"color: #00AB8E;\">(+".$user->getLetterByCheat().")</span>";
                    $acpTimeAdditional = "";
                }else if($user->getLetterByCheat() < 0){
                    $acpTimeDisplay = $user->getLetter()." <span style=\"color: orange;\">(".$user->getLetterByCheat().")</span>";
                    $acpTimeAdditional = "";
                }else{
                    $acpTimeDisplay = $user->getLetter();
                    $acpTimeAdditional = "";
                }
            }

            $warncount = (int)$MASTER->getAction('teamClass')->getTeambyUUID($user->uuid())->getWarn();
            $warnInfo = "";

            switch($warncount){
                case 0:
                break;

                case 1:
                    $warnInfo = ' <span style="color: grey;">● ○ ○</span>';
                break;
                
                case 2:
                    $warnInfo = ' <span style="color: orange;">● ● ○</span>';
                break;

                case 3:
                    $warnInfo = ' <span style="color: red;">● ● ●</span>';
                break;
                
                default:
                    $warnInfo = ' <span style="color: crimson;">● ● ● +</span>';
                break;
            }
            
            $acpUserTable->addContent(array(
                ($user->uuid() == $MASTER->getAction('FURTHER_INFO_REQUESTED_UUID') ? "<span style='color: #00AB8E; font-weight: bold; text-decoration: underline'>" : "<span>").$user->id()."</span>".$warnInfo,
                $user->name().' ('.$MASTER->getAction('teamClass')->getTeamByUUID($user->uuid())->teamid().'팀)',
                $acpTimeDisplay,
                $MASTER->getAction('teamClass')->getTeamByUUID($user->uuid())->getRest() ? "<span style=\"color: orange\">제외</span>" : "포함",
                ($user->isAdmin() ? "<b>YES</b>" : "-"),
                $_SESSION['viewonly'] ? "" : ( $user->uuid() == $MASTER->getAction('FURTHER_INFO_REQUESTED_UUID') ? "" : ('<span id="UID-'.$user->uuid().'"><a class="smallButton au-detail bright" style="color: white;">상세정보</a></span>'))
            ), ($MASTER->getAction('FURTHER_INFO_REQUESTED_UUID') == $user->uuid()));

            if($user->uuid() == $MASTER->getAction('FURTHER_INFO_REQUESTED_UUID')){

                $givewarn = $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('CALCULATION_PERIOD') ? '<span id="UID-'.$user->uuid().'"><a class="smallButton au-warnadd bright" style="color: white;">경고 +1</a></span>' : "";
                $takewarn = $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('CALCULATION_PERIOD') ? '<span id="UID-'.$user->uuid().'"><a class="smallButton au-warnsub bright" style="color: white;">경고 -1</a></span>' : $acpTimeAdditional;

                $team = &$MASTER->getAction('teamClass')->getTeambyUUID($user->uuid());
                
                $acpUserTable->addContent(array(
                    '<input type="text" size="8" placeholder="특별 자수 부여" style="font-size: 1rem; color:#888888; background: transparent;" id="U-SPE" /><span id="UID-'.$user->uuid().'"><a class="smallButton send-spe " style="color: white;" id="t-make">지급</a></span>',
                    $givewarn,
                    $takewarn,
                    '<span id="UID-'.$user->uuid().'"><a class="smallButton au-rest bright" style="color: white;">'.($team->getRest() ? '→포함' : '→제외').'</a></span>',
                    '<span id="UID-'.$user->uuid().'"><a class="smallButton au-rekey" style="color: white;">키재발급</a></span>',
                    ($user->uuid() != $_SESSION['uuid'] ? '<span id="UID-'.$user->uuid().'"><a class="smallButton au-super" style="color: white;">'.($user->isAdmin()?"권한제거":"관리자로").'</a><a class="smallButton au-remove" style="color: white;">삭제</a></span>':"본인은 설정 불가"),
                ));
                

            }
        }
    }

    if($reqUserTable->isEmpty()){
        $reqUserTable->addContent(array(
            "승인을 요청한 사용자가 없습니다.",
        ));
    }
    
    if($acpUserTable->isEmpty()){
        $acpUserTable->addContent(array(
            "승인된 번역버디가 없습니다.",
        ));
    }
    
    $acpUserTable->addContent(array(
        "* 작업으로 받은 글자수 (+특별 부여된 글자수)",
        "",
        "",
        "",
        "",
        "기준: ". $MASTER->getAction('LETTER_TIME_CURRENCY')."초/자",
    ));

    //AJAX system
    if(!$_SESSION['viewonly']){

        $oldTeamNum = array();
        $newTeamNum = array();
        $teamClass = &$MASTER->getAction('teamClass')->allTeam();
        
        for($i = 0; $i < count($teamClass); $i++){
            array_push($oldTeamNum, $teamClass[$i]->teamid());
        }
        
        $maxVal = max($oldTeamNum) + 1;
        
        for($i = 1; $i < $maxVal + 1; $i++){
            if(!in_array($i, $oldTeamNum))
                array_push($newTeamNum, $i);
        }
        

        $preTeamList = "";
        
        for($i=0; $i<count($newTeamNum); $i++){
            $preTeamList .= '<option value="'.$newTeamNum[$i].'">'.$newTeamNum[$i].'팀</option>';
        }

    }
    
    // for($i = 0; $i < count($teamList); $i++){
        
    //     $team = $teamList[$i];


    //     if($team->teamid() == $MASTER->getAction('FURTHER_INFO_REQUESTED_TID'))
    //         $teamTable->addContent(array(
    //             '<span id="TID-'.$team->teamid().'"><a class="smallButton t-warnadd bright" style="color: white;">경고 +1</a></span><span id="TID-'.$team->teamid().'"><a class="smallButton t-warnsub bright" style="color: white;">경고 -1</a></span> 현재 '.$warncount.'개',
    //             $transletter.'자 번역',
    //             SecondToText($transtime).' 소요',
    //             '',
    //             '<span id="TID-'.$team->teamid().'"><a class="smallButton t-remove" style="color: white;">팀 삭제</a></span>',
    //             '<span id="TID-'.$team->teamid().'"><a class="smallButton t-rest" style="color: white;">'.($team->getRest() ? '자동배정 활성' : '자동배정 제외').'</a></span>',
    //         ));
    // }

    if($MASTER->getAction('phase')->getPhase() == $MASTER->getAction('ADMISSION_PERIOD') && !$_SESSION['viewonly']) $display->addTable($reqUserTable);
    $display->addTable($acpUserTable);
    if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('ADMISSION_PERIOD') && !$_SESSION['viewonly']) $display->addTable($reqUserTable);
    $MASTER->setAnswerWeb($display->generate());

    saveUser($MASTER->getAction('userClass'));
    saveTeam($MASTER->getAction('teamClass'));
}

function work(){
    global $MASTER;
    $sideaction = $MASTER->getAction('WEBJSON')['sideaction'];
    //레거시코드 호환성 위해 Ajax 포맷이 member()와 좀 다름!

    if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('CALCULATION_PERIOD')){
        $table = new TableElement("번역 기간 아님", array("안내사항"));
        
        $table->addContent(array(
            "현재 번역 기간이 아닙니다. 작업은 번역 기간에만 생성 가능합니다.",
        ));
        $showTable = new TableList();
        $showTable->addTable($table);
        $MASTER->setAnswerWeb($showTable->generate());
        return;
    }

    if($sideaction != "generate" && $_SESSION['viewonly']){
        ajaxError("관리자만 접근 가능");
    }

    if($sideaction == "addwork"){
        if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD')) ajaxError("번역 기간이 아닙니다.");
        $teamid = $MASTER->getAction('WEBJSON')['team'];
        $workid = $MASTER->getAction('WEBJSON')['uuid'];
        $isgaonnuri = ($MASTER->getAction('WEBJSON')['notgaonnuri'] == "true" ? FALSE : TRUE );
        if($teamid == "-") {
            $nextTeam = $MASTER->getAction('teamClass')->getCursorAndMove();
            if(!$nextTeam) ajaxError('활성 상태인 사용자 없음');
            $teamid = $nextTeam->teamid();
        }
        if($isgaonnuri){
            if(!(is_numeric($workid) && (is_numeric($teamid)))) ajaxError('잘못된 입력');
        }else{
            if(htmlspecialchars($workid) != $workid) ajaxError('특수한 문자는 사용하지 말아 주세요');
            if(mb_strlen($workid, 'utf-8') >= 10) ajaxError('작업명은 9글자 이하로');
        }
        if(!$MASTER->getAction('teamClass')->getTeambyTeamId($teamid)) ajaxError('존재하지 않는 팀');
        if($MASTER->getAction('workClass')->findWorkById($workid)) ajaxError('이미 존재하는 고유코드');
        $MASTER->getAction('workClass')->makeNewWork($workid, $teamid, $isgaonnuri);
    }else if($sideaction == "remove"){
        //ACTION 작업 삭제
        if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('CALCULATION_PERIOD')) ajaxError("번역 또는 정산 기간이 아닙니다.");
        $workid = str_replace("WID-", "", $MASTER->getAction('WEBJSON')['uuid']);
        if(!$MASTER->getAction('workClass')->findWorkById($workid)) ajaxError('존재하지 않는 업무');
        if($MASTER->getAction('workClass')->findWorkById($workid)->isEnd()) $MASTER->getAction('userClass')->getUserByUUID($MASTER->getAction('workClass')->findWorkById($workid)->endid())->giveLetter(-($MASTER->getAction('workClass')->findWorkById($workid)->getLetter()));
        $MASTER->getAction('workClass')->dropWork($workid);
    }else if($sideaction == "download"){
        $timestamp = $MASTER->getAction('WEBJSON')['timestamp'];
        if(!is_numeric($timestamp)) ajaxError("타임스탬프는 Unix Timestamp 형식입니다.");
        
        $_DOCUMENT_ROOT_ = $_SERVER['DOCUMENT_ROOT'];
        $_fp = fopen("$_DOCUMENT_ROOT_/web/csv/".$timestamp.".csv", 'w');

        $workClass = &$MASTER->getAction('workClass')->allWork();

        $line = "WORK UNIQUE ID, TEAM, TRANSLATOR ID, ELAPSED TIME, KOREAN CHARACTER\r\n";
        fwrite($_fp, $line); 

        for($i = 0; $i < count($workClass); $i++){
            $work = $workClass[$i];
            if(!$work->isEnd()) continue;
            
            $line =
            $work->workid().","
            .$work->getTeam().","
            .$MASTER->getAction('userClass')->getUserByUUID($work->endid())->id().","
            .$work->getPeriod().","
            .$work->getLetter()."\r\n";
            
            fwrite($_fp, $line);
        }

        fclose($_fp);
        chmod("$_DOCUMENT_ROOT_/web/csv/".$timestamp.".csv", 0777);

        header('HTTP/1.1 200 '.$message);
        die(json_encode(array('redir'=>($MASTER->getAction('WEB_ROOT'))."web/csv/".$timestamp.".csv")));
        $MASTER->endPrimary();
        return;
    }

    if($MASTER->isError()){
        $table = new TableElement("오류 발생!", array("오류 코드"));
        
        $table->addContent(array(
            $MASTER->getError(),
        ));
        $showTable = new TableList();
        $showTable->addTable($table);
        $MASTER->setAnswerWeb($showTable->generate());
        return;
    }

    $display = new TableList();
    $workTable = new TableElement("번역 작업 목록", array("고유번호", "담당", "상태", "경과시간", "글자수", "관리"));
    $workList = $MASTER->getAction('workClass')->allWork();
    $teamList = $MASTER->getAction('teamClass')->allTeam();
    $autoTeamNum = ($MASTER->getAction('teamClass')->getCursorAndShow() ? $MASTER->getAction('userClass')->getUserByUUID($MASTER->getAction('teamClass')->getCursorAndShow()->uuids()[0])->name() : "X");

    $preTeamList = "";
    for($i=0; $i<count($teamList); $i++){
        $preTeamList .= '<option value="'.$teamList[$i]->teamid().'">'.$MASTER->getAction('userClass')->getUserByUUID($teamList[$i]->uuids()[0])->name().'</option>';
    }

    if(!$_SESSION['viewonly']){
        if($MASTER->getAction('phase')->getPhase() == $MASTER->getAction('TRANSLATION_PERIOD')){
            //AJAX system
            $workTable->addContent(array(
                '<input type="text" placeholder="고유번호..." style="font-size: 1rem; color:#888888; background: transparent;" id="NEW-UUID" size="8" />'
                .' <input type="checkbox" name="notgaonnuri" id="notgaonnuri" value="no"> 가온누리 아님',
                '<select id="NEW-TEAM" class="select" style="color: #888888"><option value="-">자동('.$autoTeamNum.')</option>'.$preTeamList.'</select>',
                '<a class="smallButton" style="color: white;" id="NEW-SEND">생성</a>',
            ));
        }else if($MASTER->getAction('phase')->getPhase() == $MASTER->getAction('CALCULATION_PERIOD')){
            $workTable->addContent(array(
                '번역 기간이 종료되어 업무 조회와 삭제만 가능합니다.'
            ));
        }
    }

    for($i = count($workList) - 1; $i >= 0; $i--){
        $work = $workList[$i];
        $workTable->addContent(array(
            ($work->isGaonnuri() ? "<a href=\"http://gaonnuri.ksain.net/xe/".$work->workid()."\" target=\"_blank\">".$work->workid()."</a>" : "<span style=\"color: #007bff; font-style: italic;\">".$work->workid()."</span>" ),
            (
                $work->isEnd() ?
                    $MASTER->getAction('userClass')->getUserByUUID($work->endid())->name().' ('.$work->getTeam().'팀)' :
                    $MASTER->getAction('userClass')->getUserByUUID($MASTER->getAction('teamClass')->getTeambyTeamId($work->getTeam())->uuids()[0])->name().' ('.$work->getTeam().'팀)'
            ),  
            ($work->isEnd() ? ($work->getPeriod() > $MASTER->getAction('LATE_THRESHOLD') ? '<span style="color: orange;">늦게 완료</span>' : '<span style="color: #00AB8E;">✓</span>') : ($work->getPeriod() > $MASTER->getAction('LATE_THRESHOLD') ? '<span style="color: red;">재배정 필요</span>' : '대기 중') ),
            ($work->isEnd() ? SecondToText($work->getPeriod()) : '<i>'.SecondToText($work->getPeriod()).'</i>' ),
            ($work->isEnd() ? $work->getLetter() : "-"),
            $_SESSION['viewonly'] ? "-" : '<span id="WID-'.$work->workid().'">'.'<a class="smallButton w-show bright" style="color: white;" displaytext="'.date("Y년 m월 d일, H시 i분에 배정했던 작업입니다.", $work->getStart()).'">배정일</a><a class="smallButton w-remove" style="color: white;">삭제</a></span>'
        ));
    }
    
    if(count($workList) == 0){
        $workTable->addContent(array(
            "생성된 번역 작업이 없습니다.",
        ));
    }

    $preTeamList = "";
    //<option value="1">1팀</option>
    $teamClass = &$MASTER->getAction('teamClass')->allTeam();
    for($i=0; $i<count($teamClass); $i++){
        $teamnum = $teamClass[$i]->teamid();
        $preTeamList = $preTeamList.'<option value="'.$teamnum.'">'.$teamnum.'팀</option>';
    }
    
    $display->addTable($workTable);
    $MASTER->setAnswerWeb($display->generate());
    saveUser($MASTER->getAction('userClass'));
    saveWork($MASTER->getAction('workClass'));
    saveTeam($MASTER->getAction('teamClass'));
    $MASTER->endPrimary();
}

function ajaxError($msg){
    global $MASTER;
    $message = urlencode($msg);
    header('HTTP/1.1 400 '.$message);
    $MASTER->endPrimary();
    die(json_encode(array('message' => $message, 'code' => 400)));
}

function push_log($message){
    $_DOCUMENT_ROOT_ = $_SERVER['DOCUMENT_ROOT'];
    $_fp = fopen("$_DOCUMENT_ROOT_/report/log.dat", 'a');

    $date = new DateTime("now", new DateTimeZone('Asia/Seoul'));
    $log = ($date->format('Y-m-d H:i:s'))." ";

    $log = "$log | $message";
    fwrite($_fp, $log."\n");
    fclose($_fp);
    return TRUE;
}

    $logmsg = "";
    foreach($_REQUEST as $key=>$value){
        $logmsg = $logmsg . "$key=>$value;";
    }

    push_log(($MASTER->isError() ? "FAIL" : "COMP")." | ADMINACT=>".$_SESSION['uuid']."".$logmsg);
    
    echo $MASTER->getAnswer();
    $MASTER->endPrimary();

?>