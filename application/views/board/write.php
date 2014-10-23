<form id="Write" class="modal fade" action="/board/write/<?php echo $board;?>">
<input type="hidden" name="articleNo" value="" />
  <div class="modal-dialog">
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title">글쓰기</h4>
	  </div>
	  <div class="modal-body">
		<table class="table table-striped">
		<tr><th>제목</th><td><input type="text" name="title" class="form-control" /></td></tr>
		<tr><th>내용</th><td><textarea name="contents" class="form-control"></textarea></td></tr>
		</table>
	  </div>
	  <div class="modal-footer">
		<input type="submit" value="저장" class="btn btn-primary" />
		<button type="button" class="btn btn-default" data-dismiss="modal">취소</button>
	  </div>
	</div>
  </div>
</form>