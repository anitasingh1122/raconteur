<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

	
	public function __construct() {
        parent::__construct();
        $this->load->model('Usermodel');
        $this->load->library('Encryption1');
    }
	public function signin()
	{
		$header = getallheaders();
        $xmlstr = @file_get_contents('php://input');

		if (count($xmlstr) <= 0) {
            echo APPERROR;
        } 
        else 
        {
            $userdata=  json_decode($xmlstr);
            $UserEmail = @$userdata->UserEmail;
            $Password = @$userdata->Password;;
		$Password= $this->encryption1->encode($Password);
            $type=@$userdata->UserType;
            
			 if(strtolower($type)=='publisher'){
           		/*--------------------------Login PUBLISHER---------------------------*/

			 	    $userExits = $this->Usermodel->getSpecificPublisher($UserEmail);
	            if(count($userExits) > 0) 
	            {
	                $authenticateUser = $this->Usermodel->authenticatePublisher($UserEmail,$Password);
	                
	                if(count($authenticateUser) > 0){
	                        $re=json_decode(APPSUCCESS,true);
	                        $token=$this->encryption1->encode(microtime().$Password);
	                        if($this->Usermodel->updatetoken($token,@$authenticateUser[0]->user_id,$this->encryption1->getGUID(),true)){
		                        $re['result']['userid']=@$authenticateUser[0]->user_id;
		                        $re['result']['token']=$token;
								$re['result']['UserType']='2';
		                        echo json_encode($re);
		                    }
		                    else{
		                    	echo APPERROR;
		                    }
	                }
	                else
	                    echo APPERROR;
	            }
	            else {
	                echo NOTREGISTER;
	            } 

        	}
    		else{
           		/*--------------------------Login USER---------------------------*/
				//$Password= $this->encryption1->encode($Password);
    			$userExits = $this->Usermodel->getSpecificUser($UserEmail);
	            if(count($userExits) > 0) 
	            {
	            		
	            	if(strlen($this->encryption1->decode($Password))>4){
	                	$authenticateUser = $this->Usermodel->authenticateUser($UserEmail,$Password);
		                if(count($authenticateUser) > 0){
		                        $re=json_decode(APPSUCCESS,true);
		                       $user_id= @$authenticateUser[0]->user_id;
		                      //echo $user_id;
		                       $res1=$this->Usermodel->get_existing_token($user_id);
		                   //  print_r($res1);
		                       if(!count($res1)>0){
								  // echo 'if here';
									$token=$this->encryption1->encode(microtime().$Password);
									if($this->Usermodel->updatetoken($token,@$authenticateUser[0]->user_id,$this->encryption1->getGUID())){
										$re['result']['userid']=@$authenticateUser[0]->user_id;
										$re['result']['token']=$token;
										$re['result']['passcode']=$authenticateUser[0]->passcode;
										$re['result']['is_parental_lock_enable']=$authenticateUser[0]->is_parental_lock_enable;
										if(@$authenticateUser[0]->is_super_user==0){
											$re['result']['UserType']='3';
										}else{$re['result']['UserType']='1';}
										echo json_encode($re);
									}
									else{
										echo APPERROR;
									}
								}
								else{
									//echo 'else here';
										$re['result']['userid']=@$authenticateUser[0]->user_id;
										$re['result']['token']=$res1[0]->token;
										$re['result']['passcode']=$authenticateUser[0]->passcode;
										$re['result']['is_parental_lock_enable']=$authenticateUser[0]->is_parental_lock_enable;
										if(@$authenticateUser[0]->is_super_user==0){
											$re['result']['UserType']='3';
										}else{$re['result']['UserType']='1';}
										echo json_encode($re);
									}
								
		                }
		                else
		                    echo APPERROR;
		            }
		            else{
		            	/*-------------------Check Pin--------------------------------*/
		            	$Password=$this->encryption1->decode($Password);
		            	$res=$this->Usermodel->getResetRequest($UserEmail,$Password);
		            	if(count($res)){
		            		$success=json_decode(APPSUCCESS,true);
            				$success['result']=$res[0];
            				$token=$this->encryption1->encode(microtime().$res[0]->user_id);
            				if($this->Usermodel->updatetoken($token,@$res[0]->user_id,$this->encryption1->getGUID())){
            					$userid= $res[0]->user_id;
            					$success['result']->status="Success";
            					//$success->status="Success";
            					$success['result']->token=$token;
            					$success['result']->userid=@$res[0]->user_id;
            					if(@$res[0]->is_super_user==0){
										$success['result']->UserType='3';
									}else{$success['result']->UserType='1';}
            					$this->db->where('user_id',$userid);
								$this->db->delete(PASSWORDRESET);
            					echo json_encode($success);
            				}
            				else{
            					echo APPERROR;
            				}
	          				
		            	}
		            	else{
		            			echo APPERROR;
		            	}
		            }
	            }
	            else {
	                echo NOTREGISTER;
	            } 
    		}
            //echo $Password;
                      
        }
	}
	public function signup()
	{
		$header = getallheaders();
        $xmlstr = @file_get_contents('php://input');

		if (count($xmlstr) <= 0) {
            echo APPERROR;
        } 
        else 
        {
            $userdata=  json_decode($xmlstr);
            $UserEmail = @$userdata->UserEmail;
            $Password = @$userdata->Password;
            $Password= $this->encryption1->encode($Password);
            $Name = @$userdata->Name;
            $Location = @$userdata->Location;
            $type=@$userdata->UserType;
            $passcode=@$userdata->Passcode;
            $Age=@$userdata->Age;
			$IsParentalLockEnable = @$userdata->IsParentalLockEnable;
           if(strtolower($type)=='publisher'){
           		/*--------------------------Register PUBLISHER---------------------------*/
           		$userExits = $this->Usermodel->getSpecificPublisher($UserEmail);
	            if(count($userExits) > 0) 
	            {
	            	if(!empty($UserEmail) && !empty($Password)){
	            		$data=array(
	            				'user_id'=>$this->encryption1->getGUID(),
		            			'pub_name'=>@$Name,
		            			'email'=>$UserEmail,
		            			'password'=>$Password,
		            			'website'=>@$userdata->Website,
		            			'contact_no'=>@$userdata->Contact,
		            			'logo'=>@$userdata->Logo,
		            			'location'=>@$Location,
		            			'passcode'=>@$passcode,
		            			'date_added'=>date('Y-m-d H:i:s')
		            		);
	            		$res=$this->Usermodel->registerUser($data,PUBLISHER);
	            		if($res!=0){
	            			echo REGISTERSUC;
	            		}
	            		else{
	            			echo APPERROR;
	            		}
	            	}
	            	else{
	            		echo APPERROR;
	            	}  
	           		
	            }
	            else {
	                echo NOTREGISTER;
	            }

           }
           else{
           		/*--------------------------Register Simple USER---------------------------*/
           	    $userExits = $this->Usermodel->getSpecificUser($UserEmail);
	            if(count($userExits) <= 0) 
	            {
	            	if(isset($UserEmail) && isset($Password) && isset($Age)){
	            		$data=array(
	            				'user_id'=>$this->encryption1->getGUID(),
		            			'name'=>@$Name,
		            			'user_email'=>$UserEmail,
		            			'user_password'=>$Password,
		            			'location'=>@$Location,
		            			'passcode'=>@$passcode,
		            			'age'=>@$Age,
								'is_parental_lock_enable'=>@$IsParentalLockEnable,
		            			'date_added'=>date('Y-m-d H:i:s')
		            		);
	            		$res=$this->Usermodel->registerUser($data,USERS);
	            		if($res!=0){
	            			//echo REGISTERSUC;
							 $authenticateUser = $this->Usermodel->authenticateUser($UserEmail,$Password);
								if(count($authenticateUser) > 0){
										$re=json_decode(APPSUCCESS,true);
										$token=$this->encryption1->encode(microtime().$Password);
										if($this->Usermodel->updatetoken($token,@$authenticateUser[0]->user_id,$this->encryption1->getGUID())){
											$re['result']['userid']=@$authenticateUser[0]->user_id;
											$re['result']['token']=$token;
											$re['result']['passcode']=$authenticateUser[0]->passcode;
											$re['result']['password']=$this->encryption1->decode($authenticateUser[0]->user_password);;
											$re['result']['is_parental_lock_enable']=$authenticateUser[0]->is_parental_lock_enable;
											if(@$authenticateUser[0]->is_super_user==0){
												$re['result']['UserType']='3';
											}else{$re['result']['UserType']='1';}
											echo json_encode($re);
										}
										else{
											echo APPERROR;
										}
								}
								else{
									echo APPERROR;
								}
	            		}
	            		else{
	            			echo APPERROR;
	            		}
	            	}
	            	else{
	            		echo APPERROR;
	            	}                
	            }
	            else {
	                echo ALREADYREGISTER;
	            }  
           }
                      
        }
	}

	public function signout(){
		$header = getallheaders();
        $xmlstr = @file_get_contents('php://input');

		if (count($xmlstr) <= 0) {
            	echo APPERROR;
        } 
        else 
        {
        	$userdata=  json_decode($xmlstr);
            $token = @$userdata->AccessToken;
            if($this->Usermodel->removeToken($token)){
	          echo  APPSUCCESS;
       		}
       		else
            	echo APPERROR;

        }
	}

	public function googleuser(){
		$header = getallheaders();
        $xmlstr = @file_get_contents('php://input');

		if (count($xmlstr) <= 0) {
            echo APPERROR;
        } 
        else 
        {
        	$userdata=  json_decode($xmlstr);	
            $GoogleId = @$userdata->GoogleId;
            $Name = @$userdata->Name;
            $UserEmail = @$userdata->UserEmail;
            if(!empty($GoogleId) && !empty($UserEmail)){
            	$where=array('user_email' => $UserEmail);
            	$userid=$this->encryption1->getGUID();
            	$data=array('user_id'=>$userid,'user_email' => $UserEmail,'name'=>@$Name,'google_id'=>$GoogleId);
            	$res=$this->Usermodel-> socialUser($data,$where);
            	if($res != 'no'){
            		$token=$this->encryption1->encode(microtime());
            		if($this->Usermodel->updatetoken($token,$userid,$this->encryption1->getGUID())){
            			$re=json_decode(APPSUCCESS,true);
            			$re['result']['userid']=@$res;
                    	$re['result']['token']=$token;
                    	echo json_encode($re);	
            		}
            		else{
            			echo APPERROR;
            		}

            	}
            	else{
            		echo APPERROR;
            	}
            }
            else{
            	echo APPERROR;
            }

        }

	}
	public function facebookuser(){
		$header = getallheaders();
        $xmlstr = @file_get_contents('php://input');

		if (count($xmlstr) <= 0) {
            echo APPERROR;
        } 
        else 
        {
        	$userdata=  json_decode($xmlstr);
            $FacebookId = $userdata->FacebookId;
            $Name = @$userdata->Name;
            $UserEmail = @$userdata->UserEmail;
            if(!empty($FacebookId) && !empty($UserEmail)){
            	$where=array('user_email' => $UserEmail);
            	$data=array('user_id'=>$this->encryption1->getGUID(),'user_email' => $UserEmail,'name'=>@$Name,'fb_id'=>$FacebookId);
            	$res=$this->Usermodel-> socialUser($data,$where);
            	if($res!=0){
            		$token=$this->encryption1->encode(microtime());
            		$re=json_decode(APPSUCCESS,true);
            		$re['result']['userid']=@$res;
                    $re['result']['token']=$token;
                    echo json_encode($re);
            	}
            	else{
            		echo APPERROR;
            	}
            }
            else{
            	echo APPERROR;
            }

        }

	}

	public function encrypttext(){
		$text=$this->input->get('text1');
		echo '<br>-----------------------Encrepted Text-----------------------------<br>';
		if(!empty($text)){
			$entext=$this->encryption1->encode($text);
			echo $entext;
		}
		
		echo '<br>----------------------------------------------------<br>';
		?>
		<form>
			<span>Text</span>
			<input name="text1" placeholder="enter text">
			<input type="submit" value="genrate">
		</form>
		<?php
	}
	
	public function decrypttext(){
		$text=$this->input->get('text1');
		echo '<br>-----------------------Encrepted Text-----------------------------<br>';
		if(!empty($text)){
			$entext=$this->encryption1->decode($text);
			echo $entext;
		}
		
		echo '<br>----------------------------------------------------<br>';
		?>
		<form>
			<span>Text</span>
			<input name="text1" placeholder="enter text">
			<input type="submit" value="genrate">
		</form>
		<?php
	}

	public function updatelock(){
		$header = getallheaders();
        $xmlstr = @file_get_contents('php://input');
		if (count($xmlstr) <= 0) {
            echo APPERROR;
        } 
        else 
        {
	    $userdata=  json_decode($xmlstr);
            $token = @$userdata->AccessToken;
            $lock=@$userdata->ParentalLock;
           	if(strlen($token)>10 && isset($lock)){
           		$res=$this->Usermodel->getIdByToken($token);
           	
           		if(isset($res[0]->userid) && !empty($res[0]->userid)){
           			$userid=$res[0]->userid;
           			if($this->Usermodel->updatelock($userid,$lock)){
           				echo APPSUCCESS;
           			}
           			else{
           				echo APPERROR;
           			}
           		}
           		else{
           			echo APPERROR;
           		}
           	}
           	else{
           		echo APPERROR;
           	}
        }
	}
	public function updatelockwithpasscode(){
		$header = getallheaders();
        $xmlstr = @file_get_contents('php://input');
		if (count($xmlstr) <= 0) {
            echo APPERROR;
        } 
        else 
        {
    		$userdata=  json_decode($xmlstr);
            $token = @$userdata->AccessToken;
            $passcode = @$userdata->Passcode;
            $lock=@$userdata->ParentalLock;
           	if(strlen($token)>10 && isset($lock) && isset($passcode)){
           		$res=$this->Usermodel->getIdByToken($token);
           		if(isset($res[0]->userid) && !empty($res[0]->userid)){
           			$userid=$res[0]->userid;
           			if($this->Usermodel->updatelockwithpasscode($userid,$lock,$passcode)){
           				echo APPSUCCESS;
           			}
           			else{
           				echo APPERROR;
           			}
           		}
           		else{
           			echo APPERROR;
           		}
           	}
           	else{
           		echo APPERROR;
           	}
        }
	}

	public function changePassword1(){
		$header = getallheaders();
        $xmlstr = @file_get_contents('php://input');
		if (count($xmlstr) <= 0) {
            echo APPERROR;
        } 
        else 
        {
    		$userdata=  json_decode($xmlstr);
            $token = @$userdata->AccessToken;
            $OldPassword = @$userdata->OldPassword;
            $NewPassword = @$userdata->NewPassword;
            $lock=@$userdata->ParentalLock;
           	if(strlen($token)>10 && isset($OldPassword) && isset($NewPassword)){
           		$res=$this->Usermodel->getIdByToken($token);
           		if(isset($res[0]->userid) && !empty($res[0]->userid)){
           			$userid=$res[0]->userid;
           			if($this->Usermodel->updatepassword($userid,$this->encryption1->encode($OldPassword),$this->encryption1->encode($NewPassword))){
           				echo APPSUCCESS;
           			}
           			else{
           				echo APPERROR;
           			}
           		}
           		else{
           			echo APPERROR;
           		}
           	}
           	else{
           		echo APPERROR;
           	}
        }
	}

	
	function sendMail1($email,$code)
	{
	   
	    $this->load->library('email');
		$this->email->from('infor@cornea.com', 'Cornea');
		$this->email->to($email);
		$this->email->subject('Password Reset Request');
		$this->email->message('Use this '.$code.' pin to reset Password');//Load a view into email body
		if($this->email->send()){
			return true;
		}
		else{
			return false;
		}

	}
	public function resetPassword(){
		$header = getallheaders();
        $xmlstr = @file_get_contents('php://input');
		if (count($xmlstr) <= 0) {
            echo APPERROR;
        } 
        else 
        {
    		$userdata=  json_decode($xmlstr);
            $UserEmail = @$userdata->UserEmail;
            $userExits = $this->Usermodel->getSpecificUser($UserEmail);
            if(count($userExits) >0){
				$userid=$userExits[0]->user_id;
				$code=rand (1000, 9999 );
            	if($this->sendMail1($UserEmail,$code) && $this->Usermodel->resetrequest($userid,$code,$this->encryption1->getGUID())){
            		echo APPSUCCESS;
            	}
            	else{
            		echo APPERROR;
            	}
            }
            else{
            	echo NOTREGISTER;
            }
           
        }
	}

	function sendMail()
	{
	   
	    $this->load->library('email');
		$this->email->from('infor@cornea.com', 'Cornea');
		$this->email->to('lanetteam.divyesh@gmail.com');
		$this->email->subject('Password Reset Request');
		$this->email->message('Use this  pin to reset Password');//Load a view into email body
		if($this->email->send()){
			return true;
		}
		else{
			return false;
		}

	}

	public function checkpin(){
		$header = getallheaders();
        $xmlstr = @file_get_contents('php://input');
		if (count($xmlstr) <= 0) {
            echo APPERROR;
        } 
        else 
        {
    		$userdata=  json_decode($xmlstr);
            $ResetCode = @$userdata->ResetCode;
            $UserEmail = @$userdata->UserEmail;
            if($ResetCode>0){
				$res=$this->Usermodel->getResetRequest($UserEmail,$ResetCode);
				if(count($res)>0){
					$success=json_decode(APPSUCCESS,true);
            		$success['result']['Data']=$res[0];
	          		echo json_encode($success);
				}
				else{
					echo APPERROR;
				}
            }
            else{
            	echo APPERROR;
            }
           
        }
	}

	function changePassword(){
		$header = getallheaders();
        $xmlstr = @file_get_contents('php://input');
		if (count($xmlstr) <= 0) {
            echo APPERROR;
        } 
        else 
        {
    		$userdata=  json_decode($xmlstr);
            $AccessToken = @$userdata->AccessToken;
         	$Password = @$userdata->Password;
         	$res=$this->Usermodel->getIdByToken($AccessToken);
            if(isset($res[0]->userid) && !empty($res[0]->userid)){
				$res=$this->Usermodel->ChangePassword($res[0]->userid,$this->encryption1->encode($Password));
				if(count($res)>0){
					echo APPSUCCESS;
				}
				else{
					echo APPERROR;
				}
            }
            else{
            	echo APPERROR;
            }
           
        }
	}
	

}
