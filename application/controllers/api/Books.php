<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Books extends CI_Controller {

	public function __construct() {
        parent::__construct();
        $this->load->model('Bookmodel');
        $this->load->model('Usermodel');
        $this->load->library('Encryption1');
    }

	public function listAll(){
        $header = getallheaders();
        $xmlstr = @file_get_contents('php://input');

        if (count($xmlstr) <= 0) {
            echo APPERROR;
        } 
        else{
            $userdata=  json_decode($xmlstr);
            $token=@$userdata->AccessToken;
            if(strlen($token)>10){
                $r=array();
                    $r['result']['status']='Success';
                    //$r['status']="success";
                    $r['result']['Data']=$this->Bookmodel->listallWithToken($token);
                    echo json_encode($r);
            }else{
                echo APPERROR;
            }
        }
                    
        //echo  json_encode(array('result'=>$this->Bookmodel->listAll()));;
    }
    
    public function listallBook(){
                    $r=array();
                    $r['result']['status']='Success';
                    //$r['status']="success";
                    $r['result']['Data']=$this->Bookmodel->listAll();
                    echo json_encode($r);
      //  echo  json_encode(array('result'=>$this->Bookmodel->listallBook()));;
    }
	
    public function bookDetails(){
		$header = getallheaders();
        $xmlstr = @file_get_contents('php://input');

		if (count($xmlstr) <= 0) {
            echo APPERROR;
        } 
        else{
        	$userdata=  json_decode($xmlstr);
        	$id=@$userdata->BookId;
			if(!empty($id) && $id!=NULL){
				$res=$this->Bookmodel->bookDetails($id);
				if(count($res)>0){
					//echo json_encode(array('result'=>base_url().$res));
                    $r=array();
                    $r['result']['status']='Success';
                    //$r['status']="success";
                    $r['result']['Data']=array('BookUrl'=>base_url().$res);
                    echo json_encode($r);
				} 
				else{
					echo NORECORD;
				}
			}
			else{
				echo NORECORD;;
			}
		}
	}

    public function getBooks(){
        $header = getallheaders();
        $xmlstr = @file_get_contents('php://input');

        if (count($xmlstr) <= 0) {
            echo APPERROR;
        } 
        else{
            $userdata=  json_decode($xmlstr);
            $BookID=@$userdata->BookId;
            $token=@$userdata->AccessToken;
             if(strlen($token)>10 && $BookID>0){
                $res=$this->Bookmodel->addBooks($token,$BookID);
                $bookId=@$res[0]->book_meta_id;
                //echo $bookId;exit;
                $userid=$this->Usermodel->getIdByToken($token);
                if(count($userid)>0 && count($res) >0){
                    $userType=@$userid[0]->is_pub;
                      //  if(!$userType){
                            $uid=@$userid[0]->userid;
                            
                            $key=$this->encryption1->encode($bookId.$uid);
                            $res=$this->Bookmodel->addBookToUser($bookId,$uid,$key);
                            if($res==1)
                            {
                                echo APPSUCCESS;;
                            }
                            else if($res==-1){
                                 $r=array();
                                 $r['result']['status']='Already Added';
                                  echo json_encode($r);
                            }
                            else{
                                echo NORECORD;
                            }
                       /* }
                        else{
                            echo APPERROR;
                        }*/
                }
            }
            else{
                echo APPERROR;
            }
        }
    }
	public function freeDownload(){
		$header = getallheaders();
        $xmlstr = @file_get_contents('php://input');

		if (count($xmlstr) <= 0) {
            echo APPERROR;
        } 
        else{
        	$userdata=  json_decode($xmlstr);
        	$BookID=@$userdata->BookId;
        	 $token=@$userdata->AccessToken;
        	 if(strlen($token)>10 && $BookID>0){
        		$res=@$this->Bookmodel->getBookUrlFromToken($token,$BookID);
        		if(count($res)>0){
                   // print_r($res);
                    $userid=$this->Usermodel->getIdByToken($token);
                    //print_r($userid);
                    $v=microtime();
        			//$this->zip->read_file($res);//'public/ebook/free/pdf-sample.pdf');
					//$this->zip->archive('public/User/myBook'.$v.'.zip'); 
					// Download the file to your desktop. Name it "my_backup.zip"
					//$this->zip->archive('my_backup'.$v.'.zip');
        			echo json_encode(array('result'=>array('status'=>'Success','BookZip'=>base_url().$res[0]->url,'BookXml'=>base_url().$res[0]->book_xml)));
        		}
        		else{
        			echo NORECORD;
        		}
        	}
        	else{
        		echo APPERROR;
        	}
        }
	}

	public function getMyBooks(){
		$header = getallheaders();
        $xmlstr = @file_get_contents('php://input');

		if (count($xmlstr) <= 0) {
            echo APPERROR;
        }
        else{
            $userdata=  json_decode($xmlstr);
            $token=@$userdata->AccessToken;
        	if(strlen($token)>10){
        		$res=$this->Bookmodel->getBookFromToken($token);
        		if(count($res)>0 && $res!==False){
                    $r=array();
                    $r['result']['status']='Success';
                    //$r['status']="success";
                    $r['result']['Data']=$res;
        			echo json_encode($r);
        		}
        		else{
        			echo NORECORD;
        		}
        	}
        	else{
        		echo APPERROR;
        	}
        }
	}

	/*------------------View Book--------------------*/
	public function readBook(){
		$header = getallheaders();
        $xmlstr = @file_get_contents('php://input');

		if (count($xmlstr) <= 0) {
            echo APPERROR;
        }
        else{
            $userdata=  json_decode($xmlstr);
            $token=@$userdata->AccessToken;
            $BookID=@$userdata->BookId;
        	if(strlen($token)>10 && $BookID>0){
        		$res=$this->Bookmodel->getBookUrlFromToken($token,$BookID);
        		if(count($res)>0 && $res!==False){
                    $r=array();
                    $r['result']['status']='Success';
                    //$r['status']="success";
                    $r['result']['Data']=array('BookUrl'=>base_url().$res);
                    echo json_encode($r);
        		}
        		else{
        			echo NORECORD;
        		}
        	}
        	else{
        		echo APPERROR;
        	}
        }
	}

	public function DownloadBook(){
		$header = getallheaders();
        $xmlstr = @file_get_contents('php://input');

		if (count($xmlstr) <= 0) {
            echo APPERROR;
        }
        else{
            $userdata=  json_decode($xmlstr);
            $token=@$userdata->AccessToken;
            $BookID=@$userdata->BookId;
        	if(strlen($token)>10 && $BookID>0){
        		$res=$this->Bookmodel->getBooksDataFromToken($token,$BookID);
        		if(count($res)>0 ){
                    if(isset($res[0]->open_key)){
                       // $this->zip->add_data('key.txt', '$res[0]->open_key');
                    }
                  //  $this->zip->read_file($res[0]->url);//'public/ebook/free/pdf-sample.pdf');
                   // $this->zip->archive('public/User/myBook'.$v.'.zip'); 
                    // Download the file to your desktop. Name it "my_backup.zip"
                   // $this->zip->archive('my_backup'.$v.'.zip');
                   // echo json_encode(array('result'=>array('BookUrl'=>$res[0]->url)));
                        echo json_encode(array('result'=>array('status'=>'Success','BookZip'=>base_url().$res[0]->url)));
        		}
        		else{
        			echo NORECORD;
        		}
        	}
        	else{
        		echo APPERROR;
        	}
        }
	}

    /*-------------------Update Last Scene Sync-----------------------*/

    public function LastSync(){
        $header = getallheaders();
        $xmlstr = @file_get_contents('php://input');

        if (count($xmlstr) <= 0) {
            echo APPERROR;
        }else{
            $userdata=  json_decode($xmlstr);
            $token=@$userdata->AccessToken;
            $BookID=@$userdata->BookId;
            $SceneSync=@$userdata->SceneSync;
	    $MusicSync=@$userdata->MusicSync;
            if(strlen($token)>10 && $BookID>0){
                if($this->Bookmodel->updateSyncScene($token, $BookID, $SceneSync,$MusicSync)){
                    echo  APPSUCCESS;
                 }
                else
                    echo APPERROR;
            }
        }
    }
    public function LastMusicSync(){
        $header = getallheaders();
        $xmlstr = @file_get_contents('php://input');

        if (count($xmlstr) <= 0) {
            echo APPERROR;
        }else{
            $userdata=  json_decode($xmlstr);
            $token=@$userdata->AccessToken;
            $BookID=@$userdata->BookId;
            $MusicSync=@$userdata->MusicSync;
            if(strlen($token)>10 && $BookID>0){
                if($this->Bookmodel->updateSyncMusic($token, $BookID, $MusicSync)){
                    echo  APPSUCCESS;
                 }
                else
                    echo APPERROR;
            }
        }
    }

    public function removebook(){
        $header = getallheaders();
        $xmlstr = @file_get_contents('php://input');

        if (count($xmlstr) <= 0) {
            echo APPERROR;
        } 
        else{
            $userdata=  json_decode($xmlstr);
            $BookID=@$userdata->BookId;
             $token=@$userdata->AccessToken;
             if(strlen($token)>10 && $BookID>0){
                $res=@$this->Bookmodel->removeBook($token,$BookID);
               if($res){ echo APPSUCCESS;}else{ echo APPERROR;}
            }
            else{
                echo APPERROR;
            }
        }
    }


}
