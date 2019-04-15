<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 11/22/2018
 * Time: 4:17 PM
 */
use \Illuminate\Database\Eloquent\Model as Eloquent;

class Users extends Eloquent {

    protected $table = "user"; // table name
    protected $primaryKey = 'user_id';
//    public $timestamps = false;
    protected $fillable = ['user_fname','user_lname','user_mname','username','password','user_status','user_image','user_department','user_position','dep_id'];

    public function department()
    {
       return $this->belongsTo(Departments::class ,'dep_id');
    }


}