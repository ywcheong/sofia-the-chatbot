<?php
    session_start();
    require_once('../../valid.php');
?>
<div class="simb"></div>
<div style="width: 100%; background: #FFFFFF; height: 120px;">
    <div style="width: 1000px; height: 120px; margin: 0px auto; text-align: center;">      
        <div style="float: left; margin-top: 15px; width: 1000px; align: center; vertical-align: center;">
            <p style="font-size: 1.5rem; font-weight: bold; margin:0px; color:<?php if(!isset($_REQUEST['key'])) echo "#000000"; else echo "#FF0000";?>"
            ><?php if(!isset($_REQUEST['key'])) echo "여기에 번역버디 토큰 입력"; else echo "잘못된 토큰입니다.";?></p>
            <form method="POST" name="NAME" action="index.php">
                <input type="password" name="key" placeholder="번역버디 토큰 입력..." length="64"
                style="font-size: 1rem; margin-top: 1rem; color:#888888; line-height: 1.5rem; width: 30rem;" />
                <a class="smallButton" onclick='document.forms["NAME"].submit(); return false;'>인증</a>
            </form>
        </div>
    </div>
</div>
<div class="break"></div>