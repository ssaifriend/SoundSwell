var context; //web audio context 변수
var gainNode; //볼륨 변경을 위한 gain node
//각 key 값 저장할 변수
var audioList = []; 
var bgaList = [];
var mpgList = [];
var resourceList = [];
var currentBGA = null; //지금 출력하고 있는 BGA 배경 키 값
var badObj = '00'; //miss시 출력 할 BGA 배경 키 값

var canvasBuffer = document.createElement("canvas"); //더블 버퍼링을 위한 canvas
var bgaCtx = null; //bga, bgalayer의 canvas context를 저장할 변수
var bgaLayerCtx = null;

//web worker instance를 저장할 변수
var worker = null;
var longNoteTick = Math.floor(firstTime/32); //롱노트를 몇 ms당 1콤보를 줄 것인지 설정
var startTimeStamp = null; //게임 시작 시각 (ms)
var intervalTime = 16; //play canvas에 그릴 interval
var currentTime = 0; //지난 시각
var currentIdx = 0; //timeList의 몇번째 index까지 지났는가

//음원 불러오고 저장할 변수들
var audioSource = {};
var audioTotal = 0;
var audioCurrent = 0;
var playbackRate = 1.0; //재생 속도 관련 변수

//BGA 관련 저장 변수
var bgaDisplay = true; //사용자 설정에 의해 끌 수 있음
var bgaSource = {};
var bgaTotal = 0;
var bgaCurrent = 0;
var bgaReplaceList = {}; //BGA 헤더 참조

//동영상 배경일때 쓸 변수
var mpgSource = {};
var mpgPlay = {};
var mpgTotal = 0;
var mpgCurrent = 0;

//스킨 관련 변수
var resourceSource = {};
var resourceTotal = 0;
var resourceCurrent = 0;
var resource = {play:"/skin/old/play.png",bg:'/skin/old/background.png',n01:"/skin/old/note_01.png",n02:"/skin/old/note_02.png",n03:"/skin/old/note_03.png",n04:"/skin/old/note_04.png",
n11:"/skin/old/note_11.png",n12:"/skin/old/note_12.png",n13:"/skin/old/note_13.png",n14:"/skin/old/note_14.png",
n21:"/skin/old/note_21.png",n22:"/skin/old/note_22.png",n23:"/skin/old/note_23.png",n24:"/skin/old/note_24.png",
e1:"/skin/old/Comp1_00000.png",e2:"/skin/old/Comp1_00001.png",e3:"/skin/old/Comp1_00002.png",e4:"/skin/old/Comp1_00003.png",e5:"/skin/old/Comp1_00004.png",
lb:"/skin/old/highlight_blue.png",lw:"/skin/old/highlight_white.png",lp:"/skin/old/highlight_pink.png",
judswell:"/skin/old/jud_swell.png",judwell:"/skin/old/jud_well.png",judgood:"/skin/old/jud_good.png",judbad:"/skin/old/jud_bad.png",judmiss:"/skin/old/jud_miss.png",
combo:"/skin/old/combo.png",num1:"/skin/old/num1.png",num2:"/skin/old/num2.png",num3:"/skin/old/num3.png",num4:"/skin/old/num4.png",num5:"/skin/old/num5.png",
num6:"/skin/old/num6.png",num7:"/skin/old/num7.png",num8:"/skin/old/num8.png",num9:"/skin/old/num9.png",num0:"/skin/old/num0.png"};
var skinInfo = {width:578,height:768,judge:190,extra:100,noteStart:50,noteEnd:362,comboImageLeft:100,comboImageTop:200,comboCount:300,judgeLeft:40, judgeBottom:300,effectBottom:100,effectbg:570};
var playHeight = 0;
var displayInfo = {width:0,height:0};

var perSize = 450; //1초의 길이는 150px이고, 점점 길이가 늘어나는 것을 애니메이션 화 하기 위해
var targetSize = 450; //targetSize변수가 있음. perSize에서 일정 값을 더해서 targetSize까지 증가/감소함
var currentSpeed = 2.0; //지금 속도
var display = 4; //몇 마디까지 그릴 것인가.
var combo = 0;
var maxCombo = 0;
var currentScore = 0; //perSize 처럼 애니메이션 효과를 위해..
var targetScore = 0;
var curNote = 1; //노트 번쩍이는 효과 때문에 사용
var showNote = 0;
var showJud = -1;
var showJudTime = 0;

var totalNotes = 0; //정확도를 위해 쓰임
var totalGrades = 0;
var keyObject = {}; //그 키를 누르면 출력할 키 음 값
var pushKey = {'6':false,'1':false,'2':false,'3':false,'4':false,'5':false,'8':false,'9':false}; //누른 키 저장
var acceptKey = {}; //키 누른거를 인식 할 배열 아래 예제가 있음
//var acceptKey = {'6':65,'1':83,'2':68,'3':70,'4':74,'5':75,'8':76,'9':59};
var gradeList = [50,100,200,350]; //판정 범위(ms) swell,well,good,bad 범위
var scoreList = [100,80,50,0]; //판정에 따른 score
var gageList = [0.5,0.3,0.1,-0.5]; //게이지 증가치
var longGageList = [0.3,0,2,0,0]; //롱노트 게이지 증가치
var longScoreList = [35,20,7,0]; //롱노트 score
var resultList = {"swell":0,"well":0,"good":0,"bad":0,"miss":0,"bonus":0,"score":0}; //각 판정별 노트 수
var resultBind = ["swell","well","good","bad","miss"];
//일반노트/롱노트를 별도 저장
var normalResultList = {"swell":0,"well":0,"good":0,"bad":0,"miss":0,"bonus":0,"score":0};
var longResultList = {"swell":0,"well":0,"good":0,"bad":0,"miss":0,"bonus":0,"score":0};
//각 노트별 입력 됐을 시 effect 출력
var effectList = {"6":0,"1":0,"2":0,"3":0,"4":0,"5":0,"8":0,"9":0};
var autoplay = "none"; //auto play
var currentGage = 50; //지금 게이지
var longMaxIdx = null; //longNoteTimeList 크기
var ignoreRank = false; //폭사 여부
var timeBarWidth = 0; //플레이 아래에 출력되는 시간
var bgaSize = {w:256,h:256}; //BGA 크기
var bgaRatio = {w:1,h:1}; //비율
var frameCounter = new FrameCounter(); //fps

//각종 Context
var scoreCtx = null;
var speedCtx = null;
var gradeCtx = null;

var statusBar = null;
var secBox = null;

var gradedList = {};
var gradedLongList = {};
var longnoteCurrent = {};
var autoplayCheckList = {};
var autoplayLongnoteList = {};
var longNoteCheckList = {'6':false,'1':false,'2':false,'3':false,'4':false,'5':false,'8':false,'9':false};


