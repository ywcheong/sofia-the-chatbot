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
        var requser = null;
        var acpuser = null;
        var team = null;
        var isallok = true;
        $.ajax({
            url: 'serve.php',
            data:{
                "action": "member",
                "sideaction": "generate"
            },
            type: 'post',
            success:function(data){
                $.notify("성공적으로 로딩되었습니다!", "success");
                $('#datatable').empty();
                $('#datatable').append(data['back']);
                $('.select').css('color','gray');
            },
            error: function (jqXHR, textStatus, errorThrown){
                $.notify("반영에 실패했습니다. (오류: ".concat(decodeURI(jqXHR.statusText).replace(/\+/gi, ' ')).concat(")"), "error");
            },
            dataType: 'json'
        });
    });

    $(document).on('click', "a", function(){
    if($(this).attr('class').includes('smallButton') && !$(this).attr('class').includes('send')){
        var targetSideActionLis = $(this).attr('class').split(' ');
        for(var i=0; i<targetSideActionLis.length; i++){
            if(targetSideActionLis[i].includes('-')){
                var targetSideAction = targetSideActionLis[i];
                break;
            }
        }
        console.log(targetSideAction);
        if(targetSideAction == "ru-acpall" || targetSideAction == "ru-revall"){
            var isConf = confirm('정말 모두를 승인합니까?');
            if(!isConf){
                $.notify("취소되었습니다.", "info");
                return;
            }
        } else if(targetSideAction == "au-remove"){
            var isConf = confirm('정말로 해당 번역버디를 삭제하시겠습니까? 작업이 하나라도 배정되거나 완료된 버디는 삭제할 수 없습니다.');
            if(!isConf){
                $.notify("취소되었습니다.", "info");
                return;
            }
        }
        $.ajax({
            url: 'serve.php',
            data:{
                "action": "member",
                "sideaction": targetSideAction,
                "moredata": $(this).parent().attr('id')
            },
            type: 'post',
            success:function(data){
                console.log(data['echo']);
                $.notify("성공적으로 반영되었습니다!", "success");
                $('#datatable').empty();
                $('#datatable').append(data['back']);
                $('.select').css('color','gray');
                if($('#tablefocus').length == 1) location.hash = "#tablefocus";
                else location.hash = "";
            },
            error: function (jqXHR, textStatus, errorThrown){
                $.notify("반영에 실패했습니다. (오류: ".concat(decodeURI(jqXHR.statusText).replace(/\+/gi, ' ')).concat(")"), "error");
            },
            dataType: 'json'
        })
    }else if($(this).attr('class').includes('send-team')){
        if($('#T-ID1').val() == ""){
            $.notify("필수 양식이 누락되었습니다.", "warn");
        }else{
            $.ajax({
                url: 'serve.php',
                data:{
                    "action": "member",
                    "sideaction": "t-make",
                    "T-ID1": $('#T-ID1').val(),
                    "T-ID2": $('#T-ID2').val(),
                    "T-UID": $('#TEAM-ID').val(),
                },
                type: 'post',
                success:function(data){
                    console.log(data['echo']);
                    $.notify("성공적으로 반영되었습니다!", "success");
                    $('#datatable').empty();
                    $('#datatable').append(data['back']);
                    $('.select').css('color','gray');
                },
                error: function (jqXHR, textStatus, errorThrown){
                    $.notify("반영에 실패했습니다. (오류: ".concat(decodeURI(jqXHR.statusText).replace(/\+/gi, ' ')).concat(")"), "error");
                },
                dataType: 'json'
            })
        }
    }else if($(this).attr('class').includes('send-spe')){
        if($('#U-SPE').val() == ""){
            $.notify("필수 양식이 누락되었습니다.", "warn");
        }else{
            $.ajax({
                url: 'serve.php',
                data:{
                    "action": "member",
                    "sideaction": "au-givetime",
                    "U-SPE": $('#U-SPE').val(),
                    "moredata": $(this).parent().attr('id')
                },
                type: 'post',
                success:function(data){
                    console.log(data['echo']);
                    $.notify("성공적으로 반영되었습니다!", "success");
                    $('#datatable').empty();
                    $('#datatable').append(data['back']);
                    $('.select').css('color','gray');
                },
                error: function (jqXHR, textStatus, errorThrown){
                    $.notify("반영에 실패했습니다. (오류: ".concat(decodeURI(jqXHR.statusText).replace(/\+/gi, ' ')).concat(")"), "error");
                },
                dataType: 'json'
            })
        }
    }
    });

    $(document).ajaxStart(function() {
        $.notify("데이터를 갱신 중입니다...", "info");
    });
</script>
<div class="break"></div>
</div><a href="admin.php" class="myButton"><h3>돌아가기</h3></a>
<div class="wrap-table100" id="datatable"><div class="break"></div>
<?php
    require('../supply/foot.php');
?>