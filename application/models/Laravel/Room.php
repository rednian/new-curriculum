<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 1/10/2019
 * Time: 11:38 AM
 */
use \Illuminate\Database\Eloquent\Model as Eloquent;

class Room extends Eloquent
{
    protected $table = "room_list"; // table name
    protected $primaryKey = 'rl_id';
    protected $fillable = ['room_code','room_name', 'capacity', 'location', 'type', 'desc'];

}