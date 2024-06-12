<?php
	session_start();
	require_once('session_timeout.php');
	if($_SESSION['admin'] && $_SESSION['viewonly']){
		$VAR1 = $MASTER->getAction('userClass')->getUserByUUID($_SESSION['uuid'])->name()." 님, 환영합니다.";
		$VAR2 = "<span style=\"color: #00AB8E; background-color: white;\">Sofia 번역버디 모드 - 보기 전용</span>";
		$VAR3 = "자동 로그아웃까지 ".SecondToText(abs($MASTER->getAction('ADMIN_SESSION_TIMEOUT') + $_SESSION['begintime'] - (new DateTime("now", new DateTimeZone('Asia/Seoul')))->getTimestamp())).' 남았습니다.';
	}else if($_SESSION['admin']){
		$VAR1 = $MASTER->getAction('userClass')->getUserByUUID($_SESSION['uuid'])->name()." 님, 환영합니다.";
		$VAR2 = "Sofia 관리자 페이지";
		$VAR3 = "자동 로그아웃까지 ".SecondToText(abs($MASTER->getAction('ADMIN_SESSION_TIMEOUT') + $_SESSION['begintime'] - (new DateTime("now", new DateTimeZone('Asia/Seoul')))->getTimestamp())).' 남았습니다.';
	}else{
		$VAR1 = "Sofia 관리자 페이지";
		$VAR2 = "KSA 국제부 번역버디 배정 시스템";
		if($_SESSION['is_timeout'])
			$VAR3 = "자동 로그아웃 되었습니다.";
		else $VAR3 = "로그인되지 않았습니다.";
	}

	// $userList = &$MASTER->getAction('userClass')->allUser();
	// $adminlist = "";
	// $isFirst = TRUE;
	// for($i=0; $i < count($userList); $i++){
	// 	if($userList[$i]->isAdmin()){
	// 		if(!$isFirst) $adminlist .= ", ";
	// 		$isFirst = FALSE;
	// 		$adminlist .= $userList[$i]->id();
	// 	}
	// }
	
?><!DOCTYPE html>
<html lang="ko">
<head>
	<title>Sofia : KSA 국제부</title>
	<meta content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">	
	<link rel="icon" type="image/png" href="../favicon/favicon-16.png"/>
	<link rel="icon" type="image/png" href="../favicon/favicon-32.png"/>
	<link rel="icon" type="image/png" href="../favicon/favicon-152.png"/>
	<link rel="stylesheet" type="text/css" href="../vendor/bootstrap/css/bootstrap.min.css">	
	<link rel="stylesheet" type="text/css" href="../vendor/animate/animate.css">
	<link rel="stylesheet" type="text/css" href="../vendor/select2/select2.min.css">
	<link rel="stylesheet" type="text/css" href="../vendor/perfect-scrollbar/perfect-scrollbar.css">
	<link href="https://fonts.googleapis.com/css?family=Noto+Sans+KR:400,700&display=swap&subset=korean" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="../css/util.css">
	<link rel="stylesheet" type="text/css" href="../css/main.css">
	<script src="../vendor/jquery/jquery-3.2.1.min.js"></script>
	<script src="../vendor/bootstrap/js/popper.js"></script>
	<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
	<script src="../vendor/select2/select2.min.js"></script>
	<script src="../vendor/perfect-scrollbar/perfect-scrollbar.min.js"></script>
	<script>
		$('.js-pscroll').each(function(){
			var ps = new PerfectScrollbar(this);

			$(window).on('resize', function(){
				ps.update();
			})
		});

		$( document ).ready(function() {
			if( $('#dettag').html() == "자동 로그아웃 되었습니다."){
				$.notify("자동 로그아웃 되었습니다.", "warn");
			}
		});
		
		function adminlist(){
			alert("현재 등록된 관리자: <?php echo $adminlist; ?>");
		}

	</script>
	<style>
	.sourcelink:hover {
		background-color: gray;
		color: white !important;
	}
	</style>
	<script src="../js/main.js"></script>
</head>
<body>
	
	<div class="limiter">
		<div class="container-table100">
		<img src="../css/SofiaTransparentWhite.png" height="100rem"/>
		<div class="break"></div>
		<p style="text-align: center; font-size: 2rem; font-weight: bold;"><?php echo $VAR1; ?></p>
		<div class="simb"></div>
		<p style="text-align: center; font-size: 1rem; font-weight: bold;"><?php echo $VAR2; ?></p>
		<div class="simb"></div>
		<p style="text-align: center; font-size: 1rem; font-weight: bold;" id="dettag"><?php echo $VAR3; ?></p>
		<div class="break"></div>
		<?php
			if(!$_SESSION['admin']) echo '<a class="myButton" href="'.$MASTER->getAction('KAKAO_LINK').'"><h3>챗봇 가입</h3></a>';
			// if(!$_SESSION['admin']) echo '<a class="myButton" href="../../data/Sofia-2020-01-Report.pdf"><h3>Sofia 운영보고서</h3></a>';
			//echo '<span class="myButton" onclick="adminlist();"><h3>현재 관리자 목록</h3></span>';
		?>
		<div class="wrap-table100">