$(document).ready(function(){
	$("#Member .borderAll td span").click(function(e){
		var op = $(this).attr("class");
		var target = $(this).parent().attr("class");
		switch(op){
			case "passwd":
				$("#Password").find("span[class!=close]").attr("class",target).end()
				.css({display:"block",left:e.pageX+10,top:e.pageY+10}); break;
			case "admin":
				$("#Permission").find("select").val($(".Admin_"+target).text()).end()
				.find("span[class!=close]").attr("class",target).end()
				.css({display:"block",left:e.pageX+10,top:e.pageY+10}); break;
			case "del":
				if(confirm("정말로 삭제 시키시겠습니까?.")){
					$.ajax({url:"/admin/memberDel/",type:"post",data:{nSeqNo:target}})
					.done(function(){
						location.href = location.href;
					});
				}
				break;
			case "restore":
				if(confirm("정말로 복구 시키시겠습니까?.")){
					$.ajax({url:"/admin/memberRestore/",type:"post",data:{nSeqNo:target}})
					.done(function(){
						location.href = location.href;
					});
				}
				break;
		}
	});

	$("#Permission span").click(function(){
		var target = $(this).attr("class");
		if(target == "close"){
			$(this).parent().css({display:"none"});
		} else {
			$.ajax({url:"/admin/memberAdmin/",type:"post",data:{nSeqNo:target,admin:$("#Permission select").val()}})
			.done(function(){
				location.href = location.href;
			});
		}
	});

	$("#Password span").click(function(){
		var target = $(this).attr("class");
		if(target != "close"){
			$.ajax({url:"/admin/memberPassword/",type:"post",data:{nSeqNo:target,password:$("#Password input").val()}})
			.done(function(){
				alert("완료되었습니다.");
			});
		}
		$(this).parent().css({display:"none"});
	});
});