$(document).ready(function(){
	try {
		context = new webkitAudioContext();
		gainNode = context.createGainNode();
		gainNode.connect(context.destination);
	}
	catch(e) {
		alert('Web Audio API가 지원되지 않는 브라우저입니다. 크롬을 이용하세요!');
		return;
	}

	playHeight = $(document).innerHeight() - 35;
	$('#Gear').css('bottom','35px');
	$('#Speed').css('bottom',(parseInt($('#Speed').css('bottom'))+35)+'px');
	$('#Score').css('bottom',(parseInt($('#Score').css('bottom'))+35)+'px');
	$('#Grade').css('bottom',(parseInt($('#Grade').css('bottom'))+35)+'px');

	var org = document.getElementById("Play");
	displayInfo.height = playHeight - skinInfo.judge;
	org.height = displayInfo.height + skinInfo.extra;
	displayInfo.width = $(document).innerWidth();
	canvasBuffer.width = org.width;
	canvasBuffer.height = org.height;
	bgaCtx = document.getElementById("BGA").getContext("2d");
	bgaLayerCtx = document.getElementById("BGALayer").getContext("2d");
	gradeCtx = document.getElementById("Grade").getContext("2d");
	gradeCtx.font = 'bold 22px sans-serif';
	gradeCtx.fillStyle = '#6600ff';
	scoreCtx = document.getElementById("Score").getContext("2d");
	scoreCtx.font = 'bold 30px sans-serif';
	scoreCtx.fillStyle = '#6600ff';
	speedCtx = document.getElementById("Speed").getContext("2d");
	speedCtx.font = 'bold 30px sans-serif';
	speedCtx.fillStyle = '#6600ff';
	org = null;

	//사용자 설정 값 불러옴
	$.getJSON("/game/getData/"+hash+"/bmsInfo",function(data){ 
		bmsInfo = data;
		var width = $(document).innerWidth()-2;
		var height = $(document).innerHeight()-2;
		$("#loadingImage").css({width:width,height:height});
		if(data.STAGEFILE)
			$("#loadingImage").append('<img src="'+url+data.STAGEFILE+'" width="'+width+'" height="'+height+'" />');
		else
			$(".load").css({color:"black"});
		width = null;
		height = null;
	});
	$.ajax({url:"/game/getOption/key"}).done(function(data){
		if(data == "key1") acceptKey = {'6':16,'1':90,'2':83,'3':88,'4':68,'5':67,'8':70,'9':86};
		else if(data == "key2") acceptKey = {'6':65,'1':83,'2':68,'3':70,'4':32,'5':74,'8':75,'9':76};
		else if(data == "key3"){
			$.getJSON("/game/getOption/keydetail",function(data){ 
				acceptKey = data;
			});
		}
	});
	$.ajax({url:"/game/getOption/volume"}).done(function(data){
		gainNode.gain.value = data/100;
	});
	$.ajax({url:"/game/getOption/bpm"}).done(function(data){
		playbackRate = data/100;
	});
	$.ajax({url:"/game/getOption/autoplay"}).done(function(data){
		autoplay = data;
	});
	$.getJSON("/game/getData/"+hash+"/longNoteList",function(data){
		longNoteList = data;
		$.getJSON("/game/getData/"+hash+"/playData",function(data){ playData = data; });
	});
	$.getJSON("/game/getData/"+hash+"/longNoteTimeList",function(data){ longNoteTimeList = data; longMaxIdx = longNoteTimeList.length; });
	$.getJSON("/game/getData/"+hash+"/soundPlayList",function(data){ soundPlayList = data; });
	$.getJSON("/game/getData/"+hash+"/bgaPlayList",function(data){ bgaPlayList = data; });
	$.getJSON("/game/getData/"+hash+"/bgaLayerList",function(data){ bgaLayerList = data; });
	$.getJSON("/game/getData/"+hash+"/timeList",function(data){ timeList = data; maxIdx = timeList.length; });
	$.getJSON("/game/getData/"+hash+"/currentTimeList",function(data){ 
		currentTimeList = data;
		endTime = currentTimeList[currentTimeList.length-1]+2000;
		$("#playStatus span").eq(0).html("-0:"+Math.floor(firstTime*3/1000)).end()
		.eq(1).html(Math.floor((endTime-firstTime*3)/60000)+":"+Math.floor(((endTime-firstTime*3)%60000)/1000));
	});
	$.getJSON("/game/getData/"+hash+"/poorBGAList",function(data){ poorBGAList = data; });
	$.getJSON("/game/getData/"+hash+"/stopList",function(data){ stopList = data; });
	$.getJSON("/game/getData/"+hash+"/stopTimeList",function(data){ stopTimeList = data; });
	$.ajax({url:"/game/getOption/bga"}).done(function(data){
		bgaDisplay = (data=="bgaon");
		if(!bgaDisplay){
			document.getElementById("BGA").style.display = "none";
			document.getElementById("BGALayer").style.display = "none";
		}
		$.getJSON("/game/getOption/bgaPosition",function(data){
			document.getElementById("BGA").style.left = data.l+"px";
			document.getElementById("BGA").style.top = data.t+"px";
			document.getElementById("BGALayer").style.left = data.l+"px";
			document.getElementById("BGALayer").style.top = data.t+"px";
		});
		$.getJSON("/game/getOption/bgaSize",function(data){
			document.getElementById("BGA").width= data.w;
			document.getElementById("BGA").height = data.h;
			document.getElementById("BGALayer").width = data.w;
			document.getElementById("BGALayer").height = data.h;
			bgaSize = data;
			bgaRatio.w = data.w/256;
			bgaRatio.h = data.h/256;

			$.getJSON("/game/getData/"+hash+"/loadList",function(data){ 
				loadList = data;
				var a = null;

				for(a in loadList.WAV) {
					audioList.push(a);
					audioTotal++;
				}
				if(bgaDisplay){
					for(a in loadList.BMP) {
						bgaList.push(a);
						bgaTotal++;
					}
					for(a in loadList.MPG){
						mpgList.push(a);
						mpgTotal++;
					}
					for(a in loadList.BGA){
						var obj = loadList.BGA[a];
						bgaReplaceList[a] = {no:obj[0], x:obj[1]*bgaRatio.w, y:obj[2]*bgaRatio.h, width:(obj[3]-obj[1])*bgaRatio.w,
											 height:(obj[4]-obj[2])*bgaRatio.h, targetx:obj[5]*bgaRatio.w, targety:obj[6]*bgaRatio.h};
						obj = null;
					}
					bgaLoad(0);
					mpgLoad(0);
				}
				audioLoad(0);
				for(a in resource){
					resourceList.push(a);
					resourceTotal++;
				}
				resourceLoad(0);
				a = null;
			});
		});
	});
	//시간 바 크기 설정
	timeBarWidth = parseInt($(document).innerWidth())-200;
	$("#playStatus").css({width:timeBarWidth+100})
	.children("div").css({width:timeBarWidth});
	statusBar = $("#playStatus div div:eq(0)")[0];
	secBox = $("#playStatus span:eq(0)")[0];
});

