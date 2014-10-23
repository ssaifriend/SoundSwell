<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="utf-8">
	<title>소리너울 - SoundSwell</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script type="text/javascript" src="/resource/script/play.js"></script>
    <link rel="stylesheet" type="text/css" href="/resource/css/style.css"/>
    <link rel="stylesheet" type="text/css" href="/resource/css/play.css"/>
</head>
<body id="result">
결과<br />
Swell: <?php echo number_format($record->nSwell);?><br />
Well: <?php echo number_format($record->nWell);?><br />
Good: <?php echo number_format($record->nGood);?><br />
Bad: <?php echo number_format($record->nBad);?><br />
Miss: <?php echo number_format($record->nMiss);?><br /><br />
Total Score: <?php echo number_format($record->nScore);?> (<?php echo $record->fGrade;?>%) <br />
<br />
<?php if($rank->nScore) { ?>
내 최고 기록<br />
<?php echo number_format($rank->nScore);?> (<?php echo $rank->fGrade;?>%) <br />
<?php } ?>
<span>닫기</span>
<p><a href="/game/bms/">목록으로</a></p>
</body>
</html>
