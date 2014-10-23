$(document).ready(function(){
});

function showGamePage(){
	showPage("/game/bms/");
}

function showPage(url){
	var obj = $("#Play");
	if(obj) obj.remove();
	$("body").append('<iframe id="Play" src="'+url+'"></iframe>');
	var height = $(document).innerHeight();
	var width = $(document).innerWidth();
	if(width>1200) width = 1200;
	if(height>800) height = 800;
	$("#Play").css({width:width,height:height,left:parseInt($("#Wrap").css("margin-left"))+40});
}