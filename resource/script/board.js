$(document).ready(function(){
	$(".listButton").click(function(){
		if($("#Login form").size()!=0){
			alert("로그인을 해야지만 글을 작성 할 수 있습니다.");
			return;
		}
		$("#Write").each(function(){this.reset();}).modal('show');
	});

	$("#Write").submit(function(){
		var data = $("#Write").serializeArray();
		var dataList = {};
		for(var a=0,loopa=data.length; a<loopa; a++){
			dataList[data[a].name] = data[a].value;
		}
		if(!dataList.title) {
			$("#Write input:eq(0)").focus();
			alert("제목을 입력해 주세요.");
			return false;
		}
		if(!dataList.contents) {
			$("#Write textarea").focus();
			alert("내용을 입력해 주세요.");
			return false;
		}
		$.ajax({url:$(this).attr("action"),data:data,type:"post"})
		.done(function(data){
			if(data == "false") {
				$("#Write input:eq(0)").focus();
				alert("글을 작성하는데 실패 했습니다. 다시 한번 시도해 보세요.");
			} else 
				location.href = data;
		});
		return false;
	});

	$("#View div form").submit(function(){
		var data = $(this).serializeArray();
		var dataList = {};
		for(var a=0,loopa=data.length; a<loopa; a++){
			dataList[data[a].name] = data[a].value;
		}
		if(!dataList.contents) {
			$("#View textarea").focus();
			alert("내용을 입력해 주세요.");
			return false;
		}
		$.ajax({url:$(this).attr("action"),data:data,type:"post"})
		.done(function(data){
			if(data == "false") {
				$("#View textarea").focus();
				alert("코멘트를 작성하는데 실패 했습니다. 다시 한번 시도해 보세요.");
			} else 
				location.href = location.href;
		});
		return false;
	}).find("button").click(function(){
		$("#View form input[name=commentNo]").val("");
		$("#View form textarea").val("");
		$("#View form input[type=submit]").val("작성");
	});

	$("#View .comment a").click(function(){
		var classList = $(this).attr("class").split(' ');
		var classInfo = null;
		for(var a=0,loopa=classList.length; a<loopa; a++){
			var tmp = classList[a].substr(0,3);
			if(tmp == 'edi' || tmp == 'del')
				classInfo = classList[a];
		}
		if(!classInfo) return false;
		var action = classInfo.substr(0,3);
		var url = $("#View form").attr("action");
		var articleNo = $("#View form").find("input:eq(0)").val();
		var obj = $(this);
		if(action=="edi"){
			var seq = classInfo.replace("edicomment","");
			$("#View form input[name=commentNo]").val(seq);
			$("#View form textarea").val($(".comment"+seq).text());
			$("#View form input[type=submit]").val("수정");
		} else if(action=="del"){
			if(confirm("정말로 삭제 하시겠습니까?")){
				$.ajax({url:url.replace("write","del"),data:{seq:classInfo.replace("delcomment",""),articleNo:articleNo},type:"post"})
				.done(function(data){
					if(data == "false") {
						$("#View textarea").focus();
						alert("코멘트를 삭제하는데 실패 했습니다. 다시 한번 시도해 보세요.");
					} else {
						$(obj).parent().parent().parent().remove();
						alert("코멘트를 삭제하였습니다.");
					}
				});
			}
		}
		return false;
	});

	$("#View .command a").click(function(){
		var articleNo = $("#View form").find("input:eq(0)").val();
		if($(this).hasClass("edit")){
			$("#Write input[name=articleNo]").val(articleNo);
			$("#Write input[name=title]").val($("#View .panel-heading h4").text());
			$("#Write textarea").val($(".contents").text());
			$("#Write input[type=submit]").val("수정");
			$('#Write .modal-title').val('글 수정');
			$("#Write").modal('show');
		} else if($(this).hasClass("del")){
			if(confirm("정말로 삭제 하시겠습니까?")){
				$.ajax({url:$("#Write").attr("action").replace("write","delete"),data:{articleNo:articleNo},type:"post"})
				.done(function(data){
					if(data == "false") {
						alert("글을 삭제하는데 실패 했습니다. 다시 한번 시도해 보세요.");
					} else {
						location.href = $("#Write").attr("action").replace("/write","");
					}
				});
			}
		}
	});
});