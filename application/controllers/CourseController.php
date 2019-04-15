<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 10/2/2018
 * Time: 2:39 PM
 */
class CourseController extends MY_Controller{

    public function __construct()
    {
        parent::__construct();
        if (!$this->input->is_ajax_request()) {
            exit('No direct script access allowed');
        }

        $this->$this->load->model('room_list');
    }

   public function roomId()
   {
    $room = new Room_list();
   }
}