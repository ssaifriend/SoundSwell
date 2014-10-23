var insertKeyUserInput = false;
var orgKeyUserInput = {};
var replacerInput = [6,1,2,3,4,5,8,9];
var currentSelectInput = 0;
var currentMouseXY = {x:0,y:0};
var currentDivXY = {x:0,y:0};
var currentMouseClick = false;
var currentMouseResizeClick = false;
$(document).ready(function(){
	if($("#gameOption").size()>0){
		function buttonActivate(data){
			$("."+data).addClass("active");
		}
		function getType(obj){
			return $.trim(obj.attr("class").replace("active","").replace("btn","").replace("btn-default",""));
		}
		$.ajax({url:"/game/getOption/key"}).done(buttonActivate);
		$.ajax({url:"/game/getOption/bga"}).done(buttonActivate);
		$.ajax({url:"/game/getOption/note"}).done(buttonActivate);
		$.ajax({url:"/game/getOption/autoplay"}).done(buttonActivate);
		$.ajax({url:"/game/getOption/volume"}).done(function(data){ $("input[name=volume]").val(data).change(); });
		$("#gameOption").find(".keySelect").children().click(function(){
			var obj = $(this);
			var type = getType($(this));
			$.ajax({url:"/game/updateOption/key",data:{type:type}})
			.done(function(){
				if(type=="key3"){
					$.getJSON("/game/getOption/keydetail",function(data){ 
						orgKeyUserInput = data;
						insertKeyUserInput = true;
						$("#keyUserInput td").each(function(idx){
							$(this).html(String.fromCharCode(orgKeyUserInput[replacerInput[idx]]));
						});
						$("#keyUserInput").css({display:"block"});
					});
				}
			});
		}).end().end().find(".bga").children().click(function(){
			var obj = $(this);
			var type = getType($(this));
			$.ajax({url:"/game/updateOption/bga",data:{type:type}});
		}).end().end().find(".noteSelect").children().click(function(){
			var obj = $(this);
			var type = getType($(this));
			$.ajax({url:"/game/updateOption/note",data:{type:type}});
		}).end().end().find(".autoplay").children().click(function(){
			var obj = $(this);
			var type = getType($(this));
			$.ajax({url:"/game/updateOption/autoplay",data:{data:type}});
		}).end().end().find(".bgaPosition").children().eq(0).click(function(){
			$.ajax({url:"/game/updateOption/bgaPosition",type:"post",data:{data:"init"}});
			$.ajax({url:"/game/updateOption/bgaSize",type:"post",data:{data:"init"}});
		}).end().eq(1).click(function(){
			if($('#bgaPosition > img').size()>0) $('#bgaPosition > img').remove();
			$('#bgaPosition').prepend('<img src="/skin/old/play.png" />')
				.find('.bgaPattern').css({'background':'url("/skin/old/background.png") repeat-y left top',width:$('#page-content-wrapper').innerWidth()+'px',height:$(document).innerHeight()+'px'});
			$.getJSON("/game/getOption/bgaPosition",function(data1){
				$("#bgaPosition .bgaSelect").css("left",data1.l+"px").css("top",data1.t+"px");
				$.getJSON("/game/getOption/bgaSize",function(data2){
					$("#bgaPosition").children(".bgaSelect").css({width:data2.w,height:data2.h}).end().css({display:"block",width:$('#page-content-wrapper').innerWidth()+'px',height:$(document).innerHeight()+'px'});
				});
			});
		}).end().end().end().find("input[name=volume]").change(function(){
			$.ajax({url:"/game/updateOption/volume",data:{data:$(this).val()}});
			$("input[name=displayvolume]").val($(this).val());
		}).end().find("input[name=bpm]").change(function(){
			$.ajax({url:"/game/updateOption/bpm",data:{data:$(this).val()}});
			$("input[name=displaybpm]").val($(this).val()+"%");
		});

		$("#keyUserInput").find("p span").eq(0).click(function(){
			$("#keyUserInput").find("td").html("");
			currentSelectInput = 0;
		}).end().eq(1).click(function(){
			$.ajax({url:"/game/updateOption/keydetail",type:"post",data:{data:orgKeyUserInput}})
			.done(function(){
				$("#keyUserInput").css({display:"none"});
			});
		}).end().eq(2).click(function(){
			$.ajax({url:"/game/updateOption/key",data:{type:"key2"}})
			.done(function(){
				$(".key3").removeClass("select");
				$(".key2").addClass("select");
			});
			$("#keyUserInput").css({display:"none"});
		}).end().end().find("td").each(function(idx){
			$(this).click(function(){ currentSelectInput = idx; });
		});

		$("#bgaPosition").mousemove(function(e){
			if(currentMouseResizeClick) {
				mouseMoveResize(e);
			}
		}).mouseup(function(){
			currentMouseClick = false;
			currentMouseResizeClick = false;
		}).find("span > span").click(function(){
			var obj = $("#bgaPosition .bgaSelect");
			$.ajax({url:"/game/updateOption/bgaPosition",type:"post",data:{data:{l:parseInt(obj.css("left")),t:parseInt(obj.css("top"))}}})
			.done(function(){
				$.ajax({url:"/game/updateOption/bgaSize",type:"post",data:{data:{w:parseInt(obj.innerWidth()),h:parseInt(obj.innerHeight())}}})
				.done(function(){
					$("#bgaPosition").css({display:"none"});
				});
			});
		}).end().children("div.bgaSelect").mousedown(function(e){
			currentMouseXY.x = e.pageX; currentMouseXY.y = e.pageY;
			currentDivXY.x = parseInt($(this).css("left")); currentDivXY.y = parseInt($(this).css("top"));
			currentMouseClick = true;
			return false;
		}).mousemove(function(e){
			if(currentMouseResizeClick) {
				mouseMoveResize(e);
			}
			if(currentMouseClick) {
				var left = e.pageX-currentMouseXY.x+currentDivXY.x;
				var top = e.pageY-currentMouseXY.y+currentDivXY.y;
				if(left<0) left = 0;
				else if(left>$("#bgaPosition").innerWidth() - $(this).innerWidth()) left = $("#bgaPosition").innerWidth() - $(this).innerWidth();
				if(top<0) top = 0;
				else if(top>$("#bgaPosition").innerHeight() - $(this).innerHeight()) top = $("#bgaPosition").innerHeight() - $(this).innerHeight();
				$(this).css({left:left,top:top});
			}
		}).mouseup(function(){
			currentMouseClick = false;
		}).children("div").mousedown(function(e){
			currentMouseXY.x = e.pageX; currentMouseXY.y = e.pageY;
			currentDivXY.x = parseInt($("#bgaPosition .bgaSelect").innerWidth()); currentDivXY.y = parseInt($("#bgaPosition .bgaSelect").innerHeight());
			currentMouseResizeClick = true;
			return false;
		}).mousemove(mouseMoveResize)
		.mouseup(function(){
			currentMouseResizeClick = false;
		});

		$(document).keydown(checkKeyinput);
	}
	$('.col-lg-2 a').click(function(){
		var width = $('#page-content-wrapper').innerWidth();
		var height = $(document).innerHeight();
		var title = $.trim($(this).find('.caption').text());
		$('#selectBMS .panel-body .image').html('<img src="'+$(this).find('img').attr('src')+'" alt="" />');
		$.getJSON($(this).attr('href'), function(data){
			var str = '';
			for(var a=0; a<=1; a++){
				str += '<li class="nav-header">'+(!a?'5Key':'7Key')+'</li>';
				for(var b=0, loopb=data.length; b<loopb; b++){
					if(data[b]['7Key'] != a) continue;
					var newTitle = $.trim(data[b].title.replace(title,''));
					var label = null;
					if(data[b].level < 4) label = 'primary';
					else if(data[b].level < 10) label = 'warning';
					else label = 'danger';
					str += '<li><a href="/game/play/'+data[b].hash+'"><span class="label label-'+label+' pull-right">'+data[b].level+'</span>'+(newTitle.length>0?newTitle:data[b].title)+'</a></li>';
				}
			}
			$('#selectBMS .panel-body ul.nav-stacked').html(str);
			$('#selectBMS').css({display:'block', left:(width/2-560)+'px', top: (height/2-350)+'px'})
				.find('.panel-title').text(title);
		});
		return false;
	});
	$('#selectBMS .panel-heading').mousedown(function(e){
		currentMouseXY.x = e.pageX; currentMouseXY.y = e.pageY;
		currentDivXY.x = parseInt($("#selectBMS").css('left')); currentDivXY.y = parseInt($("#selectBMS").css('top'));
		currentMouseResizeClick = true;
	}).mousemove(function(e){
		if(currentMouseResizeClick) {
			var left= e.pageX-currentMouseXY.x+currentDivXY.x;
			var top = e.pageY-currentMouseXY.y+currentDivXY.y;
			if(left<0) left = 0;
			if(top<0) top = 0;
			$("#selectBMS").css({left:left+'px',top:top+'px'});
		}
		return false;
	}).mouseup(function(){
		currentMouseResizeClick = false;
	}).mouseout(function(){
		currentMouseResizeClick = false;
		return false;
	}).find('button').click(function(){
		$('#selectBMS').css('display','none');
		return false;
	});
	$('#selectBMS .tab li a').click(function(){
		if($(this).hasClass('active')) return false;
		$(this).parent().siblings().removeClass('active').end().addClass('active');
		if($(this).parent().hasClass('bmslist')){
			$('.nav-stacked').css('display','block');
			$('#gameOption').css('display','none');
		} else if($(this).parent().hasClass('option')){
			$('.nav-stacked').css('display','none');
			$('#gameOption').css('display','block');
		}
		return false;
	});
});

function checkKeyinput(e){
	if(currentSelectInput>7) return;
	orgKeyUserInput[replacerInput[currentSelectInput]] = e.which;
	$("#keyUserInput td").eq(currentSelectInput).html(String.fromCharCode(e.which));
	currentSelectInput++;
}

function mouseMoveResize(e){
	if(currentMouseResizeClick) {
		var width = e.pageX-currentMouseXY.x+currentDivXY.x;
		var height = e.pageY-currentMouseXY.y+currentDivXY.y;
		if(width<10) width = 10;
		if(height<10) height = 10;
		$("#bgaPosition > div").css({width:width,height:height});
	}
}