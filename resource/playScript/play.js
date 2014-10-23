
var currentTime = 0;
var currentIdx = 0;

var intervalVal = null;
var intervalTime = 0;
var maxIdx = 0;
var timeList = null;
var soundPlayList = null;
var bgaPlayList = null;
var bgaLayerList = null;
var playData = null;
var prevTime = null;

onmessage = function(e){
	if(typeof e.data.reset != 'undefined'){
		currentTime = 0;
		return;
	}
	if(typeof e.data.get != 'undefined') {
		play();
		return;
	}

	intervalTime = e.data.intervalTime;
	maxIdx = e.data.maxIdx;
	timeList = e.data.timeList;
	soundPlayList = e.data.soundPlayList;
	bgaPlayList = e.data.bgaPlayList;
	bgaLayerList = e.data.bgaLayerList;
	playData = e.data.playData;

	prevTime = (new Date()).getTime();
	postMessage({startTime:prevTime});
};

function play(){
	var now = (new Date()).getTime();
	currentTime += now - prevTime; //지난 시간 만큼 누적 처리
	prevTime = now;
	var checkTimeList = [];
	var a=currentIdx;
	while(a < maxIdx){
		if(currentTime > timeList[a]){
			checkTimeList.push(timeList[a]);
			a++;
		} else {
			currentIdx = a;
			break;
		}
	}

	var playList = [];
	var bga = null;
	var layerList = [];

	var b = null;
	var loopb = null;
	for(a=0,loopa=checkTimeList.length; a<loopa; a++){
		//재생 할 BGM이 있나 찾기
		if(typeof soundPlayList[checkTimeList[a]] != 'undefined'){
			for(b=0,loopb=soundPlayList[checkTimeList[a]].length; b<loopb; b++){
				var data = soundPlayList[checkTimeList[a]][b];
				if(data == '00') { data = null; continue; }
				playList.push(data);
				soundPlayList[checkTimeList[a]][b] = '00';
				data = null;
			}
			b = null;
			loopb = null;
		}
		//재생 할 BGA가 있나 찾기
		if(typeof bgaPlayList[checkTimeList[a]] != 'undefined'){
			for(b=0,loopb=bgaPlayList[checkTimeList[a]].length; b<loopb; b++){
				var data = bgaPlayList[checkTimeList[a]][b];
				if(data == '00') { data = null; continue; }
				bga = data;
				bgaPlayList[checkTimeList[a]][b] = '00';
				data = null;
			}
			b = null;
			loopb = null;
		}
		//BGA Layer에 출력 할 것이 있나 찾기
		if(typeof bgaLayerList[checkTimeList[a]] != 'undefined'){
			for(b=0,loopb=bgaLayerList[checkTimeList[a]].length; b<loopb; b++){
				var data = bgaLayerList[checkTimeList[a]][b];
				if(data == '00') { data = null; continue; }
				layerList.push(data);
				bgaLayerList[checkTimeList[a]][b] = '00';
				data = null;
			}
			b = null;
		}
/* //only auto play
		if(typeof playData[checkTimeList[a]] != 'undefined'){
			for(var b in playData[checkTimeList[a]]){
				for(var c in playData[checkTimeList[a]][b]){
					for(var d=0,loopd=playData[checkTimeList[a]][b][c].length; d<loopd; d++){
						var data = playData[checkTimeList[a]][b][c][d];
						if(data == '00') continue;
						playList.push(data);
						playData[checkTimeList[a]][b][c][d] = '00';
					}
				}
			}
		}
*/
	}
	//main으로 값을 넘김
	postMessage({
		playList:playList,
		currentTime:currentTime,
		bga:bga,
		layerList:layerList
	});
	
	now = null;
	checkTimeList = null;
	a = null;
	playList = null;
	bga = null;
	layerList = null;
}