//아래 4개함수는 데이터 불러 오는 함수
function audioLoad(idx){
	if(!audioList[idx]) return;

	//ajax를 이용해서 데이터 불러 옴
	var request = new XMLHttpRequest();

	request.onload = function() {
		if(!this.response){
			audioCurrent++;
			$("#loadStatus").html("Audio: "+audioCurrent+"/"+audioTotal+"<br />BGA: "+bgaCurrent+"/"+bgaTotal);
			if(audioCurrent==audioTotal && bgaCurrent == bgaTotal && mpgCurrent == mpgTotal )
				start();
			else
				audioLoad(++idx);
			return;
		}
		try{
			context.decodeAudioData(request.response, function(buffer){
				audioSource[audioList[idx]] = buffer;
				audioCurrent++;
				$("#loadStatus").html("Audio: "+audioCurrent+"/"+audioTotal+"<br />BGA: "+bgaCurrent+"/"+bgaTotal);
				if(audioCurrent==audioTotal && bgaCurrent == bgaTotal && mpgCurrent == mpgTotal )
					start();
				else
					audioLoad(++idx);
			}, onError);
		} catch(e){
			console.log(e);
		}
	};
	request.open('GET', url+loadList.WAV[audioList[idx]], true);
	request.responseType = 'arraybuffer';
	request.send();
}

//bga/스킨 데이터는 img 태그를 이용해 불러옴
function bgaLoad(idx){
	if(!bgaList[idx]) return;

	var img = document.createElement("img");
	img.onload = function(){
		bgaLayerCtx.drawImage(img,0,0,bgaSize.w,bgaSize.h);
		var imageData = bgaLayerCtx.getImageData(0,0,bgaSize.w,bgaSize.h);
		for(var b=0,loopb=imageData.data.length; b<loopb; b+=4){
			if(imageData.data[b]==0 && imageData.data[b+1]==0 &&
			   imageData.data[b+2]==0)
				imageData.data[b+3] = 0;
		}
		bgaSource[bgaList[idx]] = imageData;
		bgaLayerCtx.clearRect(0,0,bgaSize.w,bgaSize.h);
		imageData = null;
		if(bgaCurrent+1 == bgaTotal){
			for(var a in bgaReplaceList){
				checkBGAReplace(a);
			}
		}
		bgaCurrent++;
		$("#loadStatus").html("Audio: "+audioCurrent+"/"+audioTotal+"<br />BGA: "+bgaCurrent+"/"+bgaTotal);
		if(audioCurrent==audioTotal && bgaCurrent == bgaTotal && mpgCurrent == mpgTotal )
			start();
		else
			bgaLoad(++idx);
	};
	img.src = url+loadList.BMP[bgaList[idx]];
}

function resourceLoad(idx){
	if(!resourceList[idx]) return;

	var img = document.createElement("img");
	img.onload = function(){
		resourceCurrent++;
		if(resourceCurrent!=resourceTotal)
			resourceLoad(++idx);
	};
	img.src = resource[resourceList[idx]];
	resourceSource[resourceList[idx]] = img;
}

//동영상은 video 태그를 이용해서 재생함
function mpgLoad(idx){
	if(!mpgList[idx]) return;

	var video = document.createElement("video");
	video.addEventListener("loadstart", function(){
		mpgCurrent++;
		$("#loadStatus2").html("BGA - Video Loading "+mpgCurrent+"/"+mpgTotal);
		if(audioCurrent==audioTotal && bgaCurrent == bgaTotal && mpgCurrent == mpgTotal )
			start();
		else
			mpgLoad(++idx);
	},false);
	video.src = url+loadList.MPG[mpgList[idx]];
	video.preload = true;
	video.defaultPlaybackRate = playbackRate;
	video.load();
	mpgSource[mpgList[idx]] = video;
}

function onError(e){
	console.log(e);
}


//로딩이 완료 되면 이 함수를 호출 함
function start(){
	//키 입력 활성화
	$(document).keydown(checkKeyinput);
	$(document).keyup(uncheckKeyinput);
	//로딩 관련 파트 사라지게 함
	$("#loadingImage").css({display:"none"});
	$(".load").css({display:"none"});
	//web worker 개시
	//interval 마다 play 함수 호출하는 역할을 함
	worker = new Worker("/resource/playScript/play.js");
	worker.onmessage = play;
	worker.postMessage({
		intervalTime:intervalTime,
		maxIdx:maxIdx,
		timeList:timeList,
		soundPlayList:soundPlayList,
		bgaPlayList:bgaPlayList,
		bgaLayerList:bgaLayerList,
		playData:playData
	});
	//canvas size & bg 적용
	var gear = document.getElementById("Gear");
	gear.height = playHeight;
	var gearCtx = gear.getContext('2d');
	//기본 gear 그림
	gearCtx.drawImage(resourceSource.bg,0,0,skinInfo.width,playHeight-skinInfo.height); //상단
	gearCtx.drawImage(resourceSource.play,0,playHeight-skinInfo.height);
	animate();
}

function animate(fps) {
	if(typeof fps == 'undefined') fps = 60;
    setTimeout(function() {
        requestAnimationFrame(animate);
		worker.postMessage({get:true});
    }, 1000 / fps);
}

