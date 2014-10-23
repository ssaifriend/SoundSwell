<?php $this->load->view('common/header',array('js'=>array('board'),'css'=>array('board'))); ?>
<?php $this->load->view('common/header_common',array('active'=>$board)); ?>
<div id="page-content-wrapper">
	<div class="content-header">
		<h3 class="section-heading"><?php echo $title;?> 목록</h3>
	</div>
	<div class="page-content inset">
	<div class="row">
		<div class="col-lg-6">
			<table class="table table-striped">
			<col width="10%" /><col width="*" /><col width="15%" /><col width="10%" /><col width="15%" />
			<tr><th>순번</th><th>제목</th><th>작성자</th><th>조회수</th><th>작성일</th></tr>
			<?php
				$now = date("Y-m-d");
				$max = $start + sizeof($list);
				for($a=0,$loopa=sizeof($list); $a<$loopa; $a++){
					$record = $list[$a];
					if($record->emNotice=="Y") $num = "공지";
					else $num = number_format($max--);
			?>
			<tr>
				<td><?php echo $num;?></td>
				<td><a href="/board/<?php echo $board;?>/<?php echo $record->nSeqNo;?>"><?php echo mb_substr($record->vcTitle,0,50);?> <?php echo ($record->cmt>0?"(".number_format($record->cmt).")":"");?></a></td>
				<td><?php echo $record->vcNickname;?></td>
				<td><?php echo number_format($record->nHit);?></td>
				<td><?php echo (substr($record->dtRegdate,0,10)==$now?substr($record->dtRegdate,11):substr($record->dtRegdate,0,10));?></td>
			</tr>
			<?php } ?>
			</table>
		</div>
	</div>
	</div>

	<div class="page-content inset">
	<div class="row">
		<div class="button tr col-lg-offset-5 col-lg-1">
			<button type="button" class="btn btn-default listButton">글쓰기</button>
		</div>
	</div>
	</div>

	<div class="page-content inset">
	<div class="row">
		<div class="page tc col-lg-6">
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
<?php $this->load->view("board/write",array("board"=>$board)); ?>
<?php $this->load->view('common/footer'); ?>