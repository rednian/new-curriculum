<?php
/**
 * Created by PhpStorm.
 * User: RedZ
 * Date: 11/23/2018
 * Time: 4:24 PM
 */
use \Illuminate\Database\Eloquent\Model as Eloquent;

class Categories extends Eloquent
{

    protected $table = "category"; // table name
    protected $primaryKey = 'c_id';
    protected $fillable = ['category_name'];

    public function subject()
    {
        return $this->belongsToMany(Subjects::class,'category_subject','c_id','subj_id')->withPivot('school_year','semester');
    }

}