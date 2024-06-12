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
<div class="simb"></div>
<div style="width: 100%; background-color: #FFFFFF; height: 700px; padding: 0px 20%;">
    <div style="width: 100%;">
        <p style="font-size: 1.5rem; font-weight: bold; color: black; text-align: center; padding: 20px 0;">[공지] Sofia 서버 이관작업 안내</p>
        <p style="font-size: 1rem; color: black; padding: 10px 0;">Sofia 유지보수팀입니다.</p>
        <p style="font-size: 1rem; color: black; padding: 10px 0;">Sofia 서버를 개인 유지비로 운영되는 서버에서 학교 예산으로 승인된 서버로 마이그레이션할 예정입니다. 따라서 해당 기간 동안 Sofia 서비스가 잠시 중단됨을 안내해 드립니다.</p>
        <p style="font-size: 1rem; color: black; padding: 10px 0;">Sofia 서버 이관 후에도 제공되는 서비스에 있어서는 차이가 없음을 알려 드립니다. 자세한 내용은 아래 사항을 참고해 주세요.</p>
        <p style="font-size: 1rem; color: black; padding: 10px 0;">다만, 기존에 Sofia 관리자 페이지를 즐겨찾기로 설정하신 분들께서는 해당 링크는 마이그레이션 이후 더 이상 Sofia 서비스로 연결되지 않음을 알려 드립니다. 새로운 주소를 이용해 주세요.</p>
        <p style="font-size: 1.5rem; font-weight: bold; color: black; text-align: center; padding: 20px 0;">=== 아래 ===</p>
        <p style="font-size: 1rem; color: black; padding: 10px 0;">작업 일시: 2020년 5월 17일(日) 12:00부터 진행</p>
        <p style="font-size: 1rem; color: black; padding: 10px 0;">예상 소요 시간: 3시간</p>
        <p style="font-size: 1rem; color: black; padding: 10px 0;">작업 내용: Sofia 서버 마이그레이션 및 데이터 이전 작업</p>
        <p style="font-size: 1rem; color: black; padding: 10px 0;">작업 상세: skytreenetwork.cafe24.com에서 ksasofia.cafe24.com으로 서버 이동</p>
        <p style="font-size: 1rem; color: black; padding: 10px 0;">작업 중 제한되는 서비스: (Sofia 관리자 페이지) 전체 (Sofia 챗봇) 내 정보/가입하기, 번역 업무 확인, 번역 보고하기, 번역 가이드라인, 탈퇴</p>
        <p style="font-size: 1rem; color: black; padding: 10px 0;">비고: 점검 기간 중에 지각 마감이 있는 업무는 일괄적으로 3시간 연장</p></p>
        <p style="font-size: 1rem; color: black; padding: 10px 0;"></p>
    </div>
</div>
<div class="break"></div>
<?php

    require('../supply/foot.php');
    ob_end_flush();
?>