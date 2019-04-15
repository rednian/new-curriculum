<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends MY_Controller {

  public function __construct()
  {
    parent::__construct();
  }


  public function index()
  {
    $this->load->view('includes/header');
    $this->load->view('includes/menu');
    $this->load->view('test');
    $this->load->view('includes/footer');
  }



}
