
var gradeList = null;
var playData = null;
var maxIdx = null;
var timeList = null
var acceptKey = null;
var longNoteList = null;
var longNoteTimeList = null;
var gradedList = {};
var gradedLongList = {};
var longnoteCurrent = {};
var longNoteTick = null;
var longNoteCheckList = {'6':false,'1':false,'2':false,'3':false,'4':false,'5':false,'8':false,'9':false};

onmessage = function(e){
	if(typeof e.data.init != 'undefined'){
		//변수 넘겨 받음
		gradeList = e.data.gradeList;
		playData = e.data.playData;
		longNoteList = e.data.longNoteList;
		longNoteTimeList = e.data.longNoteTimeList;
		timeList = e.data.timeList;
		acceptKey = e.data.acceptKey;
		maxIdx = timeList.length;
		longNoteTick = e.data.longNoteTick;
		return;
	}
	if(typeof e.data.miss != 'undefined'){
		return checkMiss(e.data.currentTime); //miss 처리 위임 받았을 때
	}
	if(typeof e.data.longnote != 'undefined'){
		return checkLongNote(e.data.currentTime,e.data.object,e.data.cont); //롱노트 지속적인 처리 위임 받음
	}

	checkJudge(e.data.currentTime,e.data.object); //롱노트나 일반 노트중 어느게 가까운지 확인하고 판단하기 위한 함수
};

function checkJudge(currentTime,object){
	try {
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
	} finally {
		min = null;
		curGrade = null;
		curIdx = null;
		last = null;
		grade = null;
		normalIdx = null;
		normalGrade = null;
		longMaxIdx = null;
		longIdx = null;
		longGrade = null;
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
	postMessage({
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
	postMessage({
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
	postMessage({
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
		postMessage({
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
					postMessage({
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
			postMessage({
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
				postMessage({
					longNote:true,
					object:object,
					miss:1
				});
				return;
			}
			postMessage({
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
	postMessage({
		play:false,
		object:object
	});
}