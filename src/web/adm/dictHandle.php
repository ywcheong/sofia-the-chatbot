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
    require_once('table.php');
    require_once('../supply/head.php');
    require_once('phase.php');
    $errorMessage = array();

    if(isset($_FILES["newDictData"])){
        $filedata = $_FILES["newDictData"];
        $target_dir = $_SERVER['DOCUMENT_ROOT']."/data/";
        $target_file = $target_dir."translate_guideline.txt";
        
        if ( $filedata["error"] ){
            array_push($errorMessage, "RECEIVE_CHANNEL_ERROR");
        }

        // 파일크기 제한
        if ($filedata["size"] > 512 * 1024) {
            array_push($errorMessage, "524KB, 512KiB(512*1024Byte)를 초과하는 파일은 업로드 금지되어 있습니다.");
        }
        
        // 파일 내용 분석 및 올바른 파싱 여부 확인. structure.php의 loadDict와 구조적 유사
        $meta = array();
        if(count($errorMessage) == 0){
            $_fp = fopen($filedata["tmp_name"], 'r');
            $num = 0;

            while(!feof($_fp)){
                $num++;
                $_tmp = trimNewline(fgets($_fp));
                
                if($_tmp[0]=="#" || strlen($_tmp)==0) continue;

                if($_tmp[0]=="@"){
                    // Metadata Analysis
                    // Possible metadata: DATE
                    $_tmparr = explode('=', $_tmp);
                    if( count($_tmparr) != 2 ){
                        $uploadOk = 0;
                        array_push($errorMessage, $num."번 줄의 메타데이터 태그가 잘못되었습니다. 메타데이터 태그는 @ METANAME = METAVALUE 꼴이어야 합니다.");
                    }
                    $_info = trimNewline(str_replace("@", "", $_tmparr[0]));
                    // if($_info == "VER"){
                    //     $newvernum = trimNewline($_tmparr[1]);
                    //     $oldvernum = $MASTER->getAction('dictClass')->getMeta('VER');
                    //     if(!is_numeric($newvernum) || (int)$newvernum <= 0) array_push($errorMessage, "@VER 태그의 값은 자연수여야 합니다.");
                    //     else if(((int)$oldvernum) != ((int)$newvernum - 1)){
                    //         array_push($errorMessage, "@VER 태그의 값은 기존의 파일보다 1 커져야 합니다. 편집한 파일이 최신 파일인지 확인해 주세요.");
                    //     }
                    // }
                    array_push($meta, $_info);
                    continue;
                }
                
                $_tmparr = explode('>', $_tmp);
                if( count($_tmparr) != 2 ){
                    array_push($errorMessage, $num."번 줄의 구성이 잘못되었습니다.");
                }
            }
            
            fclose($_fp);
        }
        
        $reqMeta = explode(',', $MASTER->getAction('REQUIRED_METADATA'));

        if(count($reqMeta) != count(array_intersect($reqMeta, $meta))){
            array_push($errorMessage, "누락된 메타데이터 태그가 있습니다. 필요한 태그: ".$MASTER->getAction('REQUIRED_METADATA'));
        }

        

        if(count($errorMessage) > 0){
            $notify = '<p style="font-weight: bold; font-size: 2rem; color: white; text-decoration: underline;">작업 실패: 파일 해석 중 오류 생김!</p><div class="simb"></div>';
            for($i = 0; $i < count($errorMessage); $i++){
                $notify .= '<p style="color: #00AB8E; background-color: white;">'.$errorMessage[$i].'</p><div class="simb"></div>';
                if($i >= 4) break;
            }
            $notify .= '<p style="color: white;">모두 '.(count($errorMessage)).'개의 오류가 탐지되었습니다. (첫 5개의 오류만 표시됩니다)</p><div class="simb"></div>';
        } else {
            
            $timestamp = new DateTime("now", new DateTimeZone('Asia/Seoul'));
            $timestamp = $timestamp->getTimestamp();
            copy($target_file, $target_dir."olddict/tg-".$timestamp.".txt.backup");
            chmod($target_dir."olddict/tg-".$timestamp.".txt.backup", 0777);
            $_dicfp = fopen($target_dir."/buffered.txt", 'w');
            $_fromfp = fopen($filedata["tmp_name"], 'r');

            while(!feof($_fromfp)){
                //$_tmp = iconv("EUC-KR", "UTF-8", trimNewline(fgets($_fromfp)));
                $_tmp = trimNewline(fgets($_fromfp));
                fwrite($_dicfp, $_tmp."\r\n");
            }

            fclose($_dictfp);
            fclose($_fromfp);

            $notify = '<p style="font-weight: bold; font-size: 2rem; color: white; text-decoration: underline;">성공적으로 업데이트되었습니다. 업로드된 파일에 필수 설정을 해주세요.</p>';
        }
    }else if(isset($_REQUEST["encodetype"])){
        $encodetype = $_REQUEST["encodetype"];
        if($encodetype != "utf8" && $encodetype != "euckr" && $encodetype != "cancel"){
            $notify = '<p style="font-weight: bold; font-size: 2rem; color: white; text-decoration: underline;">잘못된 선택 인자입니다.</p>';
        }

        if($encodetype == "euckr"){
            $_dicfp = fopen($_SERVER['DOCUMENT_ROOT']."/data/translate_guideline.txt", 'w');
            $_fromfp = fopen($_SERVER['DOCUMENT_ROOT']."/data/buffered.txt", 'r');

            while(!feof($_fromfp)){
                $_tmp = iconv("EUC-KR", "UTF-8", trimNewline(fgets($_fromfp)));
                //$_tmp = trimNewline(fgets($_fromfp));
                fwrite($_dicfp, $_tmp."\r\n");
            }

            fclose($_dictfp);
            fclose($_fromfp);

        }else if($encodetype == "utf8"){
            copy($_SERVER['DOCUMENT_ROOT']."/data/buffered.txt", $_SERVER['DOCUMENT_ROOT']."/data/translate_guideline.txt");
        }

        unlink($_SERVER['DOCUMENT_ROOT']."/data/buffered.txt");
        header('Location: dict.php');

    }

    if(file_exists($_SERVER['DOCUMENT_ROOT']."/data/buffered.txt")){
        $_SESSION['NEED_FILESET'] = TRUE;
    }else{
        $_SESSION['NEED_FILESET'] = FALSE;
    }


