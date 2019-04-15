<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 11/6/2018
 * Time: 10:27 AM
 */

class Periodic extends MY_Model
{
    const DB_TABLE ='periodic';
    const DB_TABLE_PK = 'periodic_id';

    public $periodic_id;
    public $period;
    public $created_at;
    public $updated_at;
}