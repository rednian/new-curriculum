<?php
defined('BASEPATH') OR exit('No direct script access allowed');

  class MY_Controller extends CI_Controller {

    public $userInfo = array();

    public function __construct() {
      parent::__construct();
      $this->load->model('Auth');
      $auth = new Auth;
      if (!get_session('CURRICULUM_logged')) {
        redirect('login' , 'refresh');
      }
      else{
        foreach ($auth->whoseLogged() as $user) {
          $this->userInfo = $user;

        }
      }
      }


      protected function userType()
      {
          $this->load->model('user');

          $users = $this->user->getById();

          if (!empty($users)){
              foreach ($users as $user){
                    return strtolower($user->user_type);
                    break;
              }
          }
      }

    

  
  }
