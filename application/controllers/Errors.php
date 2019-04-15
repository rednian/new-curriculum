<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 10/18/2018
 * Time: 9:19 AM
 */
class Errors extends MY_Controller{

    public function __construct()
    {
        parent::__construct();
    }

    public function error_404()
    {
        $data['title'] = 'Page not found';
        $this->load->view('errors/error_404',$data);
    }

    public function error_403()
    {
        $data['title'] = 'Access Forbidden';
        $this->load->view('errors/error_403',$data);
    }
}