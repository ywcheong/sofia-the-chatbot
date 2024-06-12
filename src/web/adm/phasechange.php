<?php    
    session_start();
    ob_start();
    if($_SESSION['admin'] != TRUE){
        header('Location: ../home/index.php');
    }

    if($_SESSION['viewonly']){
        die("관리자만 접근 가능");
    }

    require_once('../../structure.php');
    $MASTER = new MasterAction("WEB");
    require_once('../../const.php');
    #var_dump($MASTER);
    require_once('table.php');
    require_once('../supply/head.php');
?>
<?php

{
    $errorArray = array();
    $okArray = array();
    $phase = $MASTER->getAction('phase')->getPhase();
    
    switch($phase){
        case $MASTER->getAction('INACTIVE_PERIOD'):
            $nextPhase = $MASTER->getAction('ADMISSION_PERIOD');
            array_push($okArray, "Sofia가 활성화 대기 중...");
        break;
        
        case $MASTER->getAction('ADMISSION_PERIOD'):
            $userClass = &$MASTER->getAction('userClass')->allUser();
            
            $notAcceptExist = False;

            for($i = 0; $i < count($userClass); $i++){
                $userObj = $userClass[$i];
                if(!$userObj->accept()){
                    array_push($errorArray, "사용자 ".$userObj->id()."(이)가 아직 승인 또는 거부되지 않음");
                    $notAcceptExist = True;
                }
            }

            if(!$notAcceptExist) array_push($okArray, "승인되지 않은 사용자 없음");
            $nextPhase = $MASTER->getAction('TRANSLATION_PERIOD');

        break;
        
        case $MASTER->getAction('TRANSLATION_PERIOD'):
            $workClass = &$MASTER->getAction('workClass')->allWork();

            if(count($workClass) < 1) array_push($errorArray, "적어도 하나의 업무가 필요함");
            else array_push($okArray, "하나 이상의 업무가 있음");

            $notFinishedWorkExist = False;
            for($i = 0; $i < count($workClass); $i++){
                $workObj = $workClass[$i];
                if(!$workObj->isEnd()){
                    array_push($errorArray, "업무 ".$workObj->workid()."(이)가 아직 종료되지 않음");
                    $notFinishedWorkExist = True;
                }
            }

            if(!$notFinishedWorkExist) array_push($okArray, "모든 업무는 종료되었음");
            $nextPhase = $MASTER->getAction('CALCULATION_PERIOD');
        break;
        
        case $MASTER->getAction('CALCULATION_PERIOD'):
            $nextPhase = $MASTER->getAction('INACTIVE_PERIOD');
            array_push($okArray, "초기화 옵션을 선택하세요: "
            .'<span style="font-size: 1rem;"><select class="initoption" name="initoption">'
                    .'<option value="x" selected="selected">선택하세요</option>'
                    .'<option value="onlyme">본인 제외 삭제</option>'
                    .'<option value="admins">관리자 제외 삭제(추천)</option>'
                    .'<option value="all">모든 번역버디 보존</option>'
                    .'</select></span>');
        break;
    }

    if($_REQUEST['nextphase'] == "YES"){
        if($phase == $MASTER->getAction('CALCULATION_PERIOD')){
            if(!isset($_REQUEST['initoption'])){
                array_push($errorArray, "잘못된 초기화 선택지입니다.");
            }else{
                switch($_REQUEST['initoption']){
                    case "onlyme":
                        $onlyme = $MASTER->getAction('userClass')->getUserByUUID($_SESSION['uuid']);
                        $newUserClass = new UserList();
                        $newTeamClass = new TeamList();
                        $newUserClass->addUser($onlyme->uuid(), $onlyme->id(), $onlyme->name());
                        $newme = &$newUserClass->getUserByUUID($onlyme->uuid());
                        $newme->setAccept(True);
                        $newme->setAdmin(True);
                        $newTeamClass->addTeam($newme->uuid(), null, 1);
                        $MASTER->setAction('userClass', $newUserClass);
                        $MASTER->setAction('teamClass', $newTeamClass);
                    break;

                    case "admins":
                        $saveList = array();
                        $userClass = &$MASTER->getAction('userClass')->allUser();
                        for($i = 0; $i < count($userClass); $i++){
                            if($userClass[$i]->isAdmin()) array_push($saveList, $userClass[$i]);
                        }

                        $newUserClass = new UserList();
                        $newTeamClass = new TeamList();
                        for($i = 0; $i < count($saveList); $i++){
                            $user = $saveList[$i];
                            $newUserClass->addUser($user->uuid(), $user->id(), $user->name());
                            $newuser = &$newUserClass->getUserByUUID($user->uuid());
                            $newuser->setAccept(True);
                            $newuser->setAdmin(True);
                            $newTeamClass->addTeam($newuser->uuid(), null, $i+1);
                        }

                        $MASTER->setAction('userClass', $newUserClass);
                        $MASTER->setAction('teamClass', $newTeamClass);
                    break;
                    
                    case "all":
                        $saveList = array();
                        $normList = array();
                        $userClass = &$MASTER->getAction('userClass')->allUser();
                        for($i = 0; $i < count($userClass); $i++){
                            if($userClass[$i]->isAdmin()) array_push($saveList, $userClass[$i]);
                            else array_push($normList, $userClass[$i]);
                        }

                        $newUserClass = new UserList();
                        $newTeamClass = new TeamList();

                        for($i = 0; $i < count($saveList); $i++){
                            $user = $saveList[$i];
                            $newUserClass->addUser($user->uuid(), $user->id(), $user->name());
                            $newuser = &$newUserClass->getUserByUUID($user->uuid());
                            $newuser->setAccept(True);
                            $newuser->setAdmin(True);
                            $newTeamClass->addTeam($newuser->uuid(), null, $i+1);
                        }
                        for($i = 0; $i < count($normList); $i++){
                            $user = $normList[$i];
                            $newUserClass->addUser($user->uuid(), $user->id(), $user->name());
                            $newuser = &$newUserClass->getUserByUUID($user->uuid());
                            $newuser->setAccept(True);
                            $newuser->setAdmin(False);
                            $newTeamClass->addTeam($newuser->uuid(), null, $i+1+count($saveList));

                        }

                        $MASTER->setAction('userClass', $newUserClass);
                        $MASTER->setAction('teamClass', $newTeamClass);


                    break;
                    
                    default:
                        array_push($errorArray, "존재하지 않는 선택지입니다.");
                    break;
                }

                //Another all data remove
                $MASTER->setAction('workClass', new WorkList());

                $dirHandle = opendir($_SERVER['DOCUMENT_ROOT']."/web/csv");
                while ($file = readdir($dirHandle)) {
                    unlink($_SERVER['DOCUMENT_ROOT']."/web/csv/".$file);
                }

                $dirHandle2 = opendir($_SERVER['DOCUMENT_ROOT']."/data/olddict");
                while ($file = readdir($dirHandle2)) {
                    unlink($_SERVER['DOCUMENT_ROOT']."/data/olddict/".$file);
                }

            }
        }

        if(count($errorArray) > 0){
            if($phase == $MASTER->getAction('CALCULATION_PERIOD')){
                $_SESSION['majornotice'] = "옵션 설정이 잘못되었습니다!";
            }else{
                $_SESSION['majornotice'] = "아직 만족되지 못한 조건이 있습니다!";
            }
        }else{
            $_SESSION['majornotice'] = "반영되었습니다.";
            $MASTER->getAction('phase')->setPhase($nextPhase);
            savePhase($MASTER->getAction('phase'));

            if($phase == $MASTER->getAction('CALCULATION_PERIOD')){
                // Save initilized data
                saveUser($MASTER->getAction('userClass'));
                saveTeam($MASTER->getAction('teamClass'));
                saveWork($MASTER->getAction('workClass'));
            }
            
            
        }
        
        // Redirect Pattern
        header("Location: phasechange.php");
        exit;
    }

    $_SESSION['errorarray'] = $errorArray;
    $_SESSION['okarray'] = $okArray;
}
    
?>
<?php
    require_once('phase.php');
?>
<div class="container-table100">
<a href="admin.php" class="myButton"><h3>돌아가기</h3></a>
<a class="myButton" href="<?php echo ($MASTER->getAction('WEB_ROOT')); ?>data/Sofia_settings.xlsx" download><h3>Sofia 설정 보기</h3></a>
<div class="simb"></div>
<div style="text-align: center;">
    <p style="font-weight: bold; font-size: 2rem;">
        다음 기간으로 넘어가시겠습니까?
    </p>

    <p style="font-weight: bold; font-size: 1.5rem;">
        주의: 이 페이지에서는 새로고침을 하지 마세요.
    </p>
</div>
</div>
<script>
    $(document).on('click', "[id^=NEXT-PHASE]", function(){
        if(confirm('한 번 기간을 넘어가면 취소할 수 없습니다. 정말로 다음 기간으로 넘어갑니까?'))
        {
            var form = $('<form action="#" method="post">' +
            '<input type="hidden" name="nextphase" value="YES" />' +
            '<input type="hidden" name="initoption" value="'+ $('select.initoption').children("option:selected").val() +'" />' +
            '</form>');
            $('body').append(form);
            form.submit();
        }
    });

    $(document).on('click', "[id^=CANNOT-PHASE]", function(){
        alert('조건이 충족되지 않아 기간을 넘길 수 없습니다!');
    });

</script>
<div style="width: 100%;">
	<div style="width: 1000px; margin: 0px auto; text-align: center;">
        <?php

if(isset($_SESSION['majornotice'])){
    echo '<p style="font-weight: bold; font-size: 2rem; color: white; text-decoration: underline;">'.$_SESSION['majornotice'].'</p><div class="break"></div>';
    unset($_SESSION['majornotice']);
}

if(isset($_SESSION['errorarray'])){
    for($i = 0; $i < count($_SESSION['errorarray']); $i++){
        echo '<p style="font-weight: bold; font-size: 1.5rem; background-color: #FFFFFF; color: black">'.$_SESSION['errorarray'][$i].'</p><br />';
    }
}

if(isset($_SESSION['okarray'])){
    for($i = 0; $i < count($_SESSION['okarray']); $i++){
        echo '<p style="font-weight: bold; font-size: 1.5rem; background-color: #FFFFFF; color: #00AB8E">✓ '.$_SESSION['okarray'][$i].'</p><br />';
    }
}

if($phase == $MASTER->getAction('CALCULATION_PERIOD')){
    echo '<p>어떤 옵션에서도 봉사시간, 팀 구성, 번역 업무 데이터는 삭제됩니다! 번역 가이드라인은 어떤 옵션에서도 제거되지 않습니다.</p>';
}

echo '<h3 class="myButton" '.( count($errorArray) == 0 ? 'id="NEXT-PHASE"' : 'id="CANNOT-PHASE"').'>이곳을 클릭해 Sofia의 상태를 다음 상태로 바꾸기</h3>';

?>
    </div>
</div>
<?php
    require('../supply/foot.php');
    // $MASTER->getAction('phase')->setPhase($MASTER->getAction('ADMISSION_PERIOD'));
    // savePhase($MASTER->getAction('phase'));
    ob_end_flush();
?>