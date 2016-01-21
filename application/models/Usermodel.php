<?php

class Usermodel extends CI_Model {

    //put your code here

    public function getSpecificPublisher($UserEmail) {
        $this->db->where('email',$UserEmail);
        $res =  $this->db->get(PUBLISHER)->result();
        return $res;
    }
    public function getSpecificUser($UserEmail) {
        $this->db->where('user_email',$UserEmail);
        $res =  $this->db->get(USERS)->result();
        return $res;
    }
    
    public function registerUser($data, $tbl=USERS) {
        $insert_id = 0;
        if ($this->db->insert($tbl, $data)) {
            $this->db->where('user_email',$data['user_email']);
            $res=$this->db->get($tbl)->result();
            return $res[0]->user_id;
        } else {
            return $insert_id;
        }
    }
    
    
    public function authenticateUser($UserEmail,$Password) {
        $array = array('user_email' => $UserEmail, 'user_password' => $Password);
        $this->db->where($array);
        $res =  $this->db->get(USERS)->result();
        return $res;
    }
    public function authenticatePublisher($UserEmail,$Password) {
		$Password=str_replace("==",'',$Password);
        $array = array('email' => $UserEmail, 'password' => $Password);
        $this->db->select('pub_id as user_id');
        $this->db->where('email like "'.$UserEmail.'"');
		$this->db->where("password like '".$Password."%'");
		//$res=$this->db;
		//$this->db->where($array);
        $res =  $this->db->get(PUBLISHER)->result();
        return $res;
    }
    
    public function getUserId($UserEmail) {
        $array = array('user_email' => $UserEmail);
        $this->db->where($array);
        $res =  $this->db->get(USERS)->result_array();
        return $res[0]['user_id'];
    }

    public function updatetoken($token,$userid,$uid,$type=FALSE){
        if($userid=='' || $userid<=0 || strlen($token)<10){
            return false;
        }
        else{

            $array = array('userid' => $userid);
            $data=array('token'=>$token,'is_pub'=>$type);
            $this->db->where('userid',$userid);
            $this->db->where('is_pub',$type);
            $r=count($this->db->get(USERAUTH)->result());
            if($r>0){
                $this->db->where('userid',$userid);
                $this->db->where('is_pub',$type);
                if ($this->db->update(USERAUTH, $data)) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            }
            else{
               $data=array('authid'=>$uid,'token'=>$token,'is_pub'=>$type);
               $data['userid']= $userid;
               if ($this->db->insert(USERAUTH, $data)) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            }
            
        }
    }

    public function getIdByToken($token){
        if(strlen($token)>10){
            $this->db->where("token LIKE '".$token."%'");
            $data=array('date_updated'=>date('Y-m-d H:i:s'));
            $res =  $this->db->get(USERAUTH)->result();
            return $res;
        }
        else{
            return false;
        }
    }


    public function checkToken($token='all'){
        if($token=='all'){

        }else{

        }
    }
     public function updateTokenTime($token){
      if(strlen($token)>10){
            $this->db->where("token LIKE '".$token."%'");
            $data=array('date_updated'=>date('Y-m-d H:i:s'));
            if($this->db->update(USERAUTH, $data)){
                return TRUE;
            }
            else{
                return FLASE;
            }
        }
        else{
            return FALSE;
        }  
    }

    public function removeToken($token){
        if(strlen($token)>10){
            $this->db->where("token LIKE '".$token."%'");
            if($this->db->delete(USERAUTH)){
                return TRUE;
            }
            else{
                return FLASE;
            }
        }
        else{
            return FALSE;
        }
    }

    public function socialUser($data,$where){
        $this->db->where($where);
        $res=$this->db->get(USERS)->result();
        if(count($res)>0){
            $this->db->where($where);
            if($this->db->update(USERS,$data)){
                return $res[0]->user_id;
            }
            else{
                return 0;
            }
        }
        else{
            if($this->db->insert($tbl=USERS,$data)){
			//	return 'yes';
                 $this->db->where('user_email',$data['user_email']);
            	$res=$this->db->get($tbl)->result();
            	return $res[0]->user_id;
            }
            else{
                //return 0;
                return 'no';
            }
        }
    }

    public function updatelock($userid,$lock){
        if(isset($userid) && isset($lock)){
            $data=array('is_parental_lock_enable'=>$lock);
            $this->db->where('user_id',$userid);
            if($this->db->update(USERS,$data)){
                return TRUE;
            }
            else{
                return FALSE;
            }
        }
    }
     public function updatelockwithpasscode($userid,$lock,$passcode){

        if(isset($userid) && isset($lock) && isset($passcode)){
            $data=array('is_parental_lock_enable'=>$lock,'passcode'=>$passcode);
            $this->db->where('user_id',$userid);
            if($this->db->update(USERS,$data)){
                return TRUE;
            }
            else{
                return FALSE;
            }
        }
    }

    public function updatepassword($userid,$OldPassword,$NewPassword){
        if(isset($userid) && isset($OldPassword) && !empty($userid) && !empty($OldPassword) && isset($NewPassword)&& !empty($NewPassword)){
            $data=array('user_password'=>$NewPassword);
            $this->db->where('user_id',$userid);
            $this->db->where('user_password',$OldPassword);
            if($this->db->update(USERS,$data)){
                return TRUE;
            }
            else{
                return FALSE;
            }
        }
    }

    public function resetrequest($userid,$code,$uid){
	$this->db->where('user_id',$userid);
	$this->db->delete(PASSWORDRESET);
	$data=array('p_reset'=>$uid,'user_id'=>$userid,'token'=>$code);
	if($this->db->insert(PASSWORDRESET,$data)){return TRUE;}else{ return FALSE;}
    }

    public function getResetRequest($UserEmail,$ResetCode){
        if($ResetCode>0){
            $this->db->from(PASSWORDRESET);
            $this->db->join(USERS,USERS.'.user_id='.PASSWORDRESET.'.user_id');
            $this->db->where('token',$ResetCode);
            $this->db->where(USERS.'.user_email',$UserEmail);
            $res=$this->db->get()->result();
            return $res;
        }
        else{
            return array();
        }

    }
    public function getResetRequestUserId($pcode){
        if($pcode>0){
            $this->db->where('p_reset',$pcode);
            return $this->db->get(PASSWORDRESET)->result();
        }
        else{
            return array();
        }

    }
    public function ChangePassword($userid,$password){
        $data=array('user_password'=>$password);
        $this->db->where('user_id',$userid);
        if($this->db->update(USERS,$data)){
            return TRUE;
        }
        else{
            return FALSE;
        }

    }
    public function get_existing_token($user_id){
		 $array = array('userid' => $user_id);
		 $this->db->where($array);
        $res =  $this->db->get(USERAUTH)->result();
        return $res;
		}

}
