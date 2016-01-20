<?php

class Bookmodel extends CI_Model {

    //put your code here

    public function listall(){
        $this->db->select(BOOKS.'.*,'.BOOKS.'.book_meta_id as book_id,'.PUBLISHER.'.pub_name');
        $this->db->from(BOOKS);
        $this->db->join(PUBLISHER,PUBLISHER.'.pub_id='.BOOKS.'.pub_id');
        $this->db->where(BOOKS.'.is_active',1);
        $res=$this->db->get()->result_array();
        $res=array_map(function($value) { $value['book_thumb']=base_url().$value['book_thumb'];
                                          $value['book_cover']=base_url().$value['book_cover'];
                                          $value['url']=base_url().$value['url'];
                                          return $value;
                                         }, $res);
        return $res;
    }
     public function listallWithToken($token){
        if(strlen($token)>10){
			
		$array = array('token' => $token);
		$this->db->where("token LIKE '".$token."%'");
		$res =  $this->db->get(USERAUTH)->result();
		$UserType=@$res[0]->is_pub;
		$userId=@$res[0]->userid;
        $this->db->from(USERS);
        $this->db->where("token LIKE '".$token."%'");
        $this->db->join(USERAUTH,USERS.'.user_id='.USERAUTH.'.userid');
        $data=array('date_updated'=>date('Y-m-d H:i:s'));
        $res =  $this->db->get()->result();
        $userAge = @$res[0]->Age;

        $this->db->select(BOOKS.'.*,'.BOOKS.'.book_meta_id as book_id,'.PUBLISHER.'.pub_name');
        $this->db->from(BOOKS);
        $this->db->join(PUBLISHER,PUBLISHER.'.pub_id='.BOOKS.'.pub_id');
        if($res[0]->is_parental_lock_enable==1){
          $this->db->where(BOOKS.'.age < 13');  
        }
        if(count($res) && $res[0]->is_super_user ==1)
        {

        }
        else{
			if($UserType==1){
			     $this->db->where(BOOKS.".pub_id like '".$userId."'");
			}else{
				 $this->db->where(BOOKS.'.is_active',1); 
               //  $this->db->where("age <= '".$userAge."'");
			}
         
        }

        $res=$this->db->get()->result_array();
        $res=array_map(function($value) { $value['book_thumb']=base_url().$value['book_thumb'];
                                          $value['book_cover']=base_url().$value['book_cover'];
                                          $value['url']=base_url().$value['url'];
                                          return $value;
                                         }, $res);

         return $res;
        }
        else{
            return array();

        }
    }
    public function listallBook(){
        $this->db->select(BOOKS.'.*,'.BOOKS.'.book_meta_id as book_id,'.PUBLISHER.'.pub_name');
        $this->db->from(BOOKS);
        $this->db->join(PUBLISHER,PUBLISHER.'.pub_id='.BOOKS.'.pub_id');
        $res=$this->db->get()->result_array();
        $res=array_map(function($value) { $value['book_thumb']=base_url().$value['book_thumb'];
                                          $value['book_cover']=base_url().$value['book_cover'];
                                          $value['url']=base_url().$value['url'];
                                          return $value;
                                         }, $res);
        return $res;
    }

    public function bookDetails($id){
        $this->db->select(BOOKS.'.*,pub_name');
        $this->db->from(BOOKS);
        $this->db->join(PUBLISHER,PUBLISHER.'.pub_id='.BOOKS.'.pub_id');
        $this->db->where(BOOKS.'.is_active',1);
        $res=$this->db->get()->result();
        return $res;
    }
    public function getBookUrl($id){
        $this->db->select(BOOKS.'.url');
        $this->db->from(BOOKS);
        $this->db->where('pub_id='.$id);
        $res=$this->db->get()->result();
        return @$res[0]->url;
    }
    /*-------------------Updateuser book sync --------------------------*/
    public function updateSyncScene($token, $bookid, $SyncScene,$MusicSync)
    {
        //echo 'hi1';
        if(strlen($token)>10){
            $array = array('token' => $token);
            $token=str_replace('==','',$token);
            $this->db->from(USERAUTH);
            $this->db->join(USERS,USERS.'.user_id='.USERAUTH.'.userid');
            $this->db->where("token LIKE '".$token."%'");

            //echo $token;


            $res =  $this->db->get()->result();
           // print_r($res);
                if(!is_null($res)){
                    $userid=@$res[0]->userid;
                    //echo $userid;
                    if(!is_null($userid)){
                        $data=array('scene_sync'=>$SyncScene,'music_sync'=>$MusicSync);
                        $this->db->where('user_id',$userid);
                        $this->db->where('book_id',$bookid);
                        if($this->db->update(USERBOOKS,$data)){
                            return true;
                        }else{
                            return false;
                        }
                    }

                }else{
                    return false;
                }

        }else{
            return false;
        }
    }


