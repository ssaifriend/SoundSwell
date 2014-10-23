<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AdminModel extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	/*
	@desc BMS 목록을 Cache에 저장하는 함수
	*/
	public function makeBMSCache(){
		set_time_limit(0);
		//폴더에서 BMS 목록을 검사해서 1인용인것만 담음
		$path = "./files";
		$dir = dir($path);
		$bmslist = array();
		$allow = array(".bms",".bme",".bml");
		while( ($entry = $dir->read()) !== false){
			if($entry == '.' || $entry == '..') continue;
			if(is_dir($path.'/'.$entry)){
				$dir2 = dir($path.'/'.$entry);
				while( ($entry2 = $dir2->read()) !== false){
					if($entry2 == '.' || $entry2 == '..') continue;
					$ext = substr($entry2,-4);
					if(in_array($ext,$allow)){
						$data = file_get_contents($path.'/'.$entry.'/'.$entry2);
						//제목, 레벨, 플레이어 값 추출
						preg_match_all("!\#TITLE (.*)!",$data,$title);
						preg_match_all("!\#PLAYLEVEL (.*)!",$data,$level);
						preg_match_all("!\#PLAYER (.*)!",$data,$player);
						if($player[1][0] != 1) continue; //1인용 아니면 pass
						$title = $title[1][0];
						$enc = $this->detectEncoding($title);
						if($enc != "utf-8") $title = iconv($enc,"utf-8",$title); //일본어를 정상적으로 출력할 수 있게 인코딩 변환
						//hash 값 구하고 bmslist 배열에 저장
						$hash = md5_file($path.'/'.$entry.'/'.$entry2);
						$bmslist[$hash] = array("group"=>$entry,"file"=>$entry2,"title"=>$title,"level"=>$level[1][0],"enc"=>$enc);
					}
				}
				$dir2->close();
			}
		}
		$dir->close();

		$this->load->driver('cache');

		//bms 플레이와 관련된 cache를 만들기 위해 아래 함수로 넘김
		foreach($bmslist as $hash => $data){
			$this->makeBMSDataCache($hash,$bmslist[$hash]);
		}

		//다 만들 었으면 최종적으로 목록과 관련된걸 bmslist를 file cache에 저장
		$this->cache->file->save('bmslist', $bmslist, 3600*24*365*10);
	}

	/*
	@desc BMS 데이터 캐쉬 만드는 함수
		  bmsInfoData 즉, 위에서 생성한 BMS Data를 이 함수 내에서 수정 할 수 있게 call by reference로 받음
		  BPM을 바꾸는 옵션이 있으므로 인자로 받음
	*/
	public function makeBMSDataCache($hash,&$bmsInfoData,$changeBPM = 1){
		$path = "./files";
		$fileName = "/{$bmsInfoData['group']}/{$bmsInfoData['file']}";
		$url = "{$bmsInfoData['group']}";
		//파일 읽기
		$fileData = file_get_contents($path.$fileName);

		//줄로 나누고, 인코딩 확인 후, utf-8이 아니면 변환
		$fileData = explode("\n",$fileData);
		$fileCount = sizeof($fileData);
		for($a=0; $a<$fileCount; $a++){
			$fileData[$a] = trim($fileData[$a]);
			if($bmsInfoData['enc'] && $bmsInfoData['enc'] != "utf-8"){
				$fileData[$a] = iconv($bmsInfoData['enc'],"utf-8",$fileData[$a]);
			}
		}
		$bmsInfo = array();
		$loadList = array();
		$playData = array();
		$channelInterval = array();

		//실제로 추출할 데이터 header 목록
		//infoList는 플레이와 상관 없는 정보 데이터, playDataList는 플레이와 관계 있는 데이터
		$infoList = array("PLAYER","TITLE","ARTIST","GENRE","PLAYLEVEL","BPM","RANK","TOTAL","VOLWAV","STAGEFILE","VIDEOFILE","LNTYPE","LNOBJ");
		$playDataList = array("WAV","BMP","BGA","BPM","STOP","BGA");

		//각 라인별로 확인
		for($a=0; $a<$fileCount; $a++){
			//각 라인의 처음은 #으로 시작함, 아님 무시
			if(!$fileData[$a] || substr($fileData[$a],0,1)!='#') continue;
			//공백, :이 없으면 정상적인 값이 아니라고 판단하고 그 라인 무시
			$spacePos = strpos($fileData[$a],' ');
			if($spacePos === false && strpos($fileData[$a],':')===false) {
				continue;
			}
			//# 부터 공백 전까지 값을 구해서 infoList에 있는지 확인하고 있으면 bmsInfo에 저장
			$keyword = substr($fileData[$a], 1, $spacePos - 1);
			if(in_array($keyword, $infoList)){
				$bmsInfo[$keyword] = substr($fileData[$a], $spacePos+1);
				continue;
			}
			//플레이와 관련된 resource인지 구함 (자세한 설명은 별도로 주어지는 웹 페이지 확인)
			$firstThird = substr($fileData[$a], 1, $spacePos-3);
			if(in_array($firstThird, $playDataList)){
				$extraInfo = substr($fileData[$a], 1+strlen($firstThird), $spacePos - (1+strlen($firstThird)));
				//파일 명이 mpg일 경우 웹에서 재생이 불가능 하기 때문에 별도로 webm 파일로 변환하고 webm 파일을 불러오게 만듬
				$filename = rawurlencode(strtolower(substr($fileData[$a], $spacePos+1)));
				if(strtolower(substr($filename,-3)) == "mpg") { $loadList['MPG'][$extraInfo] = substr($filename,0,-3)."webm"; continue; }
				$loadList[$firstThird][$extraInfo] = $filename;
				continue;
			}
			//진짜 플레이 데이터
			$first = substr($fileData[$a], 1, 3); //마디 번호
			$channel = substr($fileData[$a], 4, 1); //채널
			$object = substr($fileData[$a], 5, 1); //노트 Line
			//0번 채널은 event 채널임, 2,6번 Line은 2자씩 끊어서 저장 할 수 있는 데이터가 아님
			//아래부터 0번 채널은 채널+노트 Line을 붙여 말함
			if($channel=='0' && ($object=='2' || $object=='6' )) $data = substr($fileData[$a],7);
			else $data = str_split(substr($fileData[$a], 7),2); //나머지는 2자씩 저장하는 데이터로, 플레이 데이터임
			$playData[$first][$channel][$object][] = $data; //실제 플레이 마디 데이터임 이 크기로 나눈게 실제 1개의 키 값이 차지하는 시간임
			//배열 순서는 마디번호/채널/노트Line으로 구성된 배열에 배열로 저장
		}

		//뒤에서부터 데이터를 검사 해서 노트가 없으면 게임이 계속되는 것을 방지 하기 위해 지워버림
		$newPlayData = array();
		krsort($playData); //reverse 시킴
		$check = false;
		foreach($playData as $first => $data1){
			if(!$check){
				foreach($data1 as $channel =>$data2){
					if($channel == 0) continue; //0번 채널은 event 채널이므로 실제 데이터가 있는 것으로 판단함
					foreach($data2 as $object => $data3){
						foreach($data3 as $dataList){
							for($a=0,$loopa=sizeof($dataList);$a<$loopa; $a++){
								if($dataList[$a] != '00') { $check = true; break(4); }
							}
						}
					}
				}
			}
			if($check == true){
				$newPlayData[$first] = $data1;
			}
		}
		//뒤에서부터 데이터를 저장하기 때문에 원래대로 돌림
		ksort($newPlayData);
		$playData = $newPlayData; //지운 데이터를 다시 저장

		//아래 부터는 마디 - 채널 - 노트 Line으로 저장된 데이터를 성능 향상을 위해 시간 - 채널 - 노트Line으로 변경하는 작업임
		$currentBPM = $bmsInfo['BPM'] * $changeBPM; //실제 플레이 될 BPM (BPM 변경 때문에..)
		$firstTime = floor(60000 / ($bmsInfo['BPM'] * $changeBPM) * 4); //1마디당 걸리는 시간
		$currentTime = $firstTime * 3; //처음 3마디는 속도 변경같은거 하게 비워둠
		$newPlayData = array(); //변경된 데이터
		$soundPlayList = array(); //BGM으로 재생 될 키 값
		$bgaPlayList = array(); //BGA으로 재생 될 키 값
		$bgaLayerList = array(); //BGA위에 Layer 형식으로 재생 될 키 값
		$poorBGAList = array(); //Miss가 났을 때 출력 할 키 값
		$stopList = array(); //노트가 내려오다가 멈추는게 정의되어 있으므로 별도로 저장
		$currentTimeList = array(0,$firstTime,$firstTime*2,$firstTime*3); //이 배열은 마디 Line을 출력할 배열임
		$longNoteList = array(); //롱노트 목록

		$longNote = array(); //각 Line별로 목록 저장할 임시 배열
		$longNoteStart = array(); //아래에서 자세하게 설명
		$is7Key = false; //7키 인지 체크
		//각 마디별로 확인 시작
		foreach($playData as $first => $data1){
			$currentFirstTime = $firstTime; //기본적인 1마디 시간
			//03번 채널은 BPM 변경 채널임, 위에서 마디 나눈 데이터로 각 마디 시간별로 BPM을 변경 함
			if(isset($data1['0']['3']) && is_array($data1['0']['3'])){
				$currentFirstTime = $data1['0']['3'][0]; //위에서 1마디 시간을 설정 했는데 아래에서 각 위치 별로 설정 하기 위해서 배열을 복사
				$currentFirstTimeList = $currentFirstTime; //이 배열은 그 BPM에 대한 1마디 시간을 저장하기 위한 배열
				$currentFirstTimeRemain = $currentFirstTime; //이 마디에서 현재까지 얼마나 흘렀나 저장하기 위한 배열
				$sum = 0;
				for($a=0,$loopa=sizeof($currentFirstTime); $a<$loopa; $a++){
					$currentFirstTime[$a] = hexdec($currentFirstTime[$a]) * $changeBPM; //hex값이기 때문에 정수로 변경
					if($currentFirstTime[$a]==0) $currentFirstTime[$a] = $currentBPM; //값이 0이면 잘못된 값이기 때문에 이전에 사용한 BPM을 적용
					$currentBPM = $currentFirstTime[$a]; //현재 BPM을 저장
					$firstTime = $currentFirstTimeList[$a] = floor(60000/$currentFirstTime[$a]*4); //이 BPM에 대한 1마디 시간 저장
					$currentFirstTimeRemain[$a] = $sum; //그 마디의 현재 위치에서 지금까지 흐른 시간
					$sum += $currentFirstTimeList[$a] / $loopa; //현재 마디의 시간을 누적함
				}
			}
			//08번 채널은 03번 채널과 비슷한데, header에 BPM이 정의되어 있고, 정의된 값을 적용
			if(isset($data1['0']['8']) && is_array($data1['0']['8'])){
				$currentFirstTime = $data1['0']['8'][0];
				$currentFirstTimeList = $currentFirstTime;
				$currentFirstTimeRemain = $currentFirstTime;
				$sum = 0;
				for($a=0,$loopa=sizeof($currentFirstTime); $a<$loopa; $a++){
					$currentFirstTime[$a] = $loadList['BPM'][$currentFirstTime[$a]] * $changeBPM; //여기가 다른데, loadList에 저장된 값을 사용함
					if($currentFirstTime[$a]==0) $currentFirstTime[$a] = $currentBPM;
					$currentBPM = $currentFirstTime[$a];
					$firstTime = $currentFirstTimeList[$a] = floor(60000/$currentFirstTime[$a]*4);
					$currentFirstTimeRemain[$a] = $sum;
					$sum += $currentFirstTimeList[$a] / $loopa;
				}
			}
			//09번 채널은 일정 시간 멈추는 기능임 /192 로 나눈만큼 멈춤
			if(isset($data1['0']['9']) && is_array($data1['0']['9'])){
				$stopTime = $data1['0']['9'][0]; //목록으로 되어 있기 때문에 실제로 멈출 시간을 계산하기 위해 목록 복사
				$stopSum = $stopTime;
				$sum = 0;
				for($a=0,$loopa=sizeof($stopTime); $a<$loopa; $a++){
					//key 값이 0이면 멈출 시간도 0임
					$stopTime[$a] = $loadList['STOP'][$stopTime[$a]] / 192; //얼마만큼 멈출지
					if(is_array($currentFirstTime)){ //위에서 BPM변경 했으면 식이 달라짐
						$currentIdx = floor($a/$loopa*sizeof($currentFirstTime)); //이 stop 위치가 지정된 마디가 BPM의 몇번째 마디인지 확인
						$stopTime[$a] = floor($currentFirstTimeList[$currentIdx] * $stopTime[$a]);//실제로 멈출 시간
					} else $stopTime[$a] = floor($stopTime[$a] * $currentFirstTime);
					$sum += $stopTime[$a]; //누적함 (아래에 식을 보면 왜 이렇게 했는지 알 수 있음)
					$stopSum[$a] = $sum;
				}
			} else
				$stopTime = null;
			//02번 채널은 마디 단축임, 지정된 값 만큼 마디 지속 시간 변경 함
			if(isset($data1['0']['2']) && isset($data1['0']['2'])){
				if(is_array($currentFirstTime)){
					for($a=0,$loopa=sizeof($currentFirstTime); $a<$loopa; $a++){
						$currentFirstTime[$a] *= $data1['0']['2'][0];
						$currentFirstTimeList[$a] *= $data1['0']['2'][0];
						$currentFirstTimeRemain[$a] *= $data1['0']['2'][0];
					}
					$sum *= $data1['0']['2'][0];
				} else
					$currentFirstTime *= $data1['0']['2'][0];
			}
			//롱노트 관련 파트임 아래에서 설명
			if(isset($bmsInfo['LNTYPE']) && $bmsInfo['LNTYPE']==2){
				foreach( $longNote as $key => $value){
					if(!$longNote[$object]) continue;
					if(!is_array($data1['1'][$key]['5'])){
						$longNoteList[$longNoteStart[$object]][$object] = array("key"=>$longNote[$object],"end"=>$currentTime);
						$longNote[$object] = $longNoteStart[$object] = false; 
					}
				}
			}
			foreach( $data1 as $channel =>$data2){
				foreach($data2 as $object => $data3){
					foreach($data3 as $dataList){
						for($a=0,$loopa=sizeof($dataList);$a<$loopa; $a++){
							if($channel!=5) {
								if($dataList[$a] == '00') continue; //5번 채널은 롱노트 채널임 이외의 채널에서 0으로 나온 값은 null값이므로 무시
							} else {
								//LNTYPE에 대한 설명은 포멧 설명 페이지 참조
								if( (!isset($bmsInfo['LNTYPE']) || (isset($bmsInfo['LNTYPE']) && $bmsInfo['LNTYPE']!=2) ) && $dataList[$a] == '00') continue;
							}
							if(is_array($currentFirstTime)){ //BPM변경이 있었으면 얼마나 지났고, 얼마만큼의 길이를 가지는지 확인 해야 함
								$currentIdx = floor($a/$loopa*sizeof($currentFirstTime)); //BPM배열의 index
								$currentIdx2 = floor($currentIdx*$loopa/sizeof($currentFirstTime)); //이 마디의 idx
								//이 마디의 시작시간 + BPM변경이 이루어 지는 지금 이 마디까지 지난 시간 + BPM 변경이 이루어지는 이 마디에서 얼마나 흘렀나 = 이 노트의 시작시간
								$currentObjectTime = $currentTime + $currentFirstTimeRemain[$currentIdx] + floor(( $a - $currentIdx2 ) * $currentFirstTimeList[$currentIdx] / $loopa);
							} else
								$currentObjectTime = $currentTime + floor($a / $loopa * $currentFirstTime); //BPM 변경이 없으면, 마디를 1/n으로 나눠서 단순 계산
							if($stopTime != null) { //만약에 노트 멈추는게 있으면 얼마만큼 멈추었는지 계산해서 이 노트의 시작시간에 누적함
								$currentIdx = floor($a/$loopa*sizeof($stopTime));
								$currentObjectTime += $stopSum[$currentIdx];
							}
							//아까 언급 했다 시피 0번 채널은 event 채널로 시간별로 데이터를 기록하면 됨
							if($channel==0){
								if($object==1) $soundPlayList[$currentObjectTime][] = $dataList[$a];
								else if($object==4) $bgaPlayList[$currentObjectTime][] = $dataList[$a];
								else if($object==6) $poorBGAList[$currentObjectTime][] = $data3;
								else if($object==7) $bgaLayerList[$currentObjectTime][] = $dataList[$a];
								else if($object==9) $stopList[$currentObjectTime-$stopSum[$currentIdx]] = $stopTime[$currentIdx];
							} else if($channel==5){
								//롱노트 채널임 00데이터가 롱노트가 이어지는 LNTYPE 1이 있고, 같은 키 값이 나와야지 롱노트가 되는 LNTYPE 2가 있음.
								//기본적으로 LNTYPE 2로 인식 하고, 이 채널에서 처음 나온 위치가 롱노트 시작시간임
								//LNTYPE 1은 00이 아닌 다른 값이 나오면 그 값이 롱노트 끝나는 시점임
								//LNTYPE 2는 같은 값이 나와야 되는데 longNote 배열에 저장되어 있기 때문에 그걸로 확인
								if($longNote[$object] && 
									( ($bmsInfo['LNTYPE']==2 && $longNote[$object] != $dataList[$a] ) ||
									  ($bmsInfo['LNTYPE']!=2 && $dataList[$a] != '00') )
									){
									//key는 재생할 key, end는 롱노트 종료 시점
									$longNoteList[$longNoteStart[$object]][$object] = array("key"=>$longNote[$object],"end"=>$currentObjectTime);
									$longNote[$object] = $longNoteStart[$object] = false; 
									if($bmsInfo['LNTYPE']!=2) continue;
								}
								//이 파트가 롱노트 시작 데이터를 저장하는 파트임
								if(!$longNote[$object] && $dataList[$a] != '00'){
									$longNoteStart[$object] = $currentObjectTime; //롱노트 시작
									$longNote[$object] = $dataList[$a]; //재생 할 음 키 값
								}
							} else if($channel == 1){ //channel == 1만 고려
								//롱노트 다른 타입으로 LNOBJ가 있는데, 정의된 키 값 이전에 00이 아닌 값이 나오면 그 값의 시점이 롱노트 시작 시점이고,
								//LNOBJ의 키 값이 나온 시점이 롱노트 종료 시점이다. 그리고 시작때 저장 했던 값은 재생 할 음임.
								//그리고, 롱노트 시작시점에 이미 저장되어 있을 일반 노트는 array_pop함수로 일반노트로 안나오게 제거함
								if(isset($bmsInfo['LNOBJ']) && $bmsInfo['LNOBJ']){
									if($dataList[$a]==$bmsInfo['LNOBJ']){
										$longNoteList[$longNote[$channel][$object]['current']][$object] = array("key"=>$longNote[$channel][$object]['key'],"end"=>$currentObjectTime);
										unset($newPlayData[$longNote[$channel][$object]['current']][$object]);
										$soundPlayList[$currentObjectTime][] = $dataList[$a];
									} else
										$longNote[$channel][$object] = array("key"=>$dataList[$a],"current"=>$currentObjectTime);
								}
								//8,9번 Line이 나오면 7키라는 것이므로,,, 체크
								if($object == 8 || $object == 9) $is7Key = true;
								//이 작업이 끝나면 저장함. 키 순서는 노트가 나오는 시간 - 채널 - 노트 Line임
								$newPlayData[$currentObjectTime][$object] = $dataList[$a];
							}
						}
					}
				}
			}
			if((isset($data1['0']['3']) && is_array($data1['0']['3'])) || (isset($data1['0']['8']) && is_array($data1['0']['8']))){
				//BPM이 변경되었으면, 시간을 총 합쳐야 마디 시간이므로 기 사용한 sum 변수 사용
				$currentTime += $sum;
			} else {
				//아님 그냥 1마디 시간 사용
				$currentTime += $currentFirstTime;
			}
			//물론 멈췄으면 그것도 누적함.
			if(isset($data1['0']['9']) && is_array($data1['0']['9'])){
				$currentTime += $stopSum[sizeof($stopSum)-1];
			}
			//노트 라인을 출력 할 수 있게 저장함
			$currentTimeList[] = $currentTime;
		}
		$currentTimeList[] = $currentTime+$firstTime;
		$currentTimeList[] = $currentTime+$firstTime*2;
		$currentTimeList[] = $currentTime+$firstTime*3;
		//노트 다 나오고 3마디는 더미로 출력함

		//BGA헤더는 관련 문서 참조
		for($a=0,$loopa=sizeof($loadList['BGA']); $a<$loopa; $a++){
			$loadList['BGA'][$a] = explode(" ",$loadList['BGA'][$a]);
		}

		//이렇게 정리 된 데이터를 시간순서대로 정렬함
		ksort($newPlayData);
		ksort($longNoteList);
		//시간만 따로 뽑아서 정리함
		$timeList = array_merge(array_keys($newPlayData),array_keys($soundPlayList),array_keys($bgaPlayList),array_keys($bgaLayerList));
		sort($timeList);
		$timeList = array_values(array_unique($timeList));

		//여기서 정리된 데이터를 배열에 저장하고,
		$bmsInfo['firstTime'] = floor(60000 / ($bmsInfo['BPM'] * $changeBPM) * 4);
		$bmsInfoData['7Key'] = $is7Key;
		$bmsInfoData['loadImage'] = $bmsInfo['STAGEFILE'];
		$insertData = array();
		$insertData['data'] = $bmsInfoData;
		$insertData['bmsInfo'] = $bmsInfo;
		$insertData['loadList'] = $loadList;
		$insertData['playData'] = $newPlayData;
		$insertData['soundPlayList'] = $soundPlayList;
		$insertData['bgaPlayList'] = $bgaPlayList;
		$insertData['bgaLayerList'] = $bgaLayerList;
		$insertData['timeList'] = $timeList;
		$insertData['currentTimeList'] = $currentTimeList;
		$insertData['poorBGAList'] = $poorBGAList;
		$insertData['stopList'] = $stopList;
		$insertData['stopTimeList'] = array_keys($stopList);
		$insertData['longNoteList'] = $longNoteList;
		$insertData['longNoteTimeList'] = array_keys($longNoteList);

		if($changeBPM==1){ //BPM 변경이 안되었으면 CI 캐쉬에 저장함 10Y
			$this->cache->file->save($hash, $insertData, 3600*24*365*10);
		} else //BPM 변경 아니면 플레이시 사용할꺼기 때문에 return해버림
			return $insertData;
	}

	/*
	@desc 떤 인코딩인지 확인하기 위한 함수
	*/
	public function detectEncoding($str) {
		$encodingSet = array("ascii","euc-kr","Shift-JIS","utf-8");
		foreach ($encodingSet as $v) {
			$tmp = @iconv($v, $v, $str);
			if (md5($tmp) == md5($str)) return $v;
		}
		return false;
	}

}
