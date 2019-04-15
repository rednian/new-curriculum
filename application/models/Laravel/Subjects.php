<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 11/22/2018
 * Time: 4:45 PM
 */
use \Illuminate\Database\Eloquent\Model as Eloquent;

class Subjects extends Eloquent {

    protected $table = "subject"; // table name
    protected $primaryKey = 'subj_id';
    protected $fillable = [
        'subj_code',
        'subj_name',
        'subj_desc',
        'lec_hour',
        'lec_unit',
        'lab_hour',
        'lab_unit',
        'subj_type',
        'split',
        'sc_id'
        ];



    public function category()
    {
        return $this->belongsToMany(Categories::class,'category_subject','subj_id','c_id')->withPivot('school_year','semester');
    }

    public function yearSem()
    {
        return $this->belongsToMany(YearSem::class,'cur_subject','subj_id','ys_id');
    }

}