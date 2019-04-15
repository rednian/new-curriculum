<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller
{

    public function __construct()
{
    parent::__construct();
        $this->load->model("Auth");
}

    public function index()
    {
        if (!$this->session->CURRICULUM_logged) {
            $this->load->view('login');
        } else {
            redirect('gen_info', 'refresh');
        }
    }

    public function verifyLogin()
    {
        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');

        if ($this->form_validation->run() == false) {
            $this->load->view('login');
        } else {
            $auth = new Auth;
            $username = $this->security->xss_clean(escape_str($this->input->post('username')));
            $password = $this->security->xss_clean(escape_str($this->input->post('password')));
            //check user
            if ($auth->checkUser($username, $password) == false) {
                $sess_login = array(
                    "result" => false
                );
                $this->session->set_userdata('CURRICULUM_logged');
                $this->load->view('login');
            }
        }


    }

    public function logout()
    {
        $auth = new Auth;
        $auth->logout();
    }
}
