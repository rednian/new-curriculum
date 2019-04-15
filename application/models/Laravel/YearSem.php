<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 11/23/2018
 * Time: 10:54 AM
 */
use \Illuminate\Database\Eloquent\Model as Eloquent;

class YearSem extends Eloquent
{

    protected $table = "year_sem"; // table name
    protected $primaryKey = 'ys_id';
    public $timestamps = false;
    protected $fillable = ['cur_id', 'year', 'semister'];

    public function subject()
    {
        return $this->belongsToMany(Subjects::class, 'cur_subject','ys_id','subj_id');
    }

}