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
        $.ajax({
            url: 'serve.php',
            data:{
                "action": "dict",
                "sideaction": "generate"
            },
            type: 'post',
            success:function(data){
                $.notify("성공적으로 로딩되었습니다!", "success");
                $('#datatable').empty();
                $('#datatable').append(generateTable(data));
                $('.select').css('color','gray');
            },
            error: function (jqXHR, textStatus, errorThrown){
                $.notify("데이터 로딩에 실패했습니다. 새로고침 해주세요.", "error");
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
        
        if(targetSideAction == "d-focus"){
            //TODO
        }else{
            $.ajax({
                url: 'serve.php',
                data:{
                    "action": "dict",
                    "sideaction": targetSideAction,
                    "moredata": $(this).parent().attr('id')
                },
                type: 'post',
                success:function(data){
                    $.notify("성공적으로 반영되었습니다!", "success");
                    $('#datatable').empty();
                    $('#datatable').append(generateTable(data));
                    $('.select').css('color','gray');
                },
                error: function (jqXHR, textStatus, errorThrown){
                    $.notify("반영에 실패했습니다. (오류: ".concat(decodeURI(jqXHR.statusText).replace(/\+/gi, ' ')).concat(")"), "error");
                },
                dataType: 'json'
            });
        }
    }else if($(this).attr('class').includes('send-data')){
        if($('#D-FROM').val() == "" || $('#D-TO').val() == ""){
            $.notify("필수 양식이 누락되었습니다.", "warn");
        }else{
            $.ajax({
                url: 'serve.php',
                data:{
                    "action": "member",
                    "sideaction": "t-make",
                    "D-FROM": $('#D-FROM').val(),
                    "D-TO": $('#D-TO').val(),
                },
                type: 'post',
                success:function(data){
                    $.notify("성공적으로 반영되었습니다!", "success");
                    $('#datatable').empty();
                    $('#datatable').append(generateTable(data));
                    $('.select').css('color','gray');
                },
                error: function (jqXHR, textStatus, errorThrown){
                    $.notify("반영에 실패했습니다. (오류: ".concat(decodeURI(jqXHR.statusText).replace(/\+/gi, ' ')).concat(")"), "error");
                },
                dataType: 'json'
            });
        }
    }
    });

    $(document).ajaxStart(function() {
        $.notify("데이터를 갱신 중입니다...", "info");
    });

    function jointArr(arr){
        var record = "";
        for(var i=0; i<arr.length; i++){
            record += "<span style=\"color: #00AB8E\">" + arr[i] + "</span>";
            if(i != arr.length - 1) record += " & ";
        }
        return record;
    }

    function generateTable(dictData){
        var record = '\
                <h2 style="margin-bottom: 10px;">번역 가이드라인 보기모드</h2>\
                <div class="table100 ver1 m-b-50">\
                <div class="table100-head">\
                <table>\
                <thead>\
                <tr class="row100 head">\
                <th class="cell100 columna">No.</th>\
                <th class="cell100 columnb">한국어 단어</th>\
                <th class="cell100 columnb">영어 단어</th>\
                </tr>\
                </thead>\
                </table>\
                </div>\
                <div class="table100-body js-pscroll">\
                <table>\
                <tbody>';

        for(var i=0; i<dictData.length; i++){
            record += '\
            <tr class="row100 body">\
            <td class="cell100 columna">' + i.toString() + '</td>\
            <td class="cell100 columnb">' + jointArr(dictData[i]["from"]) + '</td>\
            <td class="cell100 columnb">' + dictData[i]["to"] + '</td>\
            </tr>';
        }

        record += "</tbody></table></div></div>";

        return record;
    }
</script>
<div class="break"></div>
</div>
<a href="admin.php" class="myButton"><h3>돌아가기</h3></a>
<a href="../../data/translate_guideline.txt" class="myButton" download=""><h3>데이터 다운로드</h3></a>
<?php
    $add = "";
    if(file_exists($_SERVER['DOCUMENT_ROOT']."/data/buffered.txt")) $add = "red";
    $text = '<a href="dictUpload.php" class="myButton '.$add.'" ><h3>데이터 업로드</h3></a>';
    if(!$_SESSION['viewonly']) echo $text;
?>
<div class="break"></div>
<div class="wrap-table100" id="datatable">
<?php
    require('../supply/foot.php');
?>