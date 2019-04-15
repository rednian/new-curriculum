<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 1/16/2019
 * Time: 2:44 PM
 */
use \Illuminate\Database\Eloquent\Model as Eloquent;

class Instructors extends Eloquent
{

    protected $table = "employees"; // table name
    protected $primaryKey = 'employee_id';
    protected $connection = 'dtrps';
//    public $timestamps = false;
//    protected $fillable = ['dep_name', 'dep_desc'];
}