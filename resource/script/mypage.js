
$(document).ready(function(){
	$("body > span").click(function(){
		parent.jQuery("#Play").remove();
	});

	$("div > form").submit(function(){
		var data = $(this).serializeArray();
		var dataList = {};
		for(var a=0,loopa=data.length; a<loopa; a++){
			dataList[data[a].name] = data[a].value;
		}
		if(!dataList.password) {
			$("div > form input:eq(0)").focus();
			alert("비밀번호를 입력해 주세요.");
			return;
		}
		$.ajax({url:"/mypage/updateInfo",data:data,type:"post"})
		.done(function(data){
			if(data == "ok") {
				$("div > form").each(function(){this.reset();});
				alert("수정이 완료되었습니다.");
			} else {
				$("div > form input:eq(0)").focus();
				alert("수정에 문제가 생겼습니다. 다시 시도해 보세요.");
			}
		});
		return false;
	});
});