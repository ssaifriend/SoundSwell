<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
@desc 회원 정보 관련 class
*/
class MemberModel extends CI_Model {
    private $memberInfo = array();
    private $updateInfo = array();

    public function __construct(){
        parent::__construct();
        $this->updateInfo = (object)array();
    }

	/*
	@desc id로 회원 찾기
	*/
    function _getMemberById($id){
        $data = $this->db->where("vcUserId",$id)->get("member");
        if($data->num_rows()==0) return false;
        $data = $data->row();
        $this->setInfo($data);
        return true;
    }

	/*
	@desc 회원 번호로 회원 찾기
	*/
    function _getMemberByNo($no){
        $data = $this->db->where("nSeqNo",$no)->get("member");
        if($data->num_rows()==0) return false;
        $data = $data->row();
        $this->setInfo($data);
        return true;
    }

	/*
	@desc 값 저장
	*/
    function setInfo($data){
        $this->memberInfo = $data;
    }

	/*
	@desc 값 설정, 위와 별도로 저장함
	*/
    function set($col,$data){
        $this->updateInfo->{$col} = $data;
    }

	/*
	@desc 값 return, 수정한 데이터를 우선적으로 return 함
	*/
    function get($col){
        if(isset($this->updateInfo->{$col})) return $this->updateInfo->{$col};
        if(isset($this->memberInfo->{$col})) return $this->memberInfo->{$col};
        return null;
    }

	/*
	@desc 로그인 처리 및 로그 기록
	*/
    function doLogin(){
        $this->db->set(array("nUserNo"=>$this->memberInfo->nSeqNo,"dtRegdate"=>date("Y-m-d H:i:s"),"vcIP"=>$this->input->ip_address()))->insert("stat_login");
        $this->session->set_userdata("memberNo",$this->memberInfo->nSeqNo);
    }

	/*
	@desc 로그아웃 처리
	*/
    function doLogout(){
        $this->session->set_userdata("memberNo",null);
    }

	/*
	@desc 로그인 했는지,
	*/
    function isLogin(){
        $no = $this->session->userdata("memberNo");
        if($no) return true;
        return false;
    }

	/*
	@desc 위에서 설정한 값을 DB에 기록함 (변경한 필드만 저장하기 위해서 이렇게 함)
	*/
    function doUpdate(){
        $this->db->where('nSeqNo',$this->memberInfo->nSeqNo)->set($this->updateInfo)->update("member");
        if($this->db->affected_rows()==0) return false;

		//반영 했으면 실제 데이터로 처리함
        foreach($this->updateInfo as $key => $val)
            $this->memberInfo->{$key} = $val;
		$this->updateInfo = (object)array(); //clean
        return true;
    }

	/*
	@desc 회원 가입 처리
	*/
    function doJoin(){
        $this->updateInfo->dtRegdate = date("Y-m-d H:i:s");
        $this->db->set($this->updateInfo)->insert("member");

        if($this->db->insert_id()) return true;
        return false;
    }

}