    /*----------------------Get book By user Type-----------------------*/
    public function getBookFromToken($token){
        if(strlen($token)>10){
            $array = array('token' => $token);
			$token=str_replace('==','',$token);
            $this->db->from(USERAUTH);
            $this->db->join(USERS,USERS.'.user_id='.USERAUTH.'.userid');
            $this->db->where("token LIKE '".$token."%'");
			//print_r($this->db);
            $res =  $this->db->get()->result();
            $UserType=@$res[0]->is_pub;
            $userid=@$res[0]->userid;
            $user  =@$res[0]->Age;
            //print_r($res);
			
            if(!$UserType){
                $lck=$this->islock($userid);
                $islock=$lck;
                $this->db->from(USERBOOKS);
                $this->db->join(BOOKS,BOOKS.'.book_meta_id='.USERBOOKS.'.book_id');
                $this->db->join(PUBLISHER,PUBLISHER.'.pub_id='.BOOKS.'.pub_id');
                $this->db->where('user_id',$userid); 
                if($islock==1){
                     $this->db->where(BOOKS.'.age < 13');  
                }
                //$this->db->where('age',$userAge);
                $res=$this->db->get()->result_array();
                 $res=array_map(function($value) { $value['book_thumb']=base_url().$value['book_thumb'];
                                          $value['book_cover']=base_url().$value['book_cover'];
                                           $value['url']=base_url().$value['url'];
										   $value['book_id']=$value['book_meta_id'];
										   // $value['user_id']=$value['pub_id'];
                                          return $value;
                                         }, $res);
                return $res;
            }
            else{
				//$this->db->select('DISTINCT '.BOOKS.'.book_meta_id as b ,'.BOOKS.'.*,'.PUBLISHER.'.*,'.USERBOOKS.'.*');
				$lck=$this->islock($userid);
                $islock=@$lck[0]->is_parental_lock_enable;
				echo $islock;
				print_r($lck);
                $this->db->from(BOOKS);
                $this->db->where(BOOKS.'.pub_id',$userid);
                $this->db->join(PUBLISHER,PUBLISHER.'.pub_id='.BOOKS.'.pub_id');
				$this->db->join(USERBOOKS,BOOKS.'.pub_id='.USERBOOKS.'.user_id');
                $res=$this->db->get()->result_array();
                 $res=array_map(function($value) { $value['book_thumb']=base_url().$value['book_thumb'];
                                          $value['book_cover']=base_url().$value['book_cover'];
                                           $value['url']=base_url().$value['url'];
										    $value['book_id']=$value['book_meta_id'];
											 $value['user_id']=$value['pub_id'];
                                          return $value;
                                         }, $res);
                return $res;
            }
        }
        else{
            return false;
        }
    }

    /*-----------Get Boo Url By User Type------------*/
    public function getBookUrlFromToken($token,$bookId){
        if(strlen($token)>10){
            $array = array('token' => $token);
            $this->db->where("token LIKE '".$token."%'");
            $res =  $this->db->get(USERAUTH)->result();
            $UserType=@$res[0]->is_pub;
            $userid=@$res[0]->userid;

            if(!$UserType && count($res)>0){
                $this->db->select(BOOKS.'.url,'.BOOKS.'.book_xml');
                $this->db->from(BOOKS);
                //$this->db->join(BOOKS,BOOKS.'.book_meta_id='.USERBOOKS.'.book_id');
                //$this->db->where('user_id',$userid);
                $this->db->where('book_meta_id',$bookId);
                $res=$this->db->get()->result();
                return $res;
            }
            else if(count($res)>0){
                $this->db->select(BOOKS.'.url,'.BOOKS.'.book_xml');
                $this->db->where('pub_id',$userid);
                $this->db->where('book_meta_id',$bookId);
                $res=$this->db->get(BOOKS)->result();
                return $res;
            }
        }
        else{
            return array();
        }
    }
    /*---------------*/
    public function addBooks($token,$bookId){
        if(strlen($token)>10){
            $this->db->where('book_meta_id',$bookId);
            $res=$this->db->get(BOOKS)->result();
            return $res;
        }
        else{
            return false;
        }
    }

