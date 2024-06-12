<?php
    // This file defines every data abstraction of Sofia the Chatbot.

    // SHUTDOWN variable is no longer used. Always set $SHUTDOWN_SERVER as FALSE.
    // $SHUTDOWN_SERVER => 서버를 정지하는가? (TRUE=정지/FALSE=작동)
    // $SHUTDOWN_REASON => 왜 정지하는가?
    // $SHUTDOWN_TERM => 얼마나 오래 정지하는가?
    $SHUTDOWN_SERVER = FALSE;
    $SHUTDOWN_REASON = "패치 진행";
    $SHUTDOWN_TERM = "2020-03-25 오후 8시 35분";

    $WARNING_SHOW_THRESHOLD = 2;
    
    class MasterAction{
        // The core class.
        // Every request to Sofia server is converted into MasterAction class,
        // which means that no matter you made a request via web panel or Kakaotalk,
        // every input parameters and output format are generated with this class.
        //
        // This class works similar to the singleton pattern, but not exactly.
        // Hence, this class is higher form of $_REQUEST.
        // (You can think that this class works like something like $_KAKAOTALK_AND_WEB_REQUEST)
        //
        // Always use this class as: `$MASTER = new MasterAction();`
        // If $MASTER is not defined, `valid.php` will kill the session.

        private $_INPUT = array();
        private $_ERROR = array();
        private $_ANSWERTYPE = "";
        private $_RETURN = FALSE;
        private $_ACTION = array();
        private $_PRIMLOCK = FALSE;

        // $DEBUG_ALLOW => action.php의 디버그 기능 활성화? (FALSE 권장; 보안이슈)
        private $DEBUG_ALLOW = FALSE;

        function __construct($usage){
            if("KAKAOTALK" == $_SERVER['HTTP_FROMSERVER']){
                $_entityBody = json_decode(file_get_contents('php://input'), TRUE);
                $this->_INPUT['uuid'] = $_entityBody['userRequest']['user']['id'];
                foreach($_entityBody['action']['params'] as $myparam=>$myval) $this->_INPUT[$myparam] = trimNewline($myval);
                $this->_ANSWERTYPE = "SERVER";
            }else if( ($_REQUEST['DEBUG'] == "1") && $this->DEBUG_ALLOW ){
                $this->_INPUT['uuid'] = "000000000000000000000000000000000000000000000000000000000000000000";
                foreach($_REQUEST as $key=>$value) $this->_INPUT[$key] = trimNewline($value);
                $this->_ANSWERTYPE = "DEBUG";
            }else if($usage == "WEB"){
                foreach($_REQUEST as $key=>$value) $this->_INPUT[$key] = trimNewline($value);
                $this->_ANSWERTYPE = "WEB";
            }else{
                $this->_ANSWERTYPE = "ILLEGAL";
            }
        }

        function answerType(){
            return $this->_ANSWERTYPE;
        }

        function showHeader(){
            header('Content-Type: '.($_REQUEST['DEBUG'] == "1" && $this->DEBUG_ALLOW ? "text/plain" : "application/json" ).'; charset=utf-8');
        }

        function getInput($inputKey){
            return $this->_INPUT[$inputKey];
        }

        function getAllInput(){
            $rtn = "";
            foreach($this->_INPUT as $key=>$value){
                if($this->_INPUT['action'] == "dictionary" && $key == "text"){
                    $purtxt = trimNewline($value);
                    $purtxt = str_replace(";", "", $purtxt);
                    $rtn = $rtn . "$key=>".mb_strlen($purtxt)."/".mb_substr($purtxt, 0, 16).";";
                }
                else
                    $rtn = $rtn . "$key=>$value;";
            }
            return $rtn;
        }

        function setError($errorValue, $isFatal=FALSE){
            //FLOWIN errorValue: 내용 / isFatal: True-치명적, False-안 치명적
            array_push($this->_ERROR, array($errorValue,$isFatal));
            $this->setAnswer($this->getError(), array(array('홈 메뉴', '홈 메뉴')));
        }

        function getError(){
            $rtn = "";
            $level = FALSE;
            for($i=0; $i<count($this->_ERROR); $i++){
                if($this->_ERROR[$i][1] && !$level){
                    $level = TRUE;
                    if($this->_ANSWERTYPE != "WEB")
                        $rtn = "치명적인 오류: ";
                }else{
                    if($this->_ANSWERTYPE != "WEB")
                        $rtn = "작업이 반영되지 못했습니다: ";
                }
                if($level){
                    //FATAL situation
                    if($this->_ERROR[$i][1]) $rtn = $rtn.($this->_ERROR[$i][0])." ";
                }else{
                    //Non-fatal situation
                    $rtn = $rtn.($this->_ERROR[$i][0])." ";
                }
            }

            return trim($rtn);
        }

        function isError(){
            return (count($this->_ERROR) > 0);
        }

        function setAction($actionName, $actionValue){
            $this->_ACTION[$actionName] = $actionValue;
        }
        
        function getAction($actionName){
            return $this->_ACTION[$actionName];
        }
        
        function setAnswer($text="", $quickArray=array()){
            //FLOWIN text: 본문
            //FLOWIN quickArray: (보여줄 메시지, 연결방법(message, block), 터치출력메시지, (옵션)블록ID);
            $this->_RETURN = array();
            $this->_RETURN['version'] = "2.0";
            $this->_RETURN['template'] = array();
            $this->_RETURN['template']['outputs'] = array();
            array_push($this->_RETURN['template']['outputs'], array());
            $this->_RETURN['template']['outputs'][0]['simpleText'] = array();
            $this->_RETURN['template']['outputs'][0]['simpleText']['text'] = $text;
            
            if(count($quickArray)>0){
                $this->_RETURN['template']['quickReplies'] = array();
            }
    
            for($i=0; $i<count($quickArray); $i++){
                array_push($this->_RETURN['template']['quickReplies'], array());
                if(!is_array($quickArray[$i])) $quickArray[$i] = array($quickArray[$i],$quickArray[$i]);
                $this->_RETURN['template']['quickReplies'][$i]['label'] = $quickArray[$i][0];
                $this->_RETURN['template']['quickReplies'][$i]['action'] = 'message';
                $this->_RETURN['template']['quickReplies'][$i]['messageText'] = $quickArray[$i][1];
                if($quickArray[1] == "block") $this->_RETURN['template']['quickReplies'][$i]['blockId'] = '0';
            }
            $this->_RETURN = json_encode($this->_RETURN, JSON_UNESCAPED_UNICODE);
        }

        function setAnswerWeb($text, $is_raw=FALSE){
            if($is_raw){
                $this->_RETURN = $text;
            }else{
                $nowDate = new DateTime("now", new DateTimeZone('Asia/Seoul'));
                $timevar = $nowDate->getTimestamp();
                $this->_RETURN = json_encode(array('back'=>$text, 'time'=>$timevar));
            }
        }

        function getAnswer(){
            return $this->_RETURN;
        }

        function setPrimary(){
            //한 번에 여러 프로세스가 가동되면 문제가 발생할 수 있기 때문에(파일 입/출력 꼬임) 그를 방지하기 위한 함수 집합
            $this->_idleList = array(1000000, 800000, 600000, 400000, 200000);
            $this->_count = 0;
            $this->_DOCUMENT_ROOT_ = $_SERVER['DOCUMENT_ROOT'];
            $this->_fp = fopen($this->_DOCUMENT_ROOT_."/data/indic.loc", 'w');
            $this->_got_lock = true;
            while (!flock($this->_fp, LOCK_EX | LOCK_NB, $this->_wouldblock)) {
                if ($this->_wouldblock && $this->_count < count($this->_idleList)) {
                    usleep($this->_idleList[$this->_count]);
                    $this->_count++;
                } else {
                    $this->_got_lock = false;
                    break;
                }
            }
    
            if ($this->_got_lock) {
                //운영권한 획득 성공
                $this->_PRIMLOCK = &$this->_fp;
                return TRUE;
            }else{
                //운영권한 획득 실패
                fclose($this->_fp);
                return FALSE;
            }
        }

        function endPrimary(){
            return ($this->_PRIMLOCK ? fclose($this->_PRIMLOCK) : FALSE);
        }

        function isPrimary(){
            return ($this->_PRIMLOCK ? TRUE : FALSE);
        }

    }
    
    class UserList{
        //사용자 목록 저장
        private $_userlist = array();

        public function addUser($uuid, $id, $name){
            while(TRUE){
                $key = bin2hex(random_bytes(32));
                $dup = FALSE;
                for($i=0; $i<count($_userlist); $i++){
                    if($_userlist[$i]->getKey() == $key){
                        $dup = TRUE;
                        break;
                    }
                }

                if(!$dup) break;
            }
            $_tmp = new UserElement($uuid, $id, $name, $key);
            array_push($this->_userlist, $_tmp);
        }

        public function dropUser($uuid){
            for($i=0; $i<count($this->_userlist); $i++){
                if($this->_userlist[$i]->uuid() == $uuid) array_pop_index($this->_userlist, $i);
            }
        }

        public function getUserByUUID($uuid){
            for($i=0; $i<count($this->_userlist); $i++)
                if($this->_userlist[$i]->uuid() == $uuid) return $this->_userlist[$i];
            return FALSE;
        }

        public function getUserByName($name){
            for($i=0; $i<count($this->_userlist); $i++)
                if($this->_userlist[$i]->name() == $name) return $this->_userlist[$i];
            return FALSE;
        }

        public function getUserById($id){
            for($i=0; $i<count($this->_userlist); $i++)
                if($this->_userlist[$i]->id() == $id) return $this->_userlist[$i];
            return FALSE;
        }

        public function allUser(){
            return $this->_userlist;
        }
    }


    class UserElement{
        private $_uuid, $_id, $_name, $_isadmin, $_accepted;
        private $_getletter, $_cheatletter, $_keyval;

        public function __construct($uuid, $id, $name, $key){
            $this->_uuid = (string)trimNewline($uuid);
            $this->_id = trimNewline($id);
            $this->_name = trimNewline($name);
            $this->_accepted = FALSE;
            $this->_getletter = 0;
            $this->_isadmin = FALSE;
            $this->_cheatletter = 0;
            $this->_keyval = $key;
        }

        public function uuid(){
            return $this->_uuid;
        }

        public function id(){
            return str_replace("_", " ", $this->_id);
        }

        public function accept(){
            return $this->_accepted;
        }
        
        public function name(){
            return str_replace("_", " ", $this->_name);
        }

        public function isAdmin(){
            return $this->_isadmin;
        }

        public function setAdmin($is_admin){
            $this->_isadmin = (bool)$is_admin;
        }

        public function setAccept($acc){
            $this->_accepted = $acc;
        }

        public function giveLetter($value){
            $this->_getletter += $value;
        }
        
        public function giveLetterByCheat($value){
            $this->_cheatletter += $value;
        }

        public function getLetter(){
            return $this->_getletter;
        }

        public function getLetterByCheat(){
            return $this->_cheatletter;
        }

        public function getKey(){
            return $this->_keyval;
        }

        public function setKey(){
            $this->_keyval = bin2hex(random_bytes(32));
        }
    }

    function loadUser(){
        //ACTION 유저 목록을 파일에서 다시 읽어옴
        $_DOCUMENT_ROOT_ = $_SERVER['DOCUMENT_ROOT'];
        $rtn = unserialize(file_get_contents("$_DOCUMENT_ROOT_/data/userLis.dat"));
        return $rtn;
    }

    function saveUser($someClass){
        //ACTION 유저 정보를 파일에 저장
        $_DOCUMENT_ROOT_ = $_SERVER['DOCUMENT_ROOT'];
        $_fp = fopen("$_DOCUMENT_ROOT_/data/userLis.dat", 'w');
        
        fwrite($_fp, serialize($someClass)); 
        fclose($_fp);
    }

    class TeamList{
        private $_teamlist = array();
        private $_rotation;

        public function __construct(){
            $this->_teamlist = array();
            $this->_rotation = 0;
        }

        public function addTeam($uuid1, $uuid2, $teamid){
            // PHASE 1, 등록용
            $_tmp = new TeamElement($teamid, $uuid1, $uuid2);
                
            array_push($this->_teamlist, $_tmp);
            return TRUE;
        }
        
        public function dropTeam($teamid){
            // PHASE 1, 등록용
            for($i=0; $i<count($this->_teamlist); $i++){
                if($this->_teamlist[$i]->teamid() == $teamid){
                    array_pop_index($this->_teamlist, $i);
                    return TRUE;
                }
            }
            return FALSE;
        }
        
        public function getTeambyTeamId($teamid){
            for($i=0; $i<count($this->_teamlist); $i++)
            if($this->_teamlist[$i]->teamid() == $teamid) return $this->_teamlist[$i];
            return FALSE;
        }
        
        public function getTeambyUUID($uuid){
            for($i=0; $i<count($this->_teamlist); $i++)
            if(in_array($uuid, $this->_teamlist[$i]->uuids())) return $this->_teamlist[$i];
            return FALSE;
        }
        
        public function getCursorAndMove(){       
            $isAllRest = TRUE;
            for($i = 0; $i < count($this->_teamlist); $i++){
                if(!$this->_teamlist[$i]->getRest()) $isAllRest = FALSE;
            }
            if($isAllRest) return FALSE;

            $rtn = $this->_teamlist[$this->_rotation];
            while(TRUE){
                if(!$rtn->getRest()) break;
                $this->_rotation = ($this->_rotation + 1)%count($this->_teamlist);
                $rtn = $this->_teamlist[$this->_rotation];
            }
            $this->_rotation = ($this->_rotation + 1)%count($this->_teamlist);
            return $rtn;
        }

        public function getCursorAndShow(){
            $isAllRest = TRUE;
            for($i = 0; $i < count($this->_teamlist); $i++){
                if(!$this->_teamlist[$i]->getRest()) $isAllRest = FALSE;
            }
            if($isAllRest) return FALSE;
            
            $tr = $this->_rotation;

            $rtn = $this->_teamlist[$tr];
            while(TRUE){
                if(!$rtn->getRest()) break;
                $tr = ($tr + 1)%count($this->_teamlist);
                $rtn = $this->_teamlist[$tr];
            }
            
            return $rtn;
        }
        
        public function allTeam(){
            return $this->_teamlist;
        }
    }

    class TeamElement{
        private $_userid1, $_userid2, $_teamid; //UUID
        private $_warning;
        private $_isrest;

        public function __construct($teamid, $userid1, $userid2){
            $this->_teamid = $teamid;
            $this->_userid1 = $userid1;
            $this->_userid2 = $userid2;
            $this->_isrest = FALSE;
            $this->_warning = FALSE;
        }

        public function uuids(){
            return array($this->_userid1, $this->_userid2);
        }
        
        public function teamid(){
            return $this->_teamid;
        }

        public function setRest($isrest){
            $this->_isrest = (bool)$isrest;
        }

        public function getRest(){
            return $this->_isrest;
        }

        public function addWarn($warn){
            $this->_warning += $warn;
        }

        public function getWarn(){
            return $this->_warning;
        }
    }

    function loadTeam(){
        //ACTION 유저 목록을 파일에서 다시 읽어옴
        $_DOCUMENT_ROOT_ = $_SERVER['DOCUMENT_ROOT'];
        $rtn = unserialize(file_get_contents("$_DOCUMENT_ROOT_/data/teamLis.dat"));
        return $rtn;
    }

    function saveTeam($someClass){
        //ACTION 유저 정보를 파일에 저장
        $_DOCUMENT_ROOT_ = $_SERVER['DOCUMENT_ROOT'];
        $_fp = fopen("$_DOCUMENT_ROOT_/data/teamLis.dat", 'w');
        
        fwrite($_fp, serialize($someClass)); 
        fclose($_fp);
    }

    class WorkList{
        private $_worklist = array();

        public function __construct(){
            $this->_worklist = array();
        }

        public function makeNewWork($workid, $giveteam, $isgaonnuri=TRUE){
            $_tmp = new WorkElement($workid, $giveteam, $isgaonnuri);
            array_push($this->_worklist, $_tmp);
        }

        public function dropWork($workid){
            for($i=0; $i<count($this->_worklist); $i++){
                if($this->_worklist[$i]->workid() == $workid){
                    array_pop_index($this->_worklist, $i);
                    return TRUE;
                }
            }
            return FALSE;
        }

        public function findWorkById($workid){
            for($i=0; $i<count($this->_worklist); $i++)
                if($workid == $this->_worklist[$i]->workid()) return $this->_worklist[$i];
            return FALSE;
        }
        
        public function findAllWorkByUser($userid, $teamClass){
            $_rtn = array();
            for($i=0; $i<count($this->_worklist); $i++){
                // var_dump($this->_worklist[$i]);
                // var_dump($userid, $teamClass->getTeambyTeamId($this->_worklist[$i]->getTeam()));
                // var_dump($teamClass->getTeambyTeamId($this->_worklist[$i]->getTeam())->uuids());
                if(in_array($userid, $teamClass->getTeambyTeamId($this->_worklist[$i]->getTeam())->uuids())){
                    array_push($_rtn, $this->_worklist[$i]);
                }
            }
            return $_rtn;
        }

        public function allWork(){
            return $this->_worklist;
        }
    }

    class WorkElement{
        private $_workid;
        private $_giventeam;
        private $_letter;
        private $_starttime;
        private $_endtime;
        private $_endid;
        private $_isgaonnuri;

        public function __construct($workid, $giventeam, $isgaonnuri=TRUE){
            $nowDate = new DateTime("now", new DateTimeZone('Asia/Seoul'));
            $this->_workid = $workid;
            $this->_giventeam = $giventeam;
            $this->_starttime = $nowDate->getTimestamp();
            $this->_letter = FALSE;
            $this->_endtime = FALSE;
            $this->_endid = FALSE;
            $this->_isgaonnuri = $isgaonnuri;
        }

        public function workid(){
            return $this->_workid;
        }

        public function getTeam(){
            return $this->_giventeam;
        }

        public function setTeam($newteamid){
            $this->_giventeam = $newteamid;
        }

        public function setLetter($letter){
            $this->_letter = $letter;
        }

        public function getLetter(){
            return $this->_letter;
        }

        public function isEnd(){
            return ($this->_endtime == FALSE) ? FALSE : TRUE;
        }

        public function setEnd($endid){
            $endDate = new DateTime("now", new DateTimeZone('Asia/Seoul'));
            $this->_endtime = $endDate->getTimestamp();
            $this->_endid = $endid;
        }

        public function endid(){
            return $this->_endid;
        }
        
        public function getStart(){
            return $this->_starttime;
        }
        
        public function getEnd(){
            if(!$this->isEnd()) return FALSE;
            return $this->_endtime;
        }
        
        public function getPeriod(){
            if(!$this->isEnd()){
                $nowDate = new DateTime("now", new DateTimeZone('Asia/Seoul'));
                $now = $nowDate->getTimestamp();
                return $now - $this->_starttime;
            }
            return $this->_endtime - $this->_starttime;
        }

        public function isGaonnuri(){
            return $this->_isgaonnuri;
        }

        public function setGaonnuri($isgaonnuri){
            $this->_isgaonnuri = $isgaonnuri;
        }
    }

    function loadWork(){
        //ACTION 유저 목록을 파일에서 다시 읽어옴
        $_DOCUMENT_ROOT_ = $_SERVER['DOCUMENT_ROOT'];
        $rtn = unserialize(file_get_contents("$_DOCUMENT_ROOT_/data/workLis.dat"));
        return $rtn;
    }

    function saveWork($someClass){
        //ACTION 유저 정보를 파일에 저장
        $_DOCUMENT_ROOT_ = $_SERVER['DOCUMENT_ROOT'];
        $_fp = fopen("$_DOCUMENT_ROOT_/data/workLis.dat", 'w');
        
        fwrite($_fp, serialize($someClass)); 
        fclose($_fp);
    }

    class DictList{
        private $_dictlist = array();
        private $_lastvar;
        private $_metadata = array();

        public function __construct(){
            $this->_dictlist = array();
            $this->_lastvar = 0;
        }

        //FROM: 한국어
        //TO: 영어
        public function addPair($from, $to){
            array_push($this->_dictlist, new DictElement($this->_lastvar++, $from, $to));
        }

        public function setMeta($info, $data){
            $this->_metadata[$info] = $data;
        }

        public function getMeta($info){
            return $this->_metadata[$info];
        }

        public function getAllMatch($text){
            $rtnLis = array();
            for($i=0; $i<count($this->_dictlist); $i++){
                $dicel = $this->_dictlist[$i];
                for($j = 0; $j < count($dicel->from()); $j++ ){
                    if(FALSE !== strpos($text, ($dicel->from())[$j] ) ){
                        array_push($rtnLis, $dicel);
                        break;
                    }
                }
            }
            return $rtnLis;
        }

        public function allDict(){
            return $this->_dictlist;
        }

        public function JSONify(){
            $rtn = array();
            for($i = 0; $i < count($this->_dictlist); $i++){
                $ele = array();
                $dictObj = $this->_dictlist[$i];
                $ele["from"] = $dictObj->from();
                $ele["to"]   = $dictObj->to();
                array_push($rtn, $ele);
            }
            return json_encode($rtn);
        }

    }
    
    class DictElement{
        private $_from = array(), $_to = "";
        private $_woid;

        public function __construct($woid, $from, $to){
            $this->_woid = $woid;
            $this->_from = $from;
            $this->_to = $to;
        }

        public function from(){
            return $this->_from;   
        }

        public function to(){
            return $this->_to;   
        }

        public function id(){
            return $this->_woid;   
        }
    }

    function loadDict(){
        $_DOCUMENT_ROOT_ = $_SERVER['DOCUMENT_ROOT'];
        $_fp = fopen("$_DOCUMENT_ROOT_/data/translate_guideline.txt", 'r');

        $_LectureData = new DictList();
        $num = 0;
        $metadata = array();

        while(!feof($_fp)){
            $num++;
            $_tmp = trimNewline(fgets($_fp));
            
            if($_tmp[0]=="#" || strlen($_tmp)==0) continue;

            if($_tmp[0]=="@"){
                // Metadata Analysis
                // Possible metadata: DATE
                $_tmparr = explode('=', $_tmp);
                if( count($_tmparr) != 2 ) return $num;
                $_info = trimNewline(str_replace("@", "", $_tmparr[0]));
                $_data = trimNewline($_tmparr[1]);
                $_LectureData->setMeta($_info, htmlspecialchars($_data));
                continue;
            }

            $_tmparr = explode('>', $_tmp);
            if( count($_tmparr) != 2 ){
                return $num;
            }
            
            $from = array();
            $from_raw = explode('&', $_tmparr[0]);
            
            $to = trimNewline($_tmparr[1]);
            
            for($i=0; $i<count($from_raw); $i++){
                array_push($from, str_replace(" ", "", $from_raw[$i]));
            }
            
            $_LectureData->addPair($from, $to);
        }

        fclose($_fp);
        return $_LectureData;

    }

    class Phase{
        private $_phase; // 준비기간=INACTIVE_PERIOD 선발기간=ADMISSION_PERIOD 번역기간=TRANSLATION_PERIOD 정산기간=CALCULATION_PERIOD

        public function __construct($stat){
            $this->_phase = $stat;
        }

        public function getPhase(){
            return $this->_phase;
        }

        public function setPhase($status){
            $this->_phase = $status;
            //TODO 각 phase 진입에 따른 변화사항 구현이 필수적
        }
    }

    function loadPhase(){
        //ACTION 사이클 목록을 파일에서 다시 읽어옴
        $_DOCUMENT_ROOT_ = $_SERVER['DOCUMENT_ROOT'];
        $rtn = unserialize(file_get_contents("$_DOCUMENT_ROOT_/data/phase.dat"));
        return $rtn;
    }

    function savePhase($statusClass){
        //ACTION 사이클 정보를 파일에 저장
        $_DOCUMENT_ROOT_ = $_SERVER['DOCUMENT_ROOT'];
        $_fp = fopen("$_DOCUMENT_ROOT_/data/phase.dat", 'w');
        
        fwrite($_fp, serialize($statusClass)); 
        fclose($_fp);
    }

    // ===================================================
    // ===================================================

    function trimNewline($_txt){
        $_findName = str_replace("\n", "", $_txt);
        $_findName = str_replace("\r", "", $_txt);
        return trim($_findName);
    }

    function array_pop_index(&$array, $index){
        if($index >= count($array)) return;
        for($i=$index+1; $i<count($array); $i++){
            swap($array[$i], $array[$i-1]);
        }
        $rtn = $array[$index-1];
        array_pop($array);
        return $rtn;
    }

    function swap(&$a, &$b){
        $tmp = $a;
        $a = $b;
        $b = $tmp;
    }

    function SecondToText($time){
        $t = $time;
        //t: 초 단위
        $s = $t % 60;
        $t = (int)($t / 60);
        //t: 분 단위
        $m = $t % 60;
        $t = (int)($t / 60);
        //t: 시간 단위
        $h = $t % 24;
        $t = (int)($t / 24);
        //t: 일 단위
        $d = $t;

        if($time < 0){
            $recent = "음수 시간";
        }else if($time == 0){
            $recent = "없음";
        }else if($time < 60){
            $recent = "1분 미만";
        }else if($time < 3600){
            $recent = "".$m."분";
        }else if($time < 86400){
            $recent = "".$h."시간 ".$m."분";
        }else if($time < 2592000){
            $recent = "".$d."일 ".$h."시간";
        }else{
            $recent = "30일 이상";
        }

        return $recent;
    }

?>