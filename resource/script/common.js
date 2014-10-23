$(document).ready(function(){
	loginLoad();
	$("#Join").submit(checkJoin).find("input").eq(0).focusout(checkDupId).end();
});

function loginLoad(){
	$.ajax({url:"/main/loginLoad"})
	.done(function(data){
		$("#Login").html(data);
		if($("#Login form").size() > 0){
			$("#Login .join").click(function(e){
				e.preventDefault();
				$("#Join").modal('show').each(function(){this.reset();});
				return false;
			});
			$("#Login form").submit(checkLogin);
		} else {
			$("#Login .logout").click(doLogout);
		}
	});
}

function checkLogin(){
	var data = $("#Login form").serializeArray();
	var dataList = {};
	for(var a=0,loopa=data.length; a<loopa; a++){
		dataList[data[a].name] = data[a].value;
	}
	if(!dataList.userid) {
		$("#Login form input:eq(0)").focus();
		alert("아이디를 입력해 주세요.");
		return false;
	}
	if(!dataList.password) {
		$("#Login form input:eq(1)").focus();
		alert("비밀번호를 입력해 주세요.");
		return false;
	}
	$.ajax({url:"/main/login",data:data,type:"post"})
	.done(function(data){
		if(data == "ok") loginLoad();
		else {
			$("#Login form input:eq(0)").focus();
			alert("아이디 또는 비밀번호가 틀렸습니다.");
		}
	});
	return false;
}

function checkJoin(){
	var data = $("#Join").serializeArray();
	var dataList = {};
	if($("#Join").find("span.red").size()>0){
		$("#Join input:eq(0)").focus();
		alert("이미 있는 ID입니다. 다른 ID를 입력해 보세요.");
		return false;
	}
	for(var a=0,loopa=data.length; a<loopa; a++){
		dataList[data[a].name] = data[a].value;
	}
	if(!dataList.userid) {
		$("#Join input:eq(0)").focus();
		alert("아이디를 입력해 주세요.");
		return false;
	}
	if(!dataList.password) {
		$("#Join input:eq(1)").focus();
		alert("비밀번호를 입력해 주세요.");
		return false;
	}
	if(dataList.password != dataList.password2) {
		$("#Join input:eq(1)").focus();
		alert("비밀번호가 다릅니다. 다시입력해 주세요.");
		return false;
	}
	$.ajax({url:"/main/join",data:data,type:"post"})
	.done(function(data){
		if(data == "ok") {
			$("#Join").modal('hide');
			alert("회원가입이 완료되었습니다. 로그인해 주세요");
		} else {
			$("#Login form input:eq(0)").focus();
			alert("가입에 문제가 생겼습니다. 다시 시도해 보시거나 다시 입력해보세요.");
		}
	});
	return false;
}

function checkDupId(){
	var obj = $(this);
	if(!obj.val()){
		obj.parent().find("span").html("ID를 입력해주세요.").addClass("red").removeClass("blue");
		return;
	}
	$.ajax({url:"/main/isExists",type:"get",data:{id:obj.val()}})
	.done(function(data){
		if(data == "ok") obj.parent().find("span").html("사용하셔도 좋은 ID입니다.").addClass("blue").removeClass("red");
		else obj.parent().find("span").html("이미 있는 ID입니다.").addClass("red").removeClass("blue");
	});
}

function doLogout(){
	$.ajax({url:"/main/logout"})
	.done(function(){
		loginLoad();
	});
	return false;
}