<?php $this->load->view('common/header',array('js'=>array('board'),'css'=>array('board'))); ?>
<?php $this->load->view('common/header_common',array('active'=>$board)); ?>
<div id="page-content-wrapper">
	<div class="content-header">
		<h3 class="section-heading"><?php echo $title;?> 글 보기</h3>
	</div>
	<div class="page-content inset">
	<div id="View" class="row">
		<div class="page col-lg-6">
		<div class="panel panel-default">
			<div class="panel-heading"><h4><?php echo $article->vcTitle;?></h4></div>
			<div class="panel-body">
				<span class="contents"><?php echo $article->tContents; ?></span>

				<p class="command tr">
				<a href="/board/<?php echo $board;?>" class="btn btn-default">목록</a> 
				<?php if($article->nUserNo == $memberNo) { ?><a class="edit btn btn-default">수정</a> <a class="del btn btn-default">삭제</a><?php } ?>
				</p>

				<hr />

				<ul class="comment media-list">
				<?php for($a=0,$loopa=sizeof($comments); $a<$loopa; $a++){ ?>
				  <li class="media">
					<div class="media-body">
					  <h4 class="media-heading"><?php echo $comments[$a]->vcNickname;?>
					<?php if($comments[$a]->nUserNo == $memberNo) { ?><a class="btn btn-default edicomment<?php echo $comments[$a]->nSeqNo;?>">수정</a> <a class="btn btn-default delcomment<?php echo $comments[$a]->nSeqNo;?>">삭제</a><?php } ?></h4>
					  <p class="comment<?php echo $comments[$a]->nSeqNo;?>"><?php echo $comments[$a]->vcContents;?></p>
					</div>
				  </li>
				<?php } ?>
				</ul>

				<form action="/board/writecomment/<?php echo $board;?>" class="panel panel-default">
					<input type="hidden" name="articleNo" value="<?php echo $article->nSeqNo;?>" />
					<input type="hidden" name="commentNo" value="" />
					<div class="panel-heading">코멘트 작성</div>
					<div class="panel-body">
						<textarea name="contents" class="form-control"></textarea>
						<hr />
						<input type="submit" value="작성" class="btn btn-default" /> <button type="button" class="btn btn-default">취소</button>
					</div>
				</form>
			</div>
		</div>
		</div>
	</div>
	</div>
	<?php $load->view("board/write",array("board"=>$board)); ?>
</div>
<?php $this->load->view('common/footer'); ?>