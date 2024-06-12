<?php
    require_once('../../valid.php');
    $_RTN = array(0, 0, 0, 0, 0);
    $workList = &$MASTER->getAction('workClass')->allWork();
    for($i = 0; $i < count($workList); $i++){
        if(is_numeric($workList[$i]->getLetter())){
            $_RTN[0] += $workList[$i]->getLetter();
        }
        if($workList[$i]->isEnd()){
            $_RTN[1] += 1;
            $_RTN[4] += $workList[$i]->getPeriod();
        }else{
            $_RTN[2] += 1;
            if($workList[$i]->getPeriod() > $MASTER->getAction('LATE_THRESHOLD')){
                $_RTN[3] += 1;
            }
        }
    }

    #$_RTN[4] = SecondToText((int)($_RTN[4] / $_RTN[1]));
    $_RTN[4] = number_format($_RTN[0] / $_RTN[4] * 3600, 1);
    if($_RTN[1]+$_RTN[2] == 0) $_RTN[4] = "(N/A)";

?><div style="width: 100%; background: #ffffff; height: 120px;">
	<div style="width: 1000px; height: 120px; margin: 0px auto; text-align: center;">      
        
        <div style="float: left; margin-top: 23px; width: 200px;">
            <p style="font-size: 2rem; font-weight: bold; margin:0px; color:#333333;"><?php echo $_RTN[0]; ?>자</p>
            <p style="font-size: 0.8rem; margin-top: 2px; color:#888888;">총 번역한 글자수</p>
        </div>

        <div style="float: left; margin-top: 23px; width: 200px;">
            <p style="font-size: 2rem; font-weight: bold; margin:0px; color:#333333;"><?php echo $_RTN[1]; ?>건</p>
            <p style="font-size: 0.8rem; margin-top: 2px; color:#888888;">완료된 업무</p>
        </div>
        
        <div style="float: left; margin-top: 23px; width: 200px;">
            <p style="font-size: 2rem; font-weight: bold; margin:0px; color:#333333"><?php echo $_RTN[2]; ?>건</p>
            <p style="font-size: 0.8rem; margin-top: 2px; color:#888888;">완료되지 않은 업무</p>
        </div>

        <div style="float: left; margin-top: 23px; width: 200px;">
            <p style="font-size: 2rem; font-weight: bold; margin:0px; color:<?php echo ($_RTN[3] > 0 ? "red": "#333333");?>;"><?php echo $_RTN[3]; ?>건</p>
            <p style="font-size: 0.8rem; margin-top: 2px; color:#888888;">기한이 지난 업무</p>
        </div>

        <div style="float: left; margin-top: 23px; width: 200px;">
            <p style="font-size: 2rem; font-weight: bold; margin:0px; color:#00AB8E;"><?php echo $_RTN[4]; ?>자/hr*</p>
            <p style="font-size: 0.8rem; margin-top: 2px; color:#888888;">평균 번역속도</p>
            <p style="font-size: 0.6rem; margin-top: 0px; color:#888888;">*미완료 제외, 전체 자수/전체 소요시간</p>
        </div>

	</div>
 </div>
 <div style="height: 30px;"></div>