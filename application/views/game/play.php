<!DOCTYPE html>
<html lang="ko">
<head>
	<meta charset="utf-8">
	<title>소리너울 - SoundSwell</title>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
    <script type="text/javascript" src="/resource/script/play.js"></script>
    <script type="text/javascript">
    var url = "/data/<?php echo $data['group'];?>/";
    var firstTime = <?php echo $bmsInfo['firstTime'];?>;
    var hash = "<?php echo $hash;?>";
    </script>
    <script type="text/javascript" src="/resource/playScript/game.js"></script>
    <link rel="stylesheet" type="text/css" href="/resource/css/style.css"/>
    <link rel="stylesheet" type="text/css" href="/resource/css/play.css"/>
</head>
<body>
<div id="loadStatus" class="load"></div>
<div id="loadStatus2" class="load"></div>
<div id="loadingImage"></div>
<div id="errorLog"></div>
<canvas id="BGA" width="256" height="256">
</canvas>
<canvas id="BGALayer" width="256" height="256">
</canvas>
<canvas id="Play" width="700" height="620"></canvas>
<canvas id="Gear" width="700" height="780"></canvas>
<canvas id="Score" width="120" height="100"></canvas>
<canvas id="Speed" width="100" height="30"></canvas>
<canvas id="Grade" width="100" height="180"></canvas>
<div id="fps"></div>
<div id="playStatus">
	<span>-0:00</span>
	<div>
		<div></div>
	</div>
	<span>99:99</span>
</div>
</body>
</html>