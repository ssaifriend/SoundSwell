<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="utf-8">
	<title>소리너울 - SoundSwell</title>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<script type="text/javascript" src="/resource/script/bootstrap.min.js"></script>
	<script type="text/javascript" src="/resource/script/common.js"></script>
<?php
if(isset($js)){
	foreach($js as $j){
?>
	<script type="text/javascript" src="/resource/script/<?php echo $j?>.js"></script>
<?php 
	}
}
?>

	<link rel="stylesheet" type="text/css" href="/resource/css/bootstrap.css"/>
	<link rel="stylesheet" type="text/css" href="/resource/css/style.css"/>
<?php
if(isset($css)){
	foreach($css as $c){
?>
	<link rel="stylesheet" type="text/css" href="/resource/css/<?php echo $c;?>.css"/>
<?php 
	}
}
?>
</head>
<body>
<div id="wrapper">