    public function addBookToUser($bookId,$userid,$openKey){
        $data=array('user_id'=>$userid,
                     'book_id'=>$bookId,
                     'open_key'=>$openKey
                    );
        $this->db->where('book_id',$bookId);
        $this->db->where('user_id',$userid);
        $res=count($this->db->get(USERBOOKS)->result());
        if($res<=0){
          if($this->db->insert(USERBOOKS,$data))
            {
                return 1;
            }
            else{
                return 0;
            }  
        }
        else{
            return -1;

        }
        
    }


    public function getBooksDataFromToken($token,$bookId){
         if(strlen($token)>10){
            $array = array('token' => $token);
            $this->db->where("token LIKE '".$token."%'");
            $res =  $this->db->get(USERAUTH)->result();
            $UserType=@$res[0]->is_pub;
            $userid=@$res[0]->userid;
            if(!$UserType && count($res)>0){
                $this->db->select(BOOKS.'.url,'.BOOKS.'.book_xml');
                $this->db->from(BOOKS);
               // $this->db->join(BOOKS,BOOKS.'.book_meta_id='.USERBOOKS.'.book_id');
                //$this->db->where('user_id',$userid);
                $this->db->where('book_meta_id',$bookId);
                $res=$this->db->get()->result();
                return $res;
            }
            else if(count($res)>0){
                $this->db->select(BOOKS.'.url');
                $this->db->where('pub_id',$userid);
                $this->db->where('book_meta_id',$bookId);
                $res=$this->db->get(BOOKS)->result();
                return $res;
            }
        }
        else{
            return array();
        }
    }

    /*-----------------------Music Sync-----------*/
    public function updateSyncMusic($token, $bookid, $MusicSync)
    {
        //echo 'hi1';
        if(strlen($token)>10){
            $array = array('token' => $token);
            $token=str_replace('==','',$token);
            $this->db->from(USERAUTH);
            $this->db->join(USERS,USERS.'.user_id='.USERAUTH.'.userid');
            $this->db->where("token LIKE '".$token."%'");
            $res =  $this->db->get()->result();
                if(!is_null($res)){
                    $userid=@$res[0]->userid;
                    if(!is_null($userid)){
                        $data=array('music_sync'=>$MusicSync);
                        $this->db->where('user_id',$userid);
                        $this->db->where('book_id',$bookid);
                        if($this->db->update(USERBOOKS,$data)){
                            return true;
                        }else{
                            return false;
                        }
                    }
                    else{ return false;}

                }else{
                    return false;
                }
        }else{
            return false;
        }
    }

    public function islock($userid){
        if($userid>0){
            $this->db->where('user_id',$userid);
            $res1=$this->db->get(USERS)->result();
            if(count($res1)>0){
                if(isset($res1[0]->user_id)){
                    if($res1[0]->is_parental_lock_enable==1){
                        return 1;
                    }else{ return 0;}
                }else{return 0;}
            }
            else{
                return 0;
            }
        }
        else{
            return 0;
        }

    }
    public function removeBook($token,$bookId){
         if(strlen($token)>10){
            $array = array('token' => $token);
            $this->db->where("token LIKE '".$token."%'");
            $res =  $this->db->get(USERAUTH)->result();
            $UserType=@$res[0]->is_pub;
            $userid=@$res[0]->userid;
            if(!$UserType && count($res)>0){
               // $this->db->select(BOOKS.'.url,'.BOOKS.'.book_xml');
                $this->db->from(BOOKS);
               // $this->db->join(BOOKS,BOOKS.'.book_meta_id='.USERBOOKS.'.book_id');
                //$this->db->where('user_id',$userid);
                $this->db->where('book_meta_id',$bookId);
                $res=$this->db->get()->result();
                if(count($res)>0){
                    $this->db->where('book_id',$res[0]->book_meta_id);
                    $this->db->where('user_id',$userid);
                    if($this->db->delete(USERBOOKS)){return true;}else{return false;}
                }else{
                    return false;
                }
            }
            else if(count($res)>0){
                $this->db->where('book_id',$bookId);
                $this->db->where('user_id',$userid);
                if($this->db->delete(USERBOOKS)){return true;}else{return false;}
            }
        }
        else{
            return false;
        }
    }
}
