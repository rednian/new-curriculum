<?php
	defined('BASEPATH') OR exit('No direct script access allowed');
	/**
	* 
	*/
	class Auth extends MY_Model{
		private static $token;
		public function __construct(){
			parent::__construct();
			$this->load->model("User");
		}
		public function checkUser($username="",$password=""){
			
			$user = new User;
			// CHECK FROM DATABASE
			$searchData = array("username"=>$username, "user_status"=>"active");
			$search = $user->search($searchData);

			if(!empty($search)){
				foreach ($search as $key => $value) {
					if(password_verify($password, $value->password)){
						$sess_array = array( 'id' => $value->user_id, 'name' => $value->fullName('f l') );
						// SET SESSION
						$this->setUserSession($sess_array);
						// REDIRECT 
						$this->redirectUser();
					}
					else{
						return false;
					}
				}
			}
			else{
				return false;
			}
		}

		private function setUserSession($sess_array){
            $this->session->set_userdata('CURRICULUM_logged', $sess_array);
		}

		private function redirectUser(){
			if ( $this->session->userdata('CURRICULUM_logged')) {
				redirect('gen_info' , 'refresh');
	      	}
		}
		
		public function whoseLogged(){
			if ( $this->session->userdata('CURRICULUM_logged')) {
				$user = new User;
				$login_session =  $this->session->userdata('CURRICULUM_logged');
				return $user->get_user(array("user_id"=>$login_session['id']));
			}
			return false;	
		}

		public function logout(){
            $this->session->unset_userdata('CURRICULUM_logged');
            $this->session->sess_destroy();
//			delete_session("CURRICULUM_logged");
//			delete_session("session_token");
//			destroy_session();
		   	redirect("login", "refresh");
		}

		// TOKEN AUTHENTICATION
		public function generateToken(){
		    set_session("session_token", $this->security->get_csrf_hash());
		   	return $this->security->get_csrf_hash();
		}

		public function validateToken($token="")
        {
			// Is this a post request?
            if ($this->input->method() == 'post')
            {
                // Is the token field set and valid?
                return $isValid = (empty($token) || ($token != get_session("session_token"))) ? false : true;
			}
			else{
			  	return false;
			}
		}
	}
?>