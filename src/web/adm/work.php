<?php    
    session_start();
    if($_SESSION['admin'] != TRUE){
        header('Location: ../home/index.php');
    }

    require_once('../../structure.php');
    $MASTER = new MasterAction("WEB");
    require_once('../../const.php');
    #var_dump($MASTER);
    require_once('table.php');
    require_once('../supply/head.php');
    require_once('phase.php');

?><script>
    $( document ).ready(function() {
        $('#notify-csv').hide();
        $.ajax({
            url: 'serve.php',
            data:{
                "action": "work",
                "sideaction": "generate"
            },
            type: 'post',
            success:function(data){
                $.notify("성공적으로 데이터가 로딩되었습니다!", "success");
                $('#datatable').empty();
                $('#datatable').append(data['back']);
                $('.select').css('color','gray');
            },
            error: function (jqXHR, textStatus, errorThrown){
                $.notify("데이터 로딩에 실패했습니다. 새로고침 해주세요.", "error");
            },
            dataType: 'json'
        })
    });

    $(document).on('click', "[id^=NEW-SEND]", function(){
    if($('#NEW-UUID').val() != ""){
        $.ajax({
            url: 'serve.php',
            data:{
                "action": "work",
                "sideaction": "addwork",
                "uuid": $('#NEW-UUID').val(),
                "team": $('#NEW-TEAM option:selected').val(),
                "notgaonnuri": $('#notgaonnuri').is(":checked"),
            },
            type: 'post',
            success:function(data){
                $.notify("성공적으로 반영되었습니다!", "success");
                $('#datatable').empty();
                $('#datatable').append(data['back']);
                $('.select').css('color','gray');
            },
            error: function (jqXHR, textStatus, errorThrown){
                $.notify("반영에 실패했습니다. (오류: ".concat(decodeURI(jqXHR.statusText).replace(/\+/gi, ' ')).concat(")"), "error");
                console.log(decodeURI(jqXHR.statusText).replace('+', ' '));
            },
            dataType: 'json'
        })
    }else{
        $.notify("필수 양식이 누락되었습니다.", "warn");
    }
    });

    $(document).on('click', "[class*=w-remove]", function(){
        if(confirm('정말로 작업을 삭제합니까? 완료된 작업이면 획득한 봉사 시간도 소멸 처리됩니다. 다만 늦게 제출되어 경고가 부여되었던 작업이라면 경고가 자동 제거되지는 않으며, 늦은 작업이라도 완료되지 않았다면 경고가 자동 부여되지는 않습니다.')){$.ajax({
            url: 'serve.php',
            data:{
                "action": "work",
                "sideaction": "remove",
                "uuid": $(this).parent().attr('id'),
                "foo": "fff"
            },
            type: 'post',
            success:function(data){
                $.notify("성공적으로 반영되었습니다!", "success");
                $('#datatable').empty();
                $('#datatable').append(data['back']);
                $('.select').css('color','gray');
            },
            error: function (jqXHR, textStatus, errorThrown){
                console.log($(this).parent().attr('id'));
                $.notify("반영에 실패했습니다. (오류: ".concat(decodeURI(jqXHR.statusText).replace(/\+/gi, ' ')).concat(")"), "error");
            },
            dataType: 'json'
        });}
    });

    $(document).on('click', "[class*=w-show]", function(){
        $.notify($(this).attr('displaytext'), "warn");
    });

    $(document).on('click', "[class*=w-download]", function(){
        $.ajax({
            url: 'serve.php',
            data:{
                "action": "work",
                "sideaction": "download",
                "timestamp": (+new Date()),
            },
            type: 'post',
            success:function(data){
                console.log(data);
                window.location.assign(data['redir']);
                $.notify("생성 완료되었습니다. 다운로드 버튼을 눌러 받으세요. (주의: 아직 완료되지 않은 작업은 포함되지 않았습니다)", "success");
            },
            error: function (jqXHR, textStatus, errorThrown){
                $.notify("반영에 실패했습니다. (오류: ".concat(decodeURI(jqXHR.statusText).replace(/\+/gi, ' ')).concat(")"), "error");
            },
            dataType: 'json'
        });

        $('#notify-csv').show();
    });

    $(document).on('click', "#notgaonnuri", function(){
        console.log($('#notgaonnuri').is(":checked"));
        if($('#notgaonnuri').is(":checked")){
            $('#NEW-UUID').attr('placeholder', '작업의 간략한 설명...');
        }else{
            $('#NEW-UUID').attr('placeholder', '고유번호...');
        }
    });

    $(document).ajaxStart(function() {
        $.notify("데이터를 갱신 중입니다...", "info");
    });
</script>
<div class="break"></div>
</div><a href="admin.php" class="myButton"><h3>돌아가기</h3></a><?php if(($MASTER->getAction('phase')->getPhase() == $MASTER->getAction('TRANSLATION_PERIOD') || $MASTER->getAction('phase')->getPhase() == $MASTER->getAction('CALCULATION_PERIOD')) && !$_SESSION['viewonly']) echo '<a class="myButton w-download"><h3>.CSV 다운로드*</h3></a>' ?>
<div class="simb"></div>
<p style="color: #ffffff; font-size: 0.8rem;" id="notify-csv">* .csv 파일이 깨지면 UTF-8 문제입니다. <a href="https://walkingfox.tistory.com/112" class="patchnote" target="_blank">https://walkingfox.tistory.com/112</a>를 참조하세요.</p>
<div class="wrap-table100" id="datatable"><div class="break"></div>
<iframe id="my_iframe" style="display:none;"></iframe>
<?php
    require('../supply/foot.php');
?>
<!-- HH -->