<?php if(!isset($active)) { $active = ''; } ?>
<div id="sidebar-wrapper">
<ul class="sidebar-nav">
	<li class="sidebar-brand"><a href="/">소리너울</a></li>
	<li<?php echo ($active == 'game'?' class="active"':'');?>><a href="/game/bms/">플레이</a></li>
	<li<?php echo ($active == 'ir'?' class="active"':'');?>><a href="/ir/bms/">랭킹</a></li>
	<li<?php echo ($active == 'mypage'?' class="active"':'');?>><a href="/mypage/main/">마이페이지</a></li>
	<li<?php echo ($active == 'free'?' class="active"':'');?>><a href="/board/free/">게시판</a></li>
	<li<?php echo ($active == 'request'?' class="active"':'');?>><a href="/board/request/">BMS 요청</a></li>
</ul>
</div>