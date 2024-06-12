<!-- Calendar Start -->
<style>
.deactivebox{
    float: left;
    margin-top: 23px;
    width: 200px;
    background-color:#eeeeee;
    padding: 8px;
}

.deactivebox p.headline{
    font-size: 1.5rem;
    font-weight: bold;
    margin:0px;
    color:#333333;
}

.deactivebox p.explain{
    font-size: 0.8rem;
    margin-top: 2px;
    color:#888888;
}

.activebox{
    float: left;
    margin-top: 23px;
    width: 200px;
    background-color: <?php if($SHUTDOWN_SERVER) echo "red"; else echo "#00AB8E"; ?>;
    padding: 8px;
    color: white;
}

.activebox p.headline{
    font-size: 1.5rem;
    font-weight: bold;
    margin:0px;
    color: white;
}

.activebox p.explain{
    font-size: 0.8rem;
    margin-top: 2px;
    color: <?php if($SHUTDOWN_SERVER) echo "white"; else echo "rgb(0, 75, 62)"; ?>;
}
</style><?php
    $stat = $MASTER->getAction('phase')->getPhase();
    function decisionBoxType($num, $stat){
        if($num == $stat)
            return "activebox";
        else
            return "deactivebox";
    }
?><div style="width: 100%; background: #ffffff; height: 180px;">
    <div style="width: 1200px; height: 180px; margin: 0px auto; text-align: center;">   
        <p style="font-size: 2rem; font-weight: bold; padding-top:1rem; color:#000000;"><?php if($SHUTDOWN_SERVER) echo "서버가 STRUCTURE.PHP의 환경변수에 의해 정지됨"; else echo "현재 Sofia 상태"; ?></p>
        
        <div style="float: left; margin-top: 27px; width: 35px;"></div>

        <div class="<?php echo decisionBoxType($MASTER->getAction('INACTIVE_PERIOD') ,$stat); ?>">
            <p class="headline">비활성화</p>
            <p class="explain">활성화 대기</p>
        </div>

        <div style="float: left; margin-top: 27px; width: 25px;"></div>

        <div class="<?php echo decisionBoxType($MASTER->getAction('ADMISSION_PERIOD') ,$stat); ?>">
            <p class="headline">선발 기간</p>
            <p class="explain">사용자 승인 / 팀 구성</p>
        </div>

        <div style="float: left; margin-top: 27px; width: 25px;"></div>
        
        <div class="<?php echo decisionBoxType($MASTER->getAction('TRANSLATION_PERIOD') ,$stat); ?>">
            <p class="headline">번역 기간</p>
            <p class="explain">번역 작업 생성 / 처리</p>
        </div>
        
        <div style="float: left; margin-top: 27px; width: 25px;"></div>
        
        <div class="<?php echo decisionBoxType($MASTER->getAction('CALCULATION_PERIOD') ,$stat); ?>">
            <p class="headline">정산 기간</p>
            <p class="explain">글자수 정산 / 봉사시간 처리</p>
        </div>

        <div style="float: left; margin-top: 27px; width: 25px;"></div>
        
        <div class="deactivebox">
            <p class="headline">초기화</p>
            <p class="explain">이후 비활성화</p>
        </div>
        
        <div style="float: left; margin-top: 27px; width: 35px;"></div>

    </div>
</div>