?>

</div>
<div class="break"></div>
<?php echo $notify; ?>
<div class="break"></div>
<?php
    if($_SESSION['NEED_FILESET']){
        $_bfp = fopen($_SERVER['DOCUMENT_ROOT']."/data/buffered.txt", 'r');
        $showstr = "";
        $num = 0;

        while(!feof($_bfp)){
            $_tmp = trimNewline(fgets($_bfp));
            if($_tmp[0]=="#" || strlen($_tmp)==0) continue;
            
            $num++;
            $showstr .= $_tmp."<br />";
            if($num >= 5) break;
        }

        $showstr .= "...";
 
        fclose($_bfp);

        echo '
        <div class="row" style="width: 1170px;"> 
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title" style="color: rgb(0, 75, 62);">이 샘플 텍스트가 정상적으로 보입니다(UTF-8)</h5>
                        <p class="card-text" style="color:grey;">'.$showstr.'</p>
                        <br />
                        <a href="#" class="smallButton" onclick=\'$("#encodetype").val("utf8"); document.forms["setencode"].submit(); return false;\'>인코딩 확정</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title" style="color: rgb(0, 75, 62);">이 샘플 텍스트가 정상적으로 보입니다(EUC-KR)</h5>
                        <p class="card-text" style="color:grey;">'.iconv("EUC-KR", "UTF-8", $showstr).'</p>
                        <br />
                        <a class="smallButton" onclick=\'$("#encodetype").val("euckr"); document.forms["setencode"].submit(); return false;\'>인코딩 확정</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title" style="color: rgb(0, 75, 62);">개정을 취소합니다</h5>
                        <p class="card-text" style="color:grey;">잘못된 파일이었습니다. 개정하지 않겠습니다.</p>
                        <br />
                        <a class="smallButton" onclick=\'$("#encodetype").val("cancel"); document.forms["setencode"].submit(); return false;\'>인코딩 취소</a>
                    </div>
                </div>
            </div>
      </div>
      <form action="dictHandle.php" method="post" name="setencode">
          <input type="hidden" name="encodetype" value="" id="encodetype" />
      </form>';

    }else{
        echo '<a href="dict.php" class="myButton"><h3>가이드라인으로 돌아가기</h3></a>
        <div class="break"></div>
        <form action="dictHandle.php" method="post" enctype="multipart/form-data" name="dicfile">
            <div class="input-file-container">  
                <input class="input-file" name="newDictData" id="tracefile" type="file">
                <label tabindex="0" for="tracefile" class="input-file-trigger myButtonForFile file-return">번역 가이드라인 파일 선택...</label>
            </div>
            <input type="hidden" name="formsend" value="yes" />
        </form>
        <a class="myButton turnoff" id="uploadtrack" onclick=\'document.forms["dicfile"].submit(); return false;\'><h3>업로드하기</h3></a>';
    }
?>
<div class="break"></div>
<div class="wrap-table100" id="datatable">
<script>

    document.querySelector("html").classList.add('js');
    var fileInput  = document.querySelector( ".input-file" ),  
        button     = document.querySelector( ".input-file-trigger" ),
        the_return = document.querySelector(".file-return");
        
    button.addEventListener( "keydown", function( event ) {  
        if ( event.keyCode == 13 || event.keyCode == 32 ) {  
            fileInput.focus();  
        }  
    });

    button.addEventListener( "click", function( event ) {
    fileInput.focus();
    return false;
    });  

    fileInput.addEventListener( "change", function( event ) {  
        var fullPath = this.value;
        if (fullPath) {
            var startIndex = (fullPath.indexOf('\\') >= 0 ? fullPath.lastIndexOf('\\') : fullPath.lastIndexOf('/'));
            var filename = fullPath.substring(startIndex);
            if (filename.indexOf('\\') === 0 || filename.indexOf('/') === 0) {
                filename = filename.substring(1);
            }
        }
        the_return.innerHTML = filename + "을 선택함...";  
        $('#uploadtrack').removeClass("turnoff");
        $('.input-file-trigger').css("font-style", "italic");
    });  
</script>
<?php
    function push_log($message){
        global $MASTER;
        $_DOCUMENT_ROOT_ = $_SERVER['DOCUMENT_ROOT'];
        $_fp = fopen("$_DOCUMENT_ROOT_/report/log.dat", 'a');

        $date = new DateTime("now", new DateTimeZone('Asia/Seoul'));
        $log = ($date->format('Y-m-d H:i:s'))." ";

        $log = "$log | $message";
        fwrite($_fp, $log."\n");
        fclose($_fp);
        return TRUE;
    }

    push_log((count($errorMessage) > 0 ? "FAIL" : "COMP")." | ADMINACT=>".$_SESSION['uuid'].";".$MASTER->getAllInput());
    require('../supply/foot.php');
    ob_end_flush();
?>