//interval 마다 호출
function play(e){
	//언제 시작 했는지 저장
	if(typeof e.data.startTime != 'undefined') {
		startTimeStamp = e.data.startTime;
		return;
	}
	currentTime = e.data.currentTime; //현재 얼마나 흘렀나? (ms)

	//시간 지난걸 다시 확인 안하게 currentIdx 업데이트
	if(typeof currentTimeList[currentIdx+1] != 'undefined'){
		if(currentTimeList[currentIdx+1]<currentTime) currentIdx++;
	}

	if(e.data.playList)
		playSound(e.data.playList); //배경음 재생
	bindKeySound(currentTime); //키 값에 음 설정

	//miss key 값이 바뀌는걸 반영하기 위해서 사용
	if(typeof poorBGAList[currentTimeList[currentIdx]] != 'undefined')
		badObj = poorBGAList[currentTimeList[currentIdx]];

	drawObjects(currentTime); //기어에 노트 등 그림
	if(bgaDisplay){
		playBGA(e.data.bga); //BGA 그림
		if(e.data.layerList)
			playBGALayer(e.data.layerList); //BGA 위의 BGALayer에 그림
	}

	if(autoplay != "none"){ //auto play일때 처리
		var a = 0;
		var checkTimeList = [];
		while(a < maxIdx){
			if(currentTime > timeList[a]){
				checkTimeList.push(timeList[a]);
				a++;
			} else {
				break;
			}
		}

		//일반 노트일때 이고, 키를 누른 것으로 인식하도록 함
		for(a=0,loopa=checkTimeList.length; a<loopa; a++){
			if(typeof playData[checkTimeList[a]] != 'undefined'){
				for(var c in playData[checkTimeList[a]]){
					if(autoplay == "sc" && c != 6) continue;
					if(typeof autoplayCheckList[checkTimeList[a]] == 'undefined')
						autoplayCheckList[checkTimeList[a]] = {};
					if(typeof autoplayCheckList[checkTimeList[a]][c] != 'undefined') continue;
					autoplayCheckList[checkTimeList[a]][c] = true;
					var e = {};
					e.which = acceptKey[c];
					e.timeStamp = checkTimeList[a] + startTimeStamp;
					e.autoplay = true;
					checkKeyinput(e);
					uncheckKeyinput(e);
					e = null;
				}
			}
		}

		checkTimeList = null;
		a = 0;
		checkTimeList = [];
		while(a < longMaxIdx){
			if(currentTime > longNoteTimeList[a]){
				checkTimeList.push(longNoteTimeList[a]);
				a++;
			} else {
				break;
			}
		}

		//롱노트, 롱노트는 시작/끝이 있으므로 아래 참조
		for(a=0,loopa=checkTimeList.length; a<loopa; a++){
			for(var c in longNoteList[checkTimeList[a]]){
				if(autoplay == "sc" && c != 6) continue;
				if(typeof longNoteList[checkTimeList[a]][c].press == 'undefined'){
					longNoteList[checkTimeList[a]][c].press = true;
					var e = {};
					e.which = acceptKey[c];
					e.timeStamp = checkTimeList[a] + startTimeStamp;
					e.autoplay = true;
					checkKeyinput(e);
					e = null;
				} else {
					if(longNoteList[checkTimeList[a]][c].end > currentTime) continue;
					if(typeof autoplayLongnoteList[checkTimeList[a]] == 'undefined')
						autoplayLongnoteList[checkTimeList[a]] = {};
					if(typeof autoplayLongnoteList[checkTimeList[a]][c] != 'undefined' ) continue;
					autoplayLongnoteList[checkTimeList[a]][c] = true;
					var e = {};
					e.which = acceptKey[c];
					e.timeStamp = longNoteList[checkTimeList[a]][c].end + startTimeStamp;
					e.autoplay = true;
					uncheckKeyinput(e);
					e = null;
				}
			}
		}
		checkTimeList = null;
	}

	//키를 계속 누르고 있을 때 처리
	for(var a in acceptKey){
		if(pushKey[a]){
			checkLongNote(currentTime,a,1);
		}
	}
	markMiss(); //놓친 노트는 miss 처리

	var o = document.getElementById("Play").getContext("2d");
	o.clearRect(0,0,displayInfo.width,displayInfo.height+skinInfo.extra);
	o.drawImage(canvasBuffer,0,0); //버퍼에 그린 데이터를 출력함
	//시간 관련 출력
	statusBar.style.width = Math.floor(currentTime/endTime * timeBarWidth)+'px';
	var realTime = Math.floor((currentTime-firstTime*3)/1000);
	var min = Math.floor(realTime/60);
	if(min<0) min = "-0";
	var sec = (realTime%60);
	if(sec<0) sec = Math.abs(sec);
	if(sec<10) sec = '0'+sec;
	secBox.innerHTML = min+":"+sec;
	//종료 시간이 지나면, 서버에 결과 보내고 결과 페이지로
	if(endTime<currentTime){
		stop();
		resultList.score = targetScore;
		var score = {total:resultList,normalResult:normalResultList,longResult:longResultList,totalNotes:totalNotes,totalGrades:totalGrades,missCut:gradeList[3],ignoreRank:ignoreRank};
		$.ajax({url:"/game/updateScore",data:score,type:"post"})
		.done(function(){
			location.href="/game/result";
		});
	}
	frameCounter.countFps();
}

function FrameCounter(){
	this.callCount = 0; 
	this.framePerSecond = 0; 
	this.beforeTime = 0; 

	this.countFps = function(){    
		//(Date.now() is returns the number of milliseconds elapsed since 1 January 1970 00:00:00 UTC)  
		var nowTime =  Date.now(); //1970년 1월 1일 자정과 현재 날짜 및 시간 사이의 밀리초 값입니다

		//If one second has passed
		if(nowTime - this.beforeTime >= 1000){
			this.framePerSecond = this.callCount;
			this.beforeTime = nowTime;
			this.callCount = 0; 
			document.getElementById('fps').innerHTML = this.framePerSecond  + "fps";
		}

		//Increase frame count per second
		this.callCount++;
	}
}

//배경음 출력
function playSound(playList){
	var sounds = [];
	for(var a=0,loopa=playList.length; a<loopa; a++){
		if(typeof audioSource[playList[a]] == 'undefined') continue;
		var source = context.createBufferSource();
		source.buffer = audioSource[playList[a]];
		source.playbackRate.value = playbackRate;
		source.connect(gainNode);
		sounds.push(source);
	}
	for(var a=0,loopa=sounds.length; a<loopa; a++){
		sounds[a].noteOn(0);
	}
	sounds = null;
}

//BGA 출력
function playBGA(bga){
	if(bga != null) {
		currentBGA = bga;
		if(typeof mpgSource[bga] != 'undefined'){
			mpgSource[bga].play();
			mpgPlay[bga] = true;
		}
	}
	var a = null;
	for(a in mpgPlay){ //동영상 플레이
		bgaCtx.drawImage(mpgSource[a],0,0,bgaSize.w,bgaSize.h);
	}
	a = null;
	if(bga == null) return;
	if(typeof bgaSource[bga] != 'undefined'){
		bgaCtx.putImageData(bgaSource[bga],0,0);
	}
	if(typeof mpgSource[bga] == 'undefined' && typeof bgaSource[bga] == 'undefined') {
		bgaCtx.clearRect(0,0,bgaSize.w,bgaSize.h); //아무것도 설정 안되어 있으면 검은색 화면 처리
		return;
	}
}

//BGA Layer에 그리는거
function playBGALayer(bgaList){
	for(var a=0,loopa=bgaList.length; a<loopa; a++){
		if(typeof bgaSource[bgaList[a]] == 'undefined') {
			bgaLayerCtx.clearRect(0,0,bgaSize.w,bgaSize.h);
			continue;
		}
		bgaLayerCtx.putImageData(bgaSource[bgaList[a]],0,0);
	}
}

//각 이미지 위에 그리는것 처리 (BGA 헤더 참조)
function checkBGAReplace(bga){
	if(typeof bgaSource[bgaReplaceList[bga].no] == 'undefined'){
		bgaLayerCtx.beginPath();
		bgaLayerCtx.rect(bgaReplaceList[bga].targetx, bgaReplaceList[bga].targety, bgaReplaceList[bga].width, bgaReplaceList[bga].height);
		bgaLayerCtx.fillStyle = 'black';
		bgaLayerCtx.fill();
		bgaLayerCtx.closePath();
		var imageData = bgaLayerCtx.getImageData(0,0,bgaSize.w,bgaSize.h);
		for(var b=0,loopb=imageData.data.length; b<loopb; b+=4){
			if(imageData.data[b]==0 && imageData.data[b+1]==0 &&
			   imageData.data[b+2]==0)
				imageData.data[b+3] = 0;
		}
		bgaLayerCtx.putImageData(imageData,0,0);
	} else {
		if(typeof bgaSource[bga] != 'undefined')
			bgaLayerCtx.putImageData(bgaSource[bga],0,0);
		bgaLayerCtx.drawImage(bgaSource[bgaReplaceList[bga].no], bgaReplaceList[bga].x, bgaReplaceList[bga].y,
			bgaReplaceList[bga].width, bgaReplaceList[bga].height,
			bgaReplaceList[bga].targetx, bgaReplaceList[bga].targety, bgaReplaceList[bga].width, bgaReplaceList[bga].height);
	}
	bgaSource[bga] = bgaLayerCtx.getImageData(0,0,bgaSize.w,bgaSize.h);
	bgaLayerCtx.clearRect(0,0,bgaSize.w,bgaSize.h);
}

//노트 그림
function drawObjects(currentTime){
	var ctx = canvasBuffer.getContext("2d");
	ctx.clearRect(0,0,displayInfo.width,displayInfo.height+skinInfo.extra);
	var start = null;
	var per = display;
	var max = currentTime + firstTime * per; //얼마만큼 확인해서 그릴 것인가
	var reverse = displayInfo.height; //노트가 종료되는 시점
	var list = [];
	//animation을 위해 값들 업데이트
	if(targetSize!=perSize){
		var mSize = Math.floor((targetSize - perSize)/10);
		if(mSize==0) perSize = targetSize;
		else perSize += mSize;
		mSize = null;
	}
	if(targetScore!=currentScore){
		var mSize = Math.floor((targetScore - currentScore)/10);
		if(mSize==0) currentScore = targetScore;
		else currentScore += mSize;
		mSize = null;
	}

	//draw background effect
	//키 입력 시 배경 출력
	var a = null;
	var position=0, note=0, width = 0;
	for(a in pushKey){
		if(pushKey[a]) {
			position = getPosition(a);
			switch(a){
				case '1':
				case '3':
				case '5':
				case '9': note = 'w'; width = 34; break;
				case '2':
				case '4':
				case '8': note = 'b'; width = 34; break;
				case '6': note = 'p'; width = 50; break;
			}
			ctx.drawImage(resourceSource["l"+note],0,0, 40, skinInfo.effectbg, position, 0, width, reverse+3);
		}
	}

	//draw lines
	//각 마디별 line 그림
	var currentIdxTmp = currentIdx;
	var loopb = stopTimeList.length;
	var cur = null;
	var stopIdx = null;
	var b = null;
	var currentLineY = null;
	ctx.beginPath();
	ctx.strokeStyle = '#eee';
	while(true){
		if(typeof currentTimeList[currentIdxTmp] == 'undefined') break;
		cur = currentTimeList[currentIdxTmp] - currentTime;
		//노트 멈추는거 있는거 있으면 그만큼 처리..
		stopIdx = null;
		for(b=0; b<loopb; b++){
			if(stopTimeList[b]<=currentTimeList[currentIdxTmp]){
				stopIdx = b;
			} else break;
		}
		if(stopIdx != null){
			if(stopTimeList[stopIdx]>currentTime){ //before stop
				if(currentTimeList[currentIdxTmp]>=stopTimeList[stopIdx] + stopList[stopTimeList[stopIdx]])
					cur = (currentTimeList[currentIdxTmp]-stopList[stopTimeList[stopIdx]]) - currentTime;
			} else if(stopTimeList[stopIdx]<=currentTime && stopTimeList[stopIdx] + stopList[stopTimeList[stopIdx]]>=currentTime) { //stop ing
				cur = (currentTimeList[currentIdxTmp]-stopList[stopTimeList[stopIdx]]) - stopTimeList[stopIdx];
			} else { //after stop
			}
		}
		cur /= 1000;
		currentLineY = reverse + 13 - Math.floor(perSize*cur);
		cur = null; stopIdx = null;
		if(currentLineY<0) { currentLineY = null; break; }
		else if(currentLineY>reverse+13) { currentIdxTmp++; currentLineY = null; continue; }
		else currentIdxTmp++;
		//line 그리기
		ctx.moveTo(skinInfo.noteStart,currentLineY);
		ctx.lineTo(skinInfo.noteEnd,currentLineY);
		currentLineY = null;
	}
	ctx.stroke();
	ctx.closePath();
	loopb = null;
	
	//draw LongNote Objects
	//롱노트 그리기
	loopb = stopTimeList.length;
	var aniNote = Math.floor((curNote+3)/4);

	a = null;
	loopa = null;
	start -= firstTime * 10;
	for(a = 0,loopa=longNoteTimeList.length; a<loopa; a++){
		if(longNoteTimeList[a]>start && longNoteTimeList[a]<max){
			list.push(longNoteTimeList[a]);
		} else if(longNoteTimeList[a]>=max){
			break;
		}
	}

	a = null;
	loopa = null;
	for(a=0,loopa=list.length; a<loopa; a++){
		drawLongObject(longNoteList[list[a]],aniNote,reverse,list[a],stopIdx);
	}

	
	//draw Objects
	//일반노트 그리기
	list = [];
	a = null;
	start = currentTime;
	for(a = 0; a<maxIdx; a++){
		if(timeList[a]>start && timeList[a]<max){
			list.push(timeList[a]);
		} else if(timeList[a]>=max){
			break;
		}
	}

	a = null;
	loopa = null;
	for(a=0,loopa=list.length; a<loopa; a++){
		if(typeof playData[list[a]] == 'undefined') continue;
		cur = list[a] - currentTime;
		stopIdx = null;
		for(var b=0; b<loopb; b++){
			if(stopTimeList[b]<=list[a]){
				stopIdx = b;
			} else break;
		}
		if(stopIdx != null){
			if(stopTimeList[stopIdx]>currentTime){ //before stop
				if(list[a]>=stopTimeList[stopIdx] + stopList[stopTimeList[stopIdx]])
					cur = (list[a]-stopList[stopTimeList[stopIdx]]) - currentTime;
			} else if(stopTimeList[stopIdx]<=currentTime && stopTimeList[stopIdx] + stopList[stopTimeList[stopIdx]]>=currentTime) { //stop ing
				cur = (list[a]-stopList[stopTimeList[stopIdx]]) - stopTimeList[stopIdx];
			} else { //after stop
			}
		}
		cur /= 1000;
		drawObject(playData[list[a]],aniNote,reverse,cur);
		cur = null;
		stopIdx = null;
	}

	curNote++;
	if(curNote==17) curNote = 1;

	//Combo 출력
	if(combo>=2 && showNote<=40){
		ctx.drawImage(resourceSource.combo,skinInfo.comboImageLeft,skinInfo.comboImageTop);
		var currentCombo = combo;
		var tmp = 0;
		var width = 40;
		var score = [];
		while(true){
			tmp = currentCombo % 10;
			currentCombo = Math.floor(currentCombo / 10);
			score.push(tmp);
			if(currentCombo == 0) break;
		}
		var right = 220 + (width/2) * score.length;
		for( a=0; a<score.length; a++){
			right -= width;
			ctx.drawImage(resourceSource["num"+score[a]],right,skinInfo.comboCount);
		}
		right = null;
		score = null;
		width = null;
		tmp = null;
		currentCombo = null;
		showNote++;
	}

	//판정 이미지
	if(showJud!=-1 && showJudTime<=40){
		ctx.drawImage(resourceSource["jud"+resultBind[showJud]],skinInfo.judgeLeft,displayInfo.height-skinInfo.judgeBottom);
		showJudTime++;
	}

	//노트 입력 했을 시 effect 출력하는거 처리
	a = null;
	position = null;
	for(a in effectList){
		if(effectList[a] != 0){
			position = getPosition(a) - 85;
			if(a==6) position += 15;
			ctx.drawImage(resourceSource["e"+Math.floor((effectList[a]+1)/2)],position,displayInfo.height-skinInfo.effectBottom);
			effectList[a]++;
			if(effectList[a]>=11) effectList[a] = 0;
			position = null;
		}
	}

	//각 판정별 숫자 출력
	gradeCtx.clearRect(0,0,100,180);
	a = null
	var height = 0;
	for(a in resultList){
		if(a=="bonus"||a=="score") continue;
		var text = '' + resultList[a];
		var width = gradeCtx.measureText(text).width;
		height += 31;
		gradeCtx.fillText(text, 100 - width, height);
		text = null;
		width = null;
	}
	height = null;

	//max combo 출력
	scoreCtx.clearRect(0,0,120,100);
	var text = '' + maxCombo;
	width = scoreCtx.measureText(text).width;
	scoreCtx.fillText(text, 110 - width, 48);

	//스코어 출력
	text = null;
	width = null;
	text = ''+currentScore;
	width = scoreCtx.measureText(text).width;
	scoreCtx.fillText(text, 100 - width, 98);

	//속도 출력
	speedCtx.clearRect(0,0,100,30);
	text = null;
	width = null;
	text = currentSpeed.toFixed(1);
	width = speedCtx.measureText(text).width;
	speedCtx.fillText(text, 90 - width, 30);


	//변수 해제
	ctx = null;
	start = null;
	per = null;
	max = null;
	reverse = null;
	list = null;
	a = null;
	position = null;
	note = null;
	width = null;
	currentIdxTmp = null;
	loopb = null;
	cur = null;
	stopIdx = null;
	b = null;
	currentLineY = null;
	aniNote = null;
}

//일반노트 출력
function drawObject(data,aniNote,reverse,cur){
	var ctx = canvasBuffer.getContext("2d");
	for(var b in data){
		var position=0, note=0;
		position = getPosition(b);
		switch(b){
			case '1':
			case '3':
			case '5':
			case '9': note = '0'; break;
			case '2':
			case '4':
			case '8': note = '1'; break;
			case '6': note = '2'; break;
		}
		ctx.drawImage(resourceSource["n"+note+aniNote],position,reverse - Math.floor(perSize*cur));
		b = null;
		position = null;
	}
	ctx = null;
}

//롱노트 출력
function drawLongObject(data,aniNote,reverse,timeStamp,stopIdx){
	var ctx = canvasBuffer.getContext("2d");
	var cur = timeStamp - currentTime;
	var stopIdx = null;
	var b = null, loopb = null;
	for(b=0,loopb=stopTimeList.length; b<loopb; b++){
		if(stopTimeList[b]<=list[a]){
			stopIdx = b;
		} else break;
	}
	b = null;
	loopb = null;
	if(stopIdx != null){
		if(stopTimeList[stopIdx]>currentTime){ //before stop
			if(list[a]>=stopTimeList[stopIdx] + stopList[stopTimeList[stopIdx]])
				cur = (list[a]-stopList[stopTimeList[stopIdx]]) - currentTime;
		} else if(stopTimeList[stopIdx]<=currentTime && stopTimeList[stopIdx] + stopList[stopTimeList[stopIdx]]>=currentTime) { //stop ing
			cur = (list[a]-stopList[stopTimeList[stopIdx]]) - stopTimeList[stopIdx];
		} else { //after stop
		}
	}
	if(cur<0) cur = 0;
	cur /= 1000;
	for(b in data){
		var position=0, note=0;
		position = getPosition(b);
		switch(b){
			case '1':
			case '3':
			case '5':
			case '9': note = '0'; break;
			case '2':
			case '4':
			case '8': note = '1'; break;
			case '6': note = '2'; break;
		}
		var width = 34;
		if(b==6) width = 50;
		var Endcur = data[b].end - currentTime;
		if(stopIdx != null){
			if(stopTimeList[stopIdx]>currentTime){ //before stop
				if(data[b].end>=stopTimeList[stopIdx] + stopList[stopTimeList[stopIdx]])
					Endcur = (data[b].end-stopList[stopTimeList[stopIdx]]) - currentTime;
			} else if(stopTimeList[stopIdx]<=currentTime && stopTimeList[stopIdx] + stopList[stopTimeList[stopIdx]]>=currentTime) { //stop ing
				Endcur = (data[b].end-stopList[stopTimeList[stopIdx]]) - stopTimeList[stopIdx];
			} else { //after stop
			}
		}
		if(Endcur < 0) { width = null; Endcur = null; position = null; continue; }
		Endcur /= 1000;
		ctx.drawImage(resourceSource["n"+note+aniNote],position,reverse - Math.floor(perSize*Endcur),width,Math.floor(perSize*Endcur) - Math.floor(perSize*cur)+13);
		width = null; Endcur = null; position = null; 
	}
	ctx = null;
	cur = null;
	stopIdx = null;
	b = null;
	loopb = null;
}

//각 노트 위치별 x 위치 리턴하는 함수
function getPosition(b){
	try{
		var position;
		switch(b){
			case '1':
			case '2':
			case '3':
			case '4':
			case '5': position = 67 + 37 * b;  break;
			case '8':
			case '9': position = 67 + 37 * (b-2); break;
			case '6': position = 40 + 10; break;
		}
		return position;
	} finally {
		position = null;
	}
}

//키음 바인딩
function bindKeySound(currentTime){
	var idx = 0;
	var a = null;
	for(a=0; a<maxIdx; a++){
		if(timeList[a]>currentTime){
			idx = a; break;
		}
	}
	for(a=1; a<=9; a++){
		if(a==7) continue;
		var b = null;
		for(b=idx; b<maxIdx; b++){
			if(typeof playData[timeList[b]] != 'undefined' && typeof playData[timeList[b]][a] != 'undefined'){
				keyObject[a] = playData[timeList[b]][a];
				break;
			}
		}
		b = null;
	}
}

//키 눌렀을 때 처리
function checkKeyinput(e){
	//속도 조절 및 esc 키는 처리
	switch(e.which){
		case 114: if(currentSpeed<=1.0){ targetSize = targetSize / 2.0; display *= 2; currentSpeed /= 2.0; } else { targetSize -= 75; currentSpeed -= 0.25; } break;
		case 115: if(currentSpeed<1.0){ targetSize = targetSize * 2.0; display /= 2; currentSpeed *= 2.0; } else { targetSize += 75; currentSpeed += 0.25; } break;
		case 27: stop(); location.href="/game/bms"; break;
	}
	//autoplay일 때는 무시
	if(typeof e.autoplay == 'undefined'){
		if( (autoplay == "sc" && e.which == acceptKey[6]) || autoplay == "all") return false;
	}
	now = e.timeStamp - startTimeStamp;
	var a = null;
	for(a in acceptKey){
		if(e.which==acceptKey[a]){
			if(pushKey[a]) continue;
			pushKey[a] = true;
			checkJudge(now,a);
			break;
		}
	}
	return false; //나머지 browser 처리를 막기 위해서 사용
}

//score 실제 올라가는거 처리
function updateScore(e){
	//miss 일 때
	if(typeof e.miss != 'undefined'){
		if(e.miss != 0){
			resultList["miss"] += e.miss;
			combo = 0;
			showJud = 4;
			//게이지 감소
			if(!ignoreRank) currentGage -= 1.5;
			if(currentGage<0){
				currentGage = 0;
				ignoreRank = true; //IR에 저장 안되게
			}
			totalNotes++;
			if(typeof e.longNote != 'undefined') longResultList.miss += e.miss;
			else normalResultList.miss += e.miss;
			if(typeof bgaSource[badObj] == 'undefined') return;
			//miss 배경 출력
			var canvas = document.getElementById("BGALayer");
			var ctx = canvas.getContext("2d");
			ctx.clearRect(0,0,canvas.width,canvas.height);
			bgaCtx.drawImage(bgaSource[badObj],0,0,bgaSize.w,bgaSize.h);
			canvas = null;
			ctx = null;
		}
		return;
	}
	//디버깅용 로그
	if(typeof e.log != 'undefined') {
		console.log(e.data);
		return;
	}
	var sound = null;
	if(e.play){ //판정 처리를 해야 할 때,,
		var c = e.judge;
		var grade = e.grade;
		var percent = null;

		//롱노트 일 때 처리
		if(typeof e.longNote != 'undefined' && typeof e.combo != 'undefined') {
			resultList[resultBind[c]] += e.combo;
			longResultList[resultBind[c]] += e.combo;
			var score = longScoreList[c] * e.combo;
			longResultList.score += score;
			targetScore += score;
			if(!ignoreRank) currentGage += longGageList[c];
			if(c==0 && (autoplay == "none" || (autoplay == "sc" && e.object!=6) ) ) {
				percent = (1-grade / gradeList[c]);
				score = parseInt( percent * longScoreList[c] / 2) * e.combo;
				if(!ignoreRank) currentGage += Math.floor(percent * longGageList[c] / 2 * 10)/10;
				longResultList.bonus += score;
				resultList.bonus += score;
				targetScore += score;
			}
			effectList[e.object] = 3;
			score = null;
		} else {
			//일반노트 일 때 처리
			resultList[resultBind[c]]++;
			normalResultList[resultBind[c]]++;
			targetScore += scoreList[c];
			normalResultList.score += scoreList[c];
			totalGrades += gradeList[3] - grade;
			if(!ignoreRank) currentGage += gageList[c];
			totalNotes++;
			if(c==0 && (autoplay == "none" || (autoplay == "sc" && e.object!=6) ) ) {
				percent = (1-grade / gradeList[c]);
				var score = parseInt( percent * scoreList[c] / 2);
				if(!ignoreRank) currentGage += Math.floor(percent * gageList[c] / 2 * 10)/10;
				normalResultList.bonus += score;
				resultList.bonus += score;
				targetScore += score;
			}
			effectList[e.object] = 1;
		}
		if(currentGage > 100) currentGage = 100; //게이지는 100이 안넘게
		if(c<=1) {
			//swell, well은 combo 올림
			if(typeof e.combo != 'undefined') combo+= e.combo;
			else combo++;
			if(maxCombo<combo) maxCombo = combo;
		} else if(c==3) combo = 0;
		showNote = 1;
		showJud = c;
		showJudTime = 1;

		c = null;
		grade = null;

		if(typeof e.sound == 'undefined')
			return;
		sound = e.sound;
	} else {
		sound = keyObject[e.object];
	}
	if(typeof audioSource[sound] == 'undefined')
		return;
	//그 노트에 해당되는 키음 출력
	var source = context.createBufferSource();
	source.buffer = audioSource[sound];
	source.playbackRate.value = playbackRate;
	source.connect(gainNode);
	source.noteOn(0);
	source = null;
}

//키를 땠을 때..
function uncheckKeyinput(e){
	if(typeof e.autoplay == 'undefined'){
		if( (autoplay == "sc" && e.which == acceptKey[6]) || autoplay == "all") return false;
	}
	now = e.timeStamp - startTimeStamp;
	for(var a in acceptKey){
		if(e.which==acceptKey[a]){
			if(pushKey[a]){
				checkLongNote(now,a,2);
			}
			pushKey[a] = false;
		}
	}
}

//판정 worker에 처리 위임
function markMiss(){
	checkMiss(currentTime);
}

//각 worker 정지
function stop(){
	worker.terminate();
}






function checkJudge(currentTime,object){
	//어느놈이 빠를까요 ㅅㅂ
	var min = currentTime - gradeList[3];

	//일반노트 체크
	var curGrade = gradeList[3];
	var curIdx = -1;
	var last = false;
	var grade = null;
	var b = null;
	for(b=0; b<maxIdx; b++){
		if(timeList[b] < min ) continue;
		if(typeof playData[timeList[b]] != 'undefined' && typeof playData[timeList[b]][object] != 'undefined'){
			if(typeof gradedList[timeList[b]+'_'+object] != 'undefined')
				continue;
			grade = Math.abs(timeList[b] - currentTime);
			if(curGrade > grade) { curGrade = grade; curIdx = b; continue; }
			break;
		}
	}
	b = null;
	var normalIdx = curIdx;
	var normalGrade = curGrade;
	curGrade = null;
	curIdx = null;

	//롱노트 체크
	var longMaxIdx = longNoteTimeList.length;
	grade = null;
	curGrade = gradeList[3];
	curIdx = -1;
	for(var b=0; b<longMaxIdx; b++){
		if(longNoteTimeList[b] < min ) continue;
		if(typeof longNoteList[longNoteTimeList[b]] != 'undefined' && typeof longNoteList[longNoteTimeList[b]][object] != 'undefined'){
			if(typeof gradedLongList[longNoteTimeList[b]+'_'+object] != 'undefined')
				continue;
			grade = Math.abs(longNoteTimeList[b] - currentTime);
			if(curGrade > grade) { curGrade = grade; curIdx = b; continue; }
			break;
		}
	}
	var longIdx = curIdx;
	var longGrade = curGrade;
	
	//둘 중에 한놈이 가까운게 없으면..
	if(longIdx == -1 || normalIdx == -1){
		if(longIdx == -1)
			return checkNormal(normalIdx,normalGrade,object);
		else
			return checkLongNote([longIdx,longGrade,currentTime],object,0);
	} else { //가까운거를 우선순위로 처리
		if(longGrade >= normalGrade)
			return checkNormal(normalIdx,normalGrade,object);
		else
			return checkLongNote([longIdx,longGrade,currentTime],object,0);
	}
}

//노트 찾고 판정 처리
function checkNormal(curIdx,curGrade,object){
	var play = false;
	var sound = null;
	var judge = null;
	var gradePush = null;
	for(var c=0; c<4; c++){
		if(gradeList[c] > curGrade){
			play = true;
			gradedList[timeList[curIdx]+'_'+object] = true;
			sound = playData[timeList[curIdx]][object];
			judge = c;
			gradePush = curGrade;
			break;
		}
	}
	updateScore({
		sound:sound,
		judge:judge,
		grade:gradePush,
		object:object,
		play:play
	});
	play = null;
	sound = null;
	judge = null;
	gradePush = null;
}

//일반노트, 롱노트 miss 처리
function checkMiss(currentTime){
	var min = currentTime - gradeList[3];
	var miss = 0;
	var b = 0;
	for(b=0; b<maxIdx; b++){
		if(timeList[b] < min){
			for(var a in acceptKey){
				if(typeof playData[timeList[b]] != 'undefined' && typeof playData[timeList[b]][a] != 'undefined'){
					if(typeof gradedList[timeList[b]+'_'+a] == 'undefined'){
						gradedList[timeList[b]+'_'+a] = true;
						miss++;
					}
				}
			}
		}
	}
	updateScore({
		miss:miss
	});
	b = null;
	miss = 0;
	var longMaxIdx = longNoteTimeList.length;
	for(var b=0; b<longMaxIdx; b++){
		if(longNoteTimeList[b] < min){
			for(var a in acceptKey){
				if(typeof longNoteList[longNoteTimeList[b]] != 'undefined' && typeof longNoteList[longNoteTimeList[b]][a] != 'undefined'){
					if(typeof gradedLongList[longNoteTimeList[b]+'_'+a] == 'undefined'){
						gradedLongList[longNoteTimeList[b]+'_'+a] = true;
						miss++;
					}
				}
			}
		}
	}
	updateScore({
		miss:miss,
		longNote:true
	});
	longMaxIdx = null;
}

//롱노트 처리
function checkLongNote(currentTime,object,cont){
	if(cont==0){ //누르기 시작
		curIdx = currentTime[0];
		curGrade = currentTime[1];
		currentTime = currentTime[2];
		var sound = null;
		var judge = null;
		var gradePush = null;
		var play = false;
		var c = null;
		for(c=0; c<4; c++){
			if(gradeList[c] > curGrade){
				play = true;
				gradedLongList[longNoteTimeList[curIdx]+'_'+object] = true;
				sound = longNoteList[longNoteTimeList[curIdx]][object].key;
				judge = c;
				gradePush = curGrade;
				if(c!=3) {
					longnoteCurrent[object] = {judge:c,lastCheck:currentTime,remain:0,grade:curGrade,end:longNoteList[longNoteTimeList[curIdx]][object].end};
					longNoteCheckList[object] = false;
				}
				break;
			}
		}
		updateScore({
			longNote:true,
			sound:sound,
			judge:judge,
			grade:gradePush,
			object:object,
			play:play
		});
		sound = null;
		judge = null;
		gradePush = null;
		play = null;
		c = null;
		return;
	}

	if(cont==1){ //누르는 중
		if(typeof longnoteCurrent[object] == 'undefined' || longnoteCurrent[object] == null || longNoteCheckList[object] )
			return;
		try {
			longNoteCheckList[object] = true;
			//롱노트 지났나 체크
			var grade = currentTime - longnoteCurrent[object].end;
			var remain = null;
			var combo = null;
			if(grade > 0){ //롱노트 끝지점 지남
				if(grade>gradeList[3]) { //miss 뜰 때 까지 안땜
					updateScore({
						longNote:true,
						object:object,
						miss:1
					});
					longNoteCheckList[object] = false;
					longnoteCurrent[object] = null;
				}
				return; //끝지점 지났으면 pass
			}
			remain = currentTime - longnoteCurrent[object].lastCheck;
			remain += longnoteCurrent[object].remain;
			combo = Math.floor(remain / longNoteTick);
			longnoteCurrent[object].lastCheck = currentTime;
			longnoteCurrent[object].remain = remain % longNoteTick;
			updateScore({
				longNote:true,
				judge:longnoteCurrent[object].judge,
				object:object,
				grade:longnoteCurrent[object].grade,
				combo:combo,
				play:true
			});
			longNoteCheckList[object] = false;
			return;
		} finally {
			grade = null;
			remain = null;
			combo = null;
		}
	}

	if(cont == 2){ //손 땜
		if(typeof longnoteCurrent[object] == 'undefined' || longnoteCurrent[object] == null )
			return;
		try {
			var grade = Math.abs(currentTime - longnoteCurrent[object].end); //시간 지난거 체크
			var judge = 0;
			var c=0;
			for(c=0; c<4; c++){
				if(gradeList[c] > grade){
					judge = c;
					break;
				}
			}
			longnoteCurrent[object] = null;
			longNoteCheckList[object] = false;
			if(c==4){
				updateScore({
					longNote:true,
					object:object,
					miss:1
				});
				return;
			}
			updateScore({
				longNote:true,
				judge:judge,
				object:object,
				grade:grade,
				play:true
			});
			return;
		} finally {
			grade = null;
			judge = null;
			c = null;
		}
	}
	updateScore({
		play:false,
		object:object
	});
}