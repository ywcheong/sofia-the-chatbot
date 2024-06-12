<?php
    session_start();
    ob_start();
    require_once('../../structure.php');
    
    //여기서 $_REQUEST['uuid'] 토대로 판정 시작하고 부여
    $MASTER = new MasterAction("WEB");
    $MASTER->setAction('is_public', TRUE);
    require_once('../../const.php');
    require('../supply/head.php');
?>
<style>
.maintxt{
    font-size: 1rem; 
    color: black;
    padding: 10px 0;
}

.maintitle{
    font-size: 1.5rem;
    font-weight: bold;
    color: black;
    text-align: center;
    padding: 20px 0;
}

.highlt{
    color: white;
    background-color: #00AB8E;
}
</style>
<div class="simb"></div>
<div style="width: 100%; background-color: #FFFFFF; height: 1200px; padding: 0px 20%;">
    <div style="width: 100%;">
        <p class="maintitle">Sofia 토큰이 무엇인가요?</p>
        <p class="maintxt">토큰은 <span class="highlt">Sofia 웹사이트에 접속하기 위한 비밀 패스워드</span>입니다. 토큰은 알파벳과 숫자를 섞어 66자로 랜덤하게 생성됩니다.</p>
        <p class="maintitle">Sofia 웹사이트에서는 무엇을 할 수 있나요?</p>
        <p class="maintxt">승인된 번역버디는 챗봇에서는 불가능한 기능인 다른 사람의 업무 배정 현황 및 봉사시간 누계 조회가 가능합니다. 만약 본인의 번역 자수가 남들과 비교해 너무 적다면, 업무를 더 달라고 요청하는 등 응용이 가능합니다.</p>
        <p class="maintxt">관리자는 로그인 이후 Sofia에서 제공하는 모든 기능을 사용 가능합니다.</p>
        <p class="maintitle">토큰은 어떻게 사용하나요?</p>
        <p class="maintxt"><a href="../../" class="smallButton">이곳</a>으로 들어간 뒤, 토큰 입력 폼을 클릭해 토큰을 붙여넣으세요. Sofia 챗봇의 [Sofia 페이지] 버튼으로도 접속 가능합니다.</p>
        <p class="maintitle">제 토큰을 어떻게 확인할 수 있나요?</p>
        <p class="maintxt">먼저 본인의 카카오톡 계정이 Sofia에서 '승인된 번역버디' 또는 '관리자'에 해당하는 권한을 가지고 있어야 합니다. Sofia 챗봇으로 들어가서, 메인 메뉴에서 [내 정보 / 가입하기]를 클릭하고 하단 팝업 메뉴의 [토큰 조회하기]를 눌러 주세요.</p>
        <p class="maintitle">제 토큰이 유출되었습니다. 어떻게 해야 하나요?</p>
        <p class="maintxt">빠른 시일 내로 재발급하셔야 합니다. 재발급은 관리자에게 수동으로 요청해 진행할 수도 있고, Sofia 챗봇에서도 자동으로 가능합니다. 다음 절차를 따르세요.</p>
        <p class="maintxt">Sofia 챗봇으로 들어가서, 메인 메뉴에서 [내 정보 / 가입하기]를 클릭하고 하단 팝업 메뉴의 [토큰 조회하기]를 눌러 주세요. 이후 다시 하단 팝업 메뉴에서 [토큰 재발급하기]을 누른 뒤 확인 요청을 수락하면 토큰이 재발급됩니다.</p>
        <p class="maintxt">토큰이 재발급되면 기존 토큰은 자동으로 만료되어 무효 처리됩니다.</p>
        <p class="maintitle">토큰을 붙여넣었는데 로그인이 되지 않습니다.</p>
        <p class="maintxt">토큰을 재발급받은 적이 있다면 최신 토큰인지 확인하세요. 또한 토큰을 복사할 때 다른 문자(예: 앞뒤 공백)가 함께 복사되었는지 확인하세요. 또한, 번역 작업이 초기화될 때마다 토큰 역시 초기화됩니다.</p>
    </div>
</div>
<div class="break"></div>
<?php

    require('../supply/foot.php');
    ob_end_flush();
?>