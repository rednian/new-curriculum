<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 11/22/2018
 * Time: 5:18 PM
 */
use \Illuminate\Database\Eloquent\Model as Eloquent;

class Departments extends Eloquent {

    protected $table = "department"; // table name
    protected $primaryKey = 'dep_id';
//    public $timestamps = false;
    protected $fillable = ['dep_name','dep_desc'];

    public function user()
    {
       return $this->hasMany(Users::class, 'dep_id');
    }


}