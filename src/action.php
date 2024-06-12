<?php
    // This file is the core of Kakaotalk Chatbot interface.
    // To look the web dashboard part, go to /src/web/
    //
    // If user request is valid, then the code decides what to do
    // based on user's desired 'action'.
    // to look detailed actions, see below.

    ob_start();
    require_once('structure.php');
    
    $MASTER = new MasterAction();
    
    if($MASTER->answerType() == "ILLEGAL"){
        header('Location: web/home/index.php');
        ob_end_flush();
        exit;
    }
    
    $MASTER->showHeader();
    
    if($SHUTDOWN_SERVER && $_REQUEST['DEBUG'] != "1" && $MASTER->getInput('action') != "retry"){
        $MASTER->setAnswer("Sofia 서버가 정지되었습니다.\n서버 정지 사유: $SHUTDOWN_REASON\n서버 가동 일정: $SHUTDOWN_TERM",
        array("서버 연결 재시도", array("무슨 뜻인가요?", "서버 다운됨 메시지")));
        echo $MASTER->getAnswer();
        exit;
    }
    
    require_once('const.php');
    if($MASTER->getInput('action') == "INITIATION") INITIATION();
    
    //유효한 UUID인지 검증, 등록되어 있는지 정보를 $MASTER->getAction('is_user')로 검증
    //관리자인지는 'is_admin'으로 검증
    
    if($MASTER->getInput('uuid') == ""){
        $MASTER->setError("INVALID_UUID_RECEIVED", TRUE);
    }else if($MASTER->getAction('userClass')->getUserByUUID($MASTER->getInput('uuid'))){
        $MASTER->setAction('is_user', TRUE);
        if($MASTER->getAction('userClass')->getUserByUUID($MASTER->getInput('uuid'))->isAdmin()){
            $MASTER->setAction('is_admin', TRUE);
        }else if($MASTER->getInput('uuid') == $MASTER->getAction('DEV_ID')){
            $MASTER->setAction('is_admin', TRUE);
        }
        else{
            $MASTER->setAction('is_admin', FALSE);
        }
    }else{
        $MASTER->setAction('is_user', FALSE);
    }

    
    
    if(!($MASTER->isError())){
        switch($MASTER->getInput('action')){

                case "getmyinfo":
                    //현재 내 팀과 내 봉사정보, 일 정보 읽어오기, 직원용, Phase 2
                    //어떤 정보 읽어올지 선택 가능함
                    getmyinfo();
                break;

                case "gettoken":
                    gettoken();
                break;

                case "retoken":
                    retoken();
                break;

                case "givenwork":
                    //현재 내 일 정보 읽어오기, 직원용, Phase 2
                    givenwork();
                break;
                
                case "requestperm":
                    //번역버디 인증, 전원용, Phase 1
                    requestperm();
                break;

                case "derequestperm":
                    //번역버디 인증취소, 전원용, Phase 1
                    derequestperm();
                break;

                case "reportwork":
                    //번역버디 번역 완료 보고, 직원용, Phase 2
                    reportwork();
                break;

                case "dictionary":
                    //번역버디 번역용 공식용어 확인기능, 전원용, ANYTIME USE
                    //번역해야 할 텍스트 넣어주면 유용할 단어 전부 뱉음
                    dictionary();
                break;

                case "retry":
                    retry();
                break;

                case "test":
                    test();
                break;

                default:
                    $MASTER->setError("INVALID_ACTION_RECEIVED", TRUE);
                break;
            }
        }

    function requestperm(){
        //FLOWIN id-학번 / name-이름
        global $MASTER;

        if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('ADMISSION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD')){
            $MASTER->setError("지금은 선발/번역기간이 아닙니다. 이 기능을 사용할 수 없습니다.");
            return;
        }

        $userClass = &$MASTER->getAction('userClass');
        $userObject = $userClass->getUserByUUID($MASTER->getInput('uuid'));
        if($userObject){
            $MASTER->setError("이미 가입을 요청했거나, 또는 이미 가입된 상태입니다.");
            return;
        }
        else{
            if($MASTER->getInput('id')=="" || $MASTER->getInput('name')==""){
                $MASTER->setError("학번 또는 이름이 누락되었습니다.");
                return;
            }if(mb_strlen($MASTER->getInput('name')) > 4 || mb_strlen($MASTER->getInput('name')) < 2 ){
                $MASTER->setError("이름은 2자 이상이며 4자 이하로 입력해야 합니다.");
                return;
            }

            
            if(count(explode('-', $MASTER->getInput('id'))) != 2 || !is_numeric(explode('-', $MASTER->getInput('id'))[0]) ||
                !is_numeric(explode('-', $MASTER->getInput('id'))[1]) || mb_strlen(explode('-', $MASTER->getInput('id'))[0]) != 2 ||
                mb_strlen(explode('-', $MASTER->getInput('id'))[1]) != 3 ){
                $MASTER->setError("유효하지 않은 학번 양식입니다. 학번은 XX-XXX꼴로 입력되어야 하며 X는 0-9까지의 숫자여야 합니다.");
                return;
            }
            
            
            
            if($MASTER->getAction('userClass')->getUserById($MASTER->getInput('id'))){
                $MASTER->setError("이미 요청이 접수되었거나 등록된 사용자의 학번입니다. 만약 이미 등록된 학번의 주인이 본인이라면 번역버디장에게 문의하시기 바랍니다.");
                return;
            }

            $userClass->addUser($MASTER->getInput('uuid'), $MASTER->getInput('id'), $MASTER->getInput('name'));
        }
        
        $MASTER->setAnswer("번역버디 권한을 신청했습니다. 관리자의 승인을 받으면 번역기간부터 활동이 가능합니다.\n학번: ".($MASTER->getInput('id'))."\n이름: ".($MASTER->getInput('name')),
        array('홈 메뉴', '가입요청 취소하기'));

    } 

    function getmyinfo(){

        global $MASTER;
        $userClass = &$MASTER->getAction('userClass');
        $userObject = $userClass->getUserByUUID($MASTER->getInput('uuid'));
        if(!$userObject){
            $MASTER->setAnswer("현재 가입 요청을 하지 않았습니다.", array('가입 요청하기', '홈 메뉴'));
            return;
        }else if(!$userObject->accept()){
            $MASTER->setAnswer("현재 귀하의 가입 요청이 심사 중입니다. 승인되면 번역버디라는 안내가 추가됩니다. 거부되면 가입 요청이 자동으로 삭제됩니다.", array('가입요청 취소하기', '홈 메뉴'));
            return;
        }else{
            $rtn = "안녕하세요, ".$userObject->id()." ".$userObject->name()."님, ".($userObject->isAdmin() ? "당신은 관리자입니다." : "당신은 승인된 번역버디입니다.")."\n";
            $myTeam = $MASTER->getAction('teamClass')->getTeambyUUID($userObject->uuid());
            $rtn .= "\n";
            $rtn .= "\n번역한 글자 수: ".$userObject->getLetter()." (".($userObject->getLetterByCheat() >= 0 ? "+" : "").$userObject->getLetterByCheat().")";
            $rtn .= "\n봉사 시간(추정): ".SecondToText((int)($MASTER->getAction('LETTER_TIME_CURRENCY') * ($userObject->getLetter() + $userObject->getLetterByCheat())));
            $rtn .= "\n받은 경고: ".(int)$myTeam->getWarn()."개";
            $rtn .= "\n* 작업으로 받은 글자수 (+특별 부여된 글자수)";
            $MASTER->setAnswer($rtn, array('홈 메뉴', '토큰 조회하기'));
        }
    }
    
    function gettoken(){
        global $MASTER;
        //eigencode, id, letter
        if(!is_user()) return;
        $userClass = &$MASTER->getAction('userClass');
        $userObject = $userClass->getUserByUUID($MASTER->getInput('uuid'));
        if(!$userObject->accept()){
            $MASTER->setError("승인되지 않은 사용자입니다.");
            return;
        }
        
        $rtn = "이 토큰을 이용하면 Sofia 페이지에 로그인이 가능합니다. 아래의 토큰을 복사 후 붙여넣으세요.\n";
        $rtn .= $userObject->getKey();
        
        $MASTER->setAnswer($rtn, array('홈 메뉴', '토큰 도움말 보기', '토큰 재발급하기'));
        
    }
    
    function retoken(){
        global $MASTER;
        if(!is_user()) return;

        $userObj = $MASTER->getAction('userClass')->getUserByUUID($MASTER->getInput('uuid'));
        if(!$userObj->accept()){
            $MASTER->setError("승인되지 않은 사용자입니다.");
            return;
        }
        
        if($MASTER->getInput('sure') != "예, 재발급합니다."){
            $MASTER->setAnswer("네, 재발급 절차를 취소했습니다.", array('홈 메뉴'));
            return;
        }
        
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

        $rtn = "재발급되었습니다.\n";
        $rtn .= $userObj->getKey();
        
        $MASTER->setAnswer($rtn, array('홈 메뉴'));
    }

    function reportwork(){
        global $MASTER;
        //eigencode, id, letter
        if(!is_user()) return;

        if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD')){
            $MASTER->setError("지금은 번역기간이 아닙니다. 이 기능을 사용할 수 없습니다.");
            return;
        }

        $workid = $MASTER->getInput('eigencode');
        $letter = str_replace("자", "", $MASTER->getInput('letter'));

        // if(!is_numeric($workid)){
        //     $MASTER->setError('작업 번호는 숫자여야 합니다.');
        //     return;
        // }

        if(!is_numeric($letter)){
            $MASTER->setError('글자 수는 숫자여야 합니다.');
            return;
        }

        // 존재 작업?
        $workObj = &$MASTER->getAction('workClass')->findWorkById($workid);
        
        if(!$workObj){
            $MASTER->setError('존재하지 않는 번역 작업입니다.');
            return;
        }

        if($workObj->isEnd()){
            $MASTER->setError('이미 끝난 번역 작업입니다.');
            return;
        }
        
        if(!$MASTER->getAction('teamClass')->getTeambyUUID($MASTER->getInput('uuid'))){
            $MASTER->setError('팀에 소속되지 않아 번역 보고에 참여할 수 없습니다.');
            return;
        }
        
        if($workObj->getTeam() != $MASTER->getAction('teamClass')->getTeambyUUID($MASTER->getInput('uuid'))->teamid()){
            $MASTER->setError('본인의 팀에 배정된 업무가 아닙니다.');
            return;
        }
        
        //ACTION
        {
            $workObj->setEnd($MASTER->getInput('uuid'));
            $workObj->setLetter($letter);
            $MASTER->getAction('userClass')->getUserByUUID($MASTER->getInput('uuid'))->giveLetter($letter);
            $addMsg = "";
            if($workObj->getPeriod() > $MASTER->getAction('LATE_THRESHOLD')){
                $MASTER->getAction('teamClass')->getTeambyUUID($MASTER->getInput('uuid'))->addWarn(1);
                $totalWarn = $MASTER->getAction('teamClass')->getTeambyUUID($MASTER->getInput('uuid'))->getWarn();
                $addMsg = "\n\n[경고 안내] 이 작업은 기한보다 늦게 제출되었습니다. 자동으로 귀하의 팀에 경고 1회가 등록되었습니다(현재 ".$totalWarn."개). 주의하세요.";
            }
        }
        
        $MASTER->setAnswer('번역 보고가 완료되었습니다. 현재까지 '.$MASTER->getAction('userClass')->getUserByUUID($MASTER->getInput('uuid'))->getLetter().'자를 번역했습니다.'.$addMsg, array('홈 메뉴'));

    }

    function derequestperm(){
        
        global $MASTER;

        if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('ADMISSION_PERIOD') && $MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD')){
            $MASTER->setError("지금은 선발기간이나 번역기간이 아닙니다. 이 기능을 사용할 수 없습니다.");
            return;
        }
        
        if($MASTER->getInput('sure') != "네, 취소합니다."){
            $MASTER->setAnswer("네, 알겠습니다. 가입 요청을 철회하지 않겠습니다.", array('홈 메뉴'));
            return;
        }

        $userClass = &$MASTER->getAction('userClass');
        $userObject = $userClass->getUserByUUID($MASTER->getInput('uuid'));
        if(!$userObject){
            $MASTER->setError("가입 요청을 한 적이 없습니다. 취소가 불가능합니다.");
            return;
        }else if($userObject->accept()){
            $MASTER->setError("승인된 번역버디는 가입 취소가 불가합니다. 관리자에게 문의하세요.");
            return;
        }
        
        $userClass->dropUser($MASTER->getInput('uuid'));
        
        $MASTER->setAnswer("요청이 삭제되었습니다.",
        array('홈 메뉴', '가입 요청하기'));

    } 

    function givenwork(){
        global $MASTER;
        if(!is_user()) return;
        if($MASTER->getAction('phase')->getPhase() != $MASTER->getAction('TRANSLATION_PERIOD')){
            $MASTER->setError("지금은 번역기간이 아닙니다. 이 기능을 사용할 수 없습니다.");
            return;
        }
        $workList = $MASTER->getAction('workClass')->findAllWorkByUser($MASTER->getInput('uuid'), $MASTER->getAction('teamClass'));
        $rtn = "현재 해결해야 하는 업무는 다음과 같습니다.";
        $c = 0;
        for($i=0; $i<count($workList); $i++){
            $work = $workList[$i];
            if($work->isEnd()) continue;
            $c++;
            $rtn .= "\n\n";
            if($work->isGaonnuri()) $rtn .= "http://gaonnuri.ksain.net/xe/".$work->workid()." 를 번역해야 합니다. 배정된 지 ".SecondToText($work->getPeriod())." 지났습니다.";
            else $rtn .= "<".$work->workid().">을(를) 번역해야 합니다. 가온누리 외 번역입니다. 배정된 지 ".SecondToText($work->getPeriod())." 지났습니다.";
            if($work->getPeriod() > $MASTER->getAction('LATE_THRESHOLD')) $rtn .= " [마감기한이 지난 업무이므로 임의로 삭제될 수 있습니다. 번역버디장과 연락하세요.]";
        }

        if($c != 0){
            $MASTER->setAnswer($rtn, array('홈 메뉴', '끝난 번역 보고하기'));
        }else{
            $MASTER->setAnswer("현재 배정된 업무가 없습니다.", array('홈 메뉴'));
        }
    }

    function dictionary(){
        global $MASTER;
        $rawtext = $MASTER->getInput('text');
        $text = preg_replace('/\s/', '', $rawtext);
        if(mb_strlen($text) > $MASTER->getAction('DICT_ACCEPT_MAX_LENGTH')){
            $MASTER->setError(($MASTER->getAction('DICT_ACCEPT_MAX_LENGTH')).'자가 넘어 분석에 실패했습니다. 더 적은 글자수로 다시 시도해 주세요.');
            return;
        }
        $matchPair = $MASTER->getAction('dictClass')->getAllMatch($text);

        $rtn = "[분석 결과] \n".
        "분석한 텍스트: \"".mb_substr($rawtext, 0, 8)." ... ".mb_substr($rawtext, -9, 9)."\"\n".
        "텍스트의 길이(전체): ".mb_strlen($MASTER->getInput('text'))."자\n".
        "텍스트의 길이(공백/개행 등 제외): ".mb_strlen($text)."자\n".
        "번역 가이드라인에서 찾은 단어: ".count($matchPair)."개\n".
        "\n";

        if(count($matchPair) == 0){
            $rtn .= "제공하신 텍스트에서 번역 가이드라인에 포함된 단어를 찾지 못했습니다.";
        }else{
            for($i=0; $i<count($matchPair); $i++){
                $mel = $matchPair[$i];
                $fromtxt = "";
                for($j=0; $j<count($mel->from()); $j++){
                    $word = $mel->from()[$j];
                    $fromtxt .= $word;
                    if( $j == count($mel->from())-1 ) break;
                    $fromtxt .= ", ";
                }
                $rtn .= $fromtxt." > ".$mel->to();
                $rtn .= "\n";
            }
            $rtn .= "\n추가해야 하거나 개정해야 할 번역 가이드라인이 있을 경우 번역버디장에게 연락하세요.";
        }

        $MASTER->setAnswer($rtn, array('홈 메뉴', array('다시 찾기', "번역 도우미")));
    }
   
    function push_log($message){
        global $MASTER;
        $_DOCUMENT_ROOT_ = $_SERVER['DOCUMENT_ROOT'];
        $_fp = fopen("$_DOCUMENT_ROOT_/report/log.dat", 'a');

        $date = new DateTime("now", new DateTimeZone('Asia/Seoul'));
        $log = ($date->format('Y-m-d H:i:s'))." ";

        $log = "$log | $message";
        fwrite($_fp, $log."\n");
        fclose($_fp);
        return TRUE;
    }

    function retry(){
        global $SHUTDOWN_SERVER, $SHUTDOWN_REASON, $SHUTDOWN_TERM, $MASTER;
        if($SHUTDOWN_SERVER){
            $MASTER->setAnswer("Sofia 서버가 정지되었습니다.\n서버 정지 사유: $SHUTDOWN_REASON\n서버 가동 일정: $SHUTDOWN_TERM",
                array("서버 연결 재시도", array("무슨 뜻인가요?", "서버 다운됨 메시지")));
        }else{
            $MASTER->setAnswer("Sofia 서버가 작동하고 있습니다.", array('홈 메뉴'));
        }
    }

    function test(){
        global $MASTER;
        sleep(2);
        $MASTER->setAnswer("당신은 Sofia에 숨겨진 놀라운 기능을 찾았습니다! 이 기능은 2초 뒤 이 메시지를 회신하는 기능입니다. Sofia 서버가 살아 있는지 개발자가 점검하는 용도입니다.", array('개발자 정보', '홈 메뉴'));
    }

    function is_user(){
        //ACTION 등록되어 있는지 확인하는 함수
        //FLOWOUT 등록되어 있음-TRUE / 등록 안됨-FALSE
        global $MASTER;

        if(!$MASTER->getAction('is_user')){
            $MASTER->setAnswer("번역버디 인증이 되지 않았습니다. 작업이 취소되었습니다.",
            array('홈 메뉴', '가입 요청하기'));
            return FALSE;
        }else{
            return TRUE;
        }
    }

    function is_admin(){
        //ACTION 등록되어 있는지 확인하는 함수
        //FLOWOUT 등록되어 있음-TRUE / 등록 안됨-FALSE
        global $MASTER;

        if(!$MASTER->getAction('is_admin')){
            $MASTER->setAnswer("관리자가 아닙니다. 권한이 없습니다. 작업이 진행되지 않았습니다.",
            array('홈 메뉴'));
            return FALSE;
        }else{
            return TRUE;
        }
    }

    function recordtime(){
        $_DOCUMENT_ROOT_ = $_SERVER['DOCUMENT_ROOT'];
        $_fp = fopen("$_DOCUMENT_ROOT_/data/lastRefresh.dat", 'w');
        $date = new DateTime("now", new DateTimeZone('Asia/Seoul'));
        fwrite($_fp, serialize($date));
        fclose($_fp);
    }

    function INITIATION(){
        //NOTICE [[[ 관리자 명령으로만 실행되는 함수 ]]]
        //NOTICE 전체 정보 초기화(단어사전 포함), 기간은 선발기간으로 초기화
        //NOTICE 말 그대로 모든 정보 날아감, 복구 불가
        //NOTICE PARSE_FAIL_XXXX 오류 발생했을 때 쓸 것
        //NOTICE 사용 전 반드시 /data/ 폴더 아래 전부 백업해 둘 것
        //FLOWIN sure-정말?
        global $MASTER;
        
        if(!$MASTER->getAction('ALLOW_INITIATION')){
            $MASTER->setError("PHP_ALLOW_INITIATION_TAG_INVALID", TRUE);
        }else{
            $MASTER->setAction('userClass', new UserList());
            $MASTER->getAction('userClass')->addUser($MASTER->getInput('uuid'), "00-000", "관리자");
            $MASTER->getAction('userClass')->getUserByUUID($MASTER->getInput('uuid'))->setAdmin(TRUE);
            $MASTER->getAction('userClass')->getUserByUUID($MASTER->getInput('uuid'))->setAccept(TRUE);
            $MASTER->setAction('teamClass', new TeamList());
            $MASTER->setAction('workClass', new WorkList());
            $MASTER->setAction('phase', new Phase($MASTER->getAction('INACTIVE_PERIOD')));

            saveUser($MASTER->getAction('userClass'));
            saveTeam($MASTER->getAction('teamClass'));
            saveWork($MASTER->getAction('workClass'));
            savePhase($MASTER->getAction('phase'));

            $MASTER->setAnswer("모두 초기화되었습니다.\n\n초기화로 인해 이 계정을 제외한 다른 모든 계정의 관리자 권한이 해제되었습니다. 이 계정의 학번/이름이 [00-000/관리자]로 변경되었습니다.",
            array(array('처음으로', '처음으로')));
        }

        $MASTER->endPrimary();
        push_log($MASTER->getAllInput());
        echo $MASTER->getAnswer();
        exit;
    }
    
    if(!$MASTER->isError()){
        saveUser($MASTER->getAction('userClass'));
        saveTeam($MASTER->getAction('teamClass'));
        saveWork($MASTER->getAction('workClass'));
        savePhase($MASTER->getAction('phase'));
    }
    
    $MASTER->endPrimary();
    push_log(($MASTER->isError() ? "FAIL" : "COMP")." | ADMINACT=>".$_SESSION['uuid']."".$MASTER->getAllInput());
    echo $MASTER->getAnswer();

    ob_end_flush();

?>