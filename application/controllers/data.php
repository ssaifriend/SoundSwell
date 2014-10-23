<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
@desc BMS 파일 로딩 시 사용되는 controller
*/
class Data extends CI_Controller {
	public function __construct(){
		parent::__construct();
	}

	/*
	@desc BMS 파일에 wav로 기록되어 있으나, 실제로는 ogg일 때 파일을 못찾는 것을 해소하기 위한 함수
	*/
	function download($group){
		$data = explode('/',$_SERVER['REDIRECT_URL']);
		$path = $data[2];
		$file = $data[3];

		$fp = $this->getfp($path,$file);

		if(!$fp) {
			$fp = $this->getfp($path,str_replace("wav","ogg",$file));
			if(!$fp) exit();
		}
		header("Cache-Control: public");
		header("Expires: ".date("D, j M Y G:i:s T",strtotime("+1 year")));
		while(!feof($fp)){
			echo fread($fp,102400);
		}
		fclose($fp);
	}

	/*
	@desc 파일의 대소문자 구분을 없애기 위한 함수
	*/
	function getfp($path,$file){
		$folder = "./files";
		$fp = null;
		if(file_exists($folder.'/'.$path.'/'.$file)) $fp = fopen($folder.'/'.$path.'/'.$file,"rb");
		else {
			$dir2 = dir($folder.'/'.$path);
			while( ($entry2 = $dir2->read()) !== false){
				if($entry2 == '.' || $entry2 == '..') continue;
				if(strtolower($entry2) == strtolower($file)) {
					$fp = fopen($folder.'/'.$path.'/'.$entry2,"rb");
					break;
				}
			}
			$dir2->close();
		}
		return $fp;
	}

	function _remap($method){
		return $this->download($method);
	}
}