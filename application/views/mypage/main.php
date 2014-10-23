<?php $this->load->view('common/header',array('js'=>array('mypage'),'css'=>array('mypage'))); ?>
<?php $this->load->view('common/header_common',array('active'=>'mypage')); ?>
<div id="page-content-wrapper">
	<div class="content-header">
		<h3 class="section-heading">마이페이지</h3>
	</div>
	<div class="page-content inset">
	<div class="row">
		<div class="col-lg-6">
			<form>
				<legend>개인정보 변경</legend>
				<table class="table">
					<tr><th>비밀번호 변경</th><td><input type="password" name="password" /></td></tr>
					<tr><th>Email 변경</th><td><input type="text" name="email" /></td></tr>
				</table>
				<input type="submit" class="btn btn-primary" value="변경하기" />
			</form>
		</div>
	</div>
	</div>
	<div class="page-content inset">
	<div class="row">
		<div class="col-lg-4">
			<table id="recentScore" class="table table-striped">
				<caption>내 최근 플레이 기록 (총 <?php echo number_format($playCount);?>회)</caption>
				<tr><th>곡명</th><th>기록</th><th>옵션</th><th>시각</th></tr>
			<?php
				for($a=0,$loopa=$RecordModel->getTotal(); $a<$loopa; $a++){
					$record = $RecordModel->get();
					$optionList = array();
					if($record->nBPM!=100) $optionList[] = 'BPM: '.($record->nBPM>100?'+':"").($record->nBPM-100).'%';
					if($record->emNote!="N") $optionList[] = $record->emNote;
					if($record->emDead!="N") $optionList[] = "폭사";
			?>
				<tr><td><?php echo $list[$record->cBMS]['title'];?> (<?php echo trim($list[$record->cBMS]['level']);?>)</td><td><?php echo number_format($record->nScore);?> (<?php echo $record->fGrade;?>%)</td><td><?php echo join(", ",$optionList);?></td><td><?php echo $record->dtEnd;?></td></tr>
			<?php
					$RecordModel->next();
				}
			?>
			</table>
		</div>

		<div class="col-lg-4">
			<table id="rankList" class="table table-striped">
				<caption>내 랭킹 기록</caption>
				<tr><th>곡명</th><th>기록</th><th>옵션</th><th>시각</th></tr>
			<?php
				$replacer = array("key1"=>"ZXC","key2"=>"SDF","key3"=>"사용자");
				for($a=0,$loopa=$RankModel->getTotal(); $a<$loopa; $a++){
					$record = $RankModel->get();
					$optionList = array();
					if($record->nBPM!=100) $optionList[] = 'BPM: '.($record->nBPM>100?'+':"").($record->nBPM-100).'%';
					if($record->emNote!="N") $optionList[] = $record->emNote;
					if($record->emDead!="N") $optionList[] = "폭사";
			?>
				<tr><td><?php echo $list[$record->cBMS]['title'];?> (<?php echo trim($list[$record->cBMS]['level']);?>)</td><td><?php echo number_format($record->nScore);?> (<?php echo $record->fGrade;?>%) - <?php echo number_format($record->nRank+1);?>등, <?php echo $replacer["key".$record->emKeyType]." 타입";?></td><td><?php echo join(", ",$optionList);?></td><td><?php echo $record->dtEnd;?></td></tr>
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
		</div>
	</div>
	</div>
</div>
</div>
<?php $this->load->view('common/footer'); ?>