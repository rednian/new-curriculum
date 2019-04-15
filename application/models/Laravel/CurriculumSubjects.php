<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 11/23/2018
 * Time: 8:18 AM
 */
use \Illuminate\Database\Eloquent\Model as Eloquent;

class CurriculumSubjects extends Eloquent
{
    protected $table = "cur_subject"; // table name
    protected $primaryKey = 'sc_id';
    protected $fillable = ['subj_id','ys_id'];
    public $timestamps = false;

//    public function subject()
//    {
//        return $this->belongsTo(Subjects::class, 'subj_id');
//    }
//
//    public function yearSem()
//    {
//        return $this->belongsTo(YearSem::class, 'ys_id');
//    }

}