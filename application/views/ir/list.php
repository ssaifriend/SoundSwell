<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="utf-8">
	<title>소리너울 - SoundSwell</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script type="text/javascript" src="/resource/script/ir.js"></script>
    <link rel="stylesheet" type="text/css" href="/resource/css/style.css"/>
    <link rel="stylesheet" type="text/css" href="/resource/css/ir.css"/>
</head>
<body>
<?php
echo '<ul>';
foreach($list as $hash => $data){
	echo '<li><a href="/ir/show/'.$hash.'">'.$data['title'].' ('.trim($data['level']).')</a></li>';
}
echo '</ul>';
?>

<div id="recentRecord">
최근 플레이 기록들
<table>
<tr><th>닉네임</th><th>곡</th><th>기록</th><th>옵션</th><th>시간</th></tr>
<?php
	for($a=0,$loopa=$RecordModel->getTotal(); $a<$loopa; $a++){
		$record = $RecordModel->get();
		$optionList = array();
		if($record->nBPM!=100) $optionList[] = 'BPM: '.($record->nBPM>100?'+':"").($record->nBPM-100).'%';
		if($record->emNote!="N") $optionList[] = $record->emNote;
		if($record->emDead!="N") $optionList[] = "폭사";
?>
<tr><td><?php echo $record->vcNickName;?></td><td><?php echo $list[$record->cBMS]['title'];?> (<?php echo trim($list[$record->cBMS]['level']);?>)</td><td><?php echo number_format($record->nScore);?> (<?php echo $record->fGrade;?>%)</td><td><?php echo join(", ",$optionList);?></td><td><?php echo $record->dtEnd;?></td></tr>
<?php
		$RecordModel->next();
	}
?>
</table>
</div>
<span id="close">닫기</span>
</body>
</html>