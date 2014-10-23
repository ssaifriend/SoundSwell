<?php $this->load->view('common/header',array('js'=>array('play'),'css'=>array('play'))); ?>
<?php $this->load->view('common/header_common',array('active'=>'game')); ?>
<div id="page-content-wrapper">
	<div class="content-header">
		<h3 class="section-heading">게임 플레이</h3>
	</div>
<?php
$group = array();
foreach($list as $hash => $data){
	if(!isset($group[$data['group']])) {
		$group[$data['group']] = array('loadImage'=>$data['loadImage'], 'list'=>array());
	}
	$group[$data['group']]['list'][] = array_merge($data,array('hash'=>$hash));
}
foreach($group as $group_name => $d){
	if(sizeof($d['list']) == 1) {
		$group[$group_name]['title'] = $d['list'][0]['title'];
	} else {
		$continue = true;
		$len = 1;
		while($continue){
			$tmp = null;
			foreach($d['list'] as $l){
				if($tmp == null) $tmp = mb_substr($l['title'],0,$len);
				else if($tmp != mb_substr($l['title'],0,$len)){
					$continue = false;
					$len--;
					break(2);
				}
			}
			if(!$tmp) break;
			if($len>mb_strlen($l['title'])) break;
			$len++;
		}
		$title = mb_substr($l['title'], 0, $len);
		if(mb_substr($title,-1) == '[') $title = mb_substr($title,0,-1);
		$group[$group_name]['title'] = trim($title);
	}
}
echo '<ul class="list-unstyled">';
foreach($group as $group_name => $data){
	echo '<li class="col-lg-2">
		<a href="/game/bmslist/'.$group_name.'" class="thumbnail">
		  <img data-src="holder.js/100%x100%" src="'.($data['loadImage']?'/data/'.$group_name.'/'.$data['loadImage']:'/images/no_image.png').'" alt="">
		  <div class="caption">
		  '.$data['title'].'
		  </div>
		</a>
		</li>';
}
echo '</ul>';
?>
<div id="selectBMS" class="panel panel-default">
  <div class="panel-heading">
    <h3 class="panel-title"></h3>
	<button type="button" class="close">&times;</button>
  </div>
  <div class="panel-body">
	<div class="image"></div>
	<div class="list">
		<ul class="nav nav-tabs tab">
			<li class="active bmslist"><a href="#">BMS 목록</a></li>
			<li class="option"><a href="#">게임 옵션</a></li>
		</ul>
		<ul class="nav nav-stacked">
		</ul>
		<ul id="gameOption" class="list-unstyled">
			<li><p>키 설정 변경</p>
				<div class="keySelect btn-group btn-group-justified" data-toggle="buttons">
					<label class="btn btn-default key1"><input type="radio" name="keySelect" value="key1" />ZXC 타입</label>
					<label class="btn btn-default key2"><input type="radio" name="keySelect" value="key2" />SDF 타입</label>
					<label class="btn btn-default key3"><input type="radio" name="keySelect" value="key3" />사용자 설정</label>
				</div>
			</li>
			<li><p>노트 배치</p>
				<div class="noteSelect btn-group btn-group-justified" data-toggle="buttons">
					<label class="btn btn-default Normal"><input type="radio" name="noteSelect" value="Normal" />일반</label>
					<label class="btn btn-default Random"><input type="radio" name="noteSelect" value="Random" />랜덤</label>
					<label class="btn btn-default Mirror"><input type="radio" name="noteSelect" value="Mirror" />미러</label>
					<label class="btn btn-default SuperRandom"><input type="radio" name="noteSelect" value="SuperRandom" />슈랜</label>
					<label class="btn btn-default HyperRandom"><input type="radio" name="noteSelect" value="HyperRandom" />하랜</label>
				</div>
			</li>
			<li><p>AUTO Play</p>
				<div class="autoplay btn-group btn-group-justified" data-toggle="buttons">
					<label class="btn btn-default none"><input type="radio" name="autoplay" value="none" />없음</label>
					<label class="btn btn-default sc"><input type="radio" name="autoplay" value="sc" />스크래치</label>
					<label class="btn btn-default all"><input type="radio" name="autoplay" value="all" />전부</label>
				</div>
			</li>
			<li><p>BGA On/OFF</p>
				<div class="bga btn-group btn-group-justified" data-toggle="buttons">
					<label class="btn btn-default bgaon"><input type="radio" name="bga" value="bgaon" />ON</label>
					<label class="btn btn-default bgaoff"><input type="radio" name="bga" value="bgaoff" />OFF</label>
				</div>
			</li>
			<li><p>BGA 위치조절</p>
				<ul class="bgaPosition">
					<li class="init btn btn-default">초기화</li>
					<li class="setting btn btn-default">설정</li>
				</ul>
			</li>
			<li>Volume<br /><input type="range" name="volume" min="0" max="100" step="5" /> <input type="text" name="displayvolume" size="1" /></li>
			<li>BPM변경<br /><input type="range" name="bpm" min="-50" max="50" step="1" value="0" /> <input type="text" name="displaybpm" value="0%" size="1" /></li>
		</ul>
	</div>
	<div id="keyUserInput">
		초기화를 선택하고 차례대로 키를 입력하시거나,<br />
		입력하고자 하는 키를 선택하시고 키를 입력하시면 변경 됩니다.<br />
		특수키도 입력은 가능하지만 아래 정상적으로 표시되지 않습니다.
		<table>
		<tr><th>스크래치</th><th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th></tr>
		<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
		</table>
		<p><span>초기화</span> <span>확인</span> <span>취소</span></p>
	</div>
  </div>
</div>
<div id="bgaPosition">
	<div class="bgaPattern"></div>
	<span>게임 내에서 BGA가 표시될 위치를 설정하세요.<br />아래 검은 영역을 드래그 하시면 이동이 되고,<br />오른쪽 아래의 사각 버튼으로 크기 조절이 가능합니다.<br />설정이 끝나셨으면 <span>여기</span>를 눌러 설정을 마치세요.</span>
	<div class="bgaSelect">BGA 표시 영역<div></div></div>
</div>

</div>
<?php $this->load->view('common/footer'); ?>