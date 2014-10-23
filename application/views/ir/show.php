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
<body id="show">
<?php $replacer = array("key1"=>"ZXC","key2"=>"SDF","key3"=>"사용자"); ?>
<?php echo $info['ARTIST'];?> - <?php echo $info['TITLE']; ?> (<?php echo $info['BPM'];?> BPM, <?php echo $info['PLAYLEVEL'];?>) 키설정: <?php echo $replacer[$key];?> 타입 &nbsp; Total: <?php echo $total;?>명<br />
다른 타입 랭킹 조회: 
<?php foreach($replacer as $type => $text){ ?>
<a href="/ir/show/<?php echo $hash;?>/<?php echo $type;?>"><?php echo $text;?> 타입</a>
<?php } ?>
<table>
<tr><th></th><th>Nickname</th><th>Score</th><th>Judge</th></tr>
<?php
    for($a=0,$loopa=$RankModel->getTotal(); $a<$loopa; $a++){
        $record = $RankModel->get();
?>
<tr><td><?php echo ++$start;?></td><td><?php echo $record->vcNickName;?></td><td><?php echo number_format($record->nScore);?></td><td><?php echo $record->fGrade;?>%</td></tr>
<?php
        $RankModel->next();
    }
?>
</table>
<div class="page">
<?php
$perpage = 15;
$totalPage = ceil($total/$perpage);
$nowPage = $page;
$str = '';

if($nowPage > 1){
    $startPage = floor(($nowPage-1)/10)*10+1;
}else{
    $startPage = 1;
}

if( (floor(($nowPage-1)/10) +1)*10 < $totalPage ){
    $endPage = (floor(($nowPage-1)/10) +1) * 10;
}else{
    $endPage = $totalPage;
}

if(10 <= $startPage ) $str .= ' <a href="'.$_SERVER['PHP_SELF'].'?page='.($startPage-1).'">&lt;</a> &nbsp; ';
else $str .= '&lt; &nbsp; ';

for($a=$startPage; $a<=$endPage; $a++){
    if($a==$nowPage) $str .= ' <span style="font-weight:bold;">'.$a.'</span> &nbsp; ';
    else $str .= ' <a href="'.$_SERVER['PHP_SELF'].'?page='.$a.'">'.$a.'</a> &nbsp; ';
}

if($endPage < $totalPage ) $str .= ' <a href="'.$_SERVER['PHP_SELF'].'?page='.$endPage.'">&gt;</a> &nbsp; ';
else $str .= '&gt; &nbsp; ';

echo $str;
?>
</div>
<span id="close">닫기</span>
<p><a href="/ir/bms/">목록으로</a></p>
</body>
</html>
