<?php $this->load->view('common/header',array('js'=>array('index'),'css'=>array('index'))); ?>
<?php $this->load->view('common/header_common'); ?>
<div id="page-content-wrapper">
	<div class="content-header">
		<h1 class="section-heading">소리너울 (SoundSwell)<small> - Web based BMS Player</small></h1>
	</div>
	<div class="page-content inset">
	<div class="row">
		<div class="col-lg-12 section">
		<p class="section-paragraph">소리너울은 Chrome기반으로 작성된 BMS 구동기입니다. 온라인으로 플레이가 가능하며, 랭킹까지 지원하고 있습니다.</p>
		</div>
	</div>
	</div>
	<div class="page-content inset">
	<div class="row">
		<div class="col-lg-3">
			<div class="panel panel-info">
				<div class="panel-heading">
					<h3 class="panel-title">사용자 활동 현황</h3>
				</div>
				<div class="panel-body">
					<dl class="dl-horizontal">
						<dt>전체사용자</dt><dd><?php echo number_format($totalUser);?>명</dd>
						<dt>플레이 회수</dt><dd><?php echo number_format($totalPlayCount);?>회</dd>
						<dt>랭킹 기록 수</dt><dd><?php echo number_format($totalRankCount);?>회</dd>
					</dl>
				</div>
				<div class="panel-footer">기준시간: <?php echo date('Y-m-d H:i:s');?></div>
			</div>
		</div>
		<div class="col-lg-3">
			<div class="panel panel-info board">
				<div class="panel-heading">
					<h3 class="panel-title">게시판</h3>
					<a href="/board/free/">더보기</a>
				</div>
				<div class="panel-body">
					<ul>
					<?php
						$now = date("Y-m-d");
						$freeBoard = array_merge($freeBoardNotice,$freeBoard);
						for($a=0,$loopa=sizeof($freeBoard); $a<$loopa; $a++){
							$record = $freeBoard[$a];
					?>
						<li><span class="free_<?php echo $record->nSeqNo;?>">[<?php echo (substr($record->dtRegdate,0,10)==$now?substr($record->dtRegdate,11):substr($record->dtRegdate,0,10));?>] <?php echo mb_substr($record->vcTitle,0,15);?></span></li>
					<?php } ?>
					</ul>
				</div>
			</div>
		</div>
		<div class="col-lg-3">
			<div class="panel panel-info board">
				<div class="panel-heading">
					<h3 class="panel-title">BMS 요청</h3>
					<a href="/board/request/">더보기</a>
				</div>
				<div class="panel-body">
					<ul>
					<?php
						for($a=0,$loopa=sizeof($requestBoard); $a<$loopa; $a++){
							$record = $requestBoard[$a];
					?>
						<li><span class="request_<?php echo $record->nSeqNo;?>">[<?php echo (substr($record->dtRegdate,0,10)==$now?substr($record->dtRegdate,11):substr($record->dtRegdate,0,10));?>] <?php echo mb_substr($record->vcTitle,0,15);?></span></li>
					<?php } ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
	</div>
</div>
<?php $this->load->view('common/footer'); ?>