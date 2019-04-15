<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Curriculum extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Curr_codelist');
        $this->load->model('Year_sem');
        $this->load->model('Subject');
        $this->load->model('Cur_subject');
        $this->load->model('Pre_requisite');

        if ( $this->userType() != 'dean' && $this->userType() != 'admin' ){
            $data['title'] = 'Access Forbidden';
            $this->load->view('errors/error_403',$data);
            exit();
        }
    }

    public function index()
    {
        $data['title'] = 'Curriculum';
        $this->load->view('includes/header', $data);
        $this->load->view('includes/menu');
        $this->load->view('curriculum/css');
        $this->load->view('curriculum/index');
        $this->load->view('includes/footer');
        $this->load->view('includes/js');
        $this->load->view('curriculum/js');
    }

    public function getCurriculumByUser()
    {
        $curr = new Curr_codelist();
        $query = $curr->db->query("SELECT * from program_list where dep_id = {$this->userInfo->dep_id}");
        $programs = $query->result();
        $data = [];
        $display = '';

        if (!empty($programs)){
            foreach ($programs as $program){
                $display = '<div class="curr-list-container">';
                $display .= '    <div class="list-head clearfix">';
                $display .= '        <div class="list-counter text-center pull-left">';
                $display .= '            <h3 class="m-t-0 m-b-0">' . $this->count_revices($program->pl_id) . '</h3>';
                $display .= '            <span class="text-white">revises</span>';
                $display .= '        </div>';
                $display .= '    <div class="pull-left p-l-10">';
                $display .= '        <h5 class="m-t-0 m-b-0">' . strtoupper($program->prog_name) . '</h5>';
                $display .= '            <h6 class="m-t-0 m-b-0">' . ucwords($program->major) . '</h6>';
                $display .= '            <small class="m-t-0 m-b-0">' . ucwords($program->dep_name) . '</small>';
                $display .= '     </div>';
                $display .= '   </div>';
                $display .= '   <div class="list-content p-t-10" style="min-height:100px;max-height:300px;overflow:auto;">';
                $display .= '     <ul id="listProg_' . $program->pl_id . '" class="curr-list"></ul>';
//                $display .= '     <button onclick="showMoreCurr(' . $program->pl_id . ')" class="btn btn-xs btn-default">Show more</button>';
                $display .= '  </div>';
                $display .= '</div>';

                $data[] = [
                    'program'=> $display,
                    'program_id'=>$program->pl_id
                ];
            }
        }
        echo json_encode(['data'=>$data]);
    }

    public function curriculumList()
    {
        $curr = new Curr_codelist;
        $query = $curr->db->query("SELECT * from program_list where dep_id = {$this->userInfo->dep_id}");
        $programs = $query->result();
        $display = "";
        $array = array();

        foreach ($programs as $key => $value) {

            $display .= '<div class="curr-list-container">';
            $display .= '    <div class="list-head clearfix">';
            $display .= '        <div class="list-counter text-center pull-left">';
            $display .= '            <h3 class="m-t-0 m-b-0">' . $this->count_revices($value->pl_id) . '</h3>';
            $display .= '            <span class="text-white">revises</span>';
            $display .= '        </div>';
            $display .= '    <div class="pull-left p-l-10">';
            $display .= '        <h5 class="m-t-0 m-b-0">' . strtoupper($value->prog_name) . '</h5>';
            $display .= '            <h6 class="m-t-0 m-b-0">' . $value->major . '</h6>';
            $display .= '            <small class="m-t-0 m-b-0">' . $value->dep_name . '</small>';
            $display .= '     </div>';
            $display .= '   </div>';
            $display .= '   <div class="list-content p-t-10" style="min-height:100px;max-height:300px;overflow:auto;">';
            $display .= '     <ul id="listProg_' . $value->pl_id . '" class="curr-list"></ul>';
            $display .= '     <button onclick="showMoreCurr(' . $value->pl_id . ')" class="btn btn-xs btn-default">Show more</button>';
            $display .= '  </div>';
            $display .= '</div>';

            $array[$value->pl_id] = $display;
            $display = "";
        }
        echo json_encode($array);
    }

    public function showCurrPerProgram()
    {
        $curr = new Curr_codelist;
        $pl_id = $this->input->get("pl_id");
        $query = $curr->db->query("SELECT * FROM curr_codelist WHERE pl_id = {$pl_id} ORDER BY eff_sy DESC ");
        $curriculum = $query->result();
        echo json_encode($curriculum);
    }

    public function showMoreCurr()
    {
        $curr = new Curr_codelist;
        $limit = $this->input->get("limit");
        $pl_id = $this->input->get("pl_id");

        $query = $curr->db->query("SELECT * FROM curr_codelist WHERE pl_id = {$pl_id} ORDER BY eff_sy DESC LIMIT {$limit},5");
        $curriculum = $query->result();
        echo json_encode($curriculum);
    }

    private function count_revices($pl_id)
    {
        $curr = new Curr_codelist;
        $currList = $curr->search(array('pl_id' => $pl_id));
        return $result = !empty($currList) ? count($currList) : false;
    }

    public function program_list()
    {
        $curr = new Curr_codelist;
        $query = $curr->db->query("SELECT * from program_list where dep_id = {$this->userInfo->dep_id}");
        $programs = $query->result();
        echo json_encode($programs);
    }

    public function getProgramMajor()
    {
        $curr = new Curr_codelist;
        $pl_id = $this->input->get('pl_id');
        $query = $curr->db->query("SELECT * from program_list where pl_id = {$pl_id}");
        $programs = $query->result();
        foreach ($programs as $key => $value) {
            echo json_encode(array("major" => $value->major));
        }
    }

    public function setNewCUrriculum()
    {
        $curr = new Curr_codelist;
        $data = $this->input->post();

        foreach ($data as $key => $value) {
            $data['status'] = "active";
        }
        $result = $curr->add($data);
        echo json_encode(array("result" => $result));
    }

    public function curriculumPreview()
    {
        $curr = new Curr_codelist;
        $cur_id = $this->input->get('cur_id');

        $currInfo = $curr->search(array("cur_id" => $cur_id));
        $pl_id = $currInfo[$cur_id]->pl_id;

        $currStatus = $currInfo[$cur_id]->status;

        if ($currStatus == "active") {
            $currStatus = "Deactivate";
        } elseif ($currStatus == "inactive") {
            $currStatus = "Activate";
        }

        $query = $curr->db->query("SELECT * from program_list where pl_id = {$pl_id}");
        $result = $query->result();

        $program = "";
        $major = "";
        $display = "";

        foreach ($result as $key => $value) {
            $program = $value->prog_name;
            $major = $value->major;
        }
        $user = $this->userInfo;

        if($user->dep_id != 4) {
            $display .= '<form id="formSaveRevisionCurriculum" action=" ' . base_url('curriculum/save_revision') . '" method="post">';
            $display .= '<div class="curr-container">';
            $display .= '<input type="hidden" name="cur_id" value="' . $cur_id . '">';
            $display .= '<div class="curr-preview-header">';
            $display .= '<center>';
            $display .= '<h4 class="m-b-0"> ' . strtoupper($program) . '</h4>';
            $display .= '<h6 class="m-t-0 m-b-0">' . ucwords($major) . '</h6>';
            $display .= '<small>';
            $display .= 'Revised Curriculum Effectivity: Semester';
            $display .= '<select class="preview-select-sem">';
            $display .= '<option selected class="hide">' . $currInfo[$cur_id]->eff_sem . '</option>';
            $display .= '<option>1st Semester</option>';
            $display .= '<option>2nd Semester</option>';
            $display .= '</select>';
            $display .= 'School Year';
            $display .= '<select class="preview-select-sy">';
            $display .= '<option selected class="hide">' . $currInfo[$cur_id]->eff_sy . '</option>';

            for ($x = date('Y'); $x >= 2000; $x--) {
                $display .= "<option>" . $x . "-" . ($x + 1) . "</option>";
            }
            $display .= "</select></small>
                            </center>
                        </div>
                        <div id=\"existing_ys_container\"></div>
                        <div id='year_sem_container'></div>
                        <button onclick=\"addYearAndSemister()\" type=\"button\" class=\"btn btn-xs btn-primary m-t-5\">Next Year / Semester</button>
                        <button onclick=\"removePreviousYS()\" type='button' class='btn btn-xs btn-danger m-t-5'>Remove Previous Year / Semester</button>
                    </div>
                    <div class=\"curr-preview-footer\">
                        <button type='submit' class=\"btn btn-success btn-sm\">Save</button>
                        <button onclick=\"cancelSave()\" type='button' class=\"btn btn-danger btn-sm\">Cancel</button>
                        <button id=\"btnSetActiveInactiveCurriculum\" onclick=\"setActiveInactiveCurriculum()\" type='button' class=\"btn btn-inverse btn-sm pull-right m-l-5\">" . ucfirst($currStatus) . " Curriculum</button>
                    </div></form>";
        }
        else{

        }
        echo $display;

    }

    public function addYearSem()
    {
        $ys = new Year_sem;
        $data = $this->input->post();
        $result = $ys->add($data);
    }

    public function getYearSem1()
    {
        $cur_id = $this->input->get('cur_id');

        $yearSem = YearSem::where('cur_id',$cur_id)->orderBy('ys_id')->get();

        $array = $display = null;
        $totalUnit = 0;
        $user = $this->userInfo;



        foreach ($yearSem as $semester){
            $curriculumSubject = CurriculumSubjects::where('ys_id',$semester->ys_id)->get();
            if(!empty($curriculumSubject)){
                $display .= '<div id="ys_'.str_replace(' ', '', $semester->year . "-" . $semester->semister).'">';
                $display .= '    <center>';
                $display .= '      <input type="hidden" name="txt_ys[]" value="'.$semester->year . " - " . $semester->semister.'">';
                $display .= '      <h6 class="m-t-30 m-b-0">'.$semester->year . " - " . $semester->semister.'</h6>';
                $display .= '<table class="table table-curr">';
                $display .= '    <thead>';
                $display .= '        <tr>';
                $display .= '        <td>Course</td>';
                $display .= '        <td>Title</td>';
                $display .= '        <td>Lec</td>';
                $display .= '        <td>Lab</td>';
                $display .= '        <td>Unit</td>';
                $display .= '        <td class="col-md-1">Pre-requisites</td>';
                $display .= '        <td></td>';
                $display .= '        </tr>';
                $display .= '    </thead>';
                $display .= '    <body>';

                $categoriesSubject = Categories::with(['subject.yearsem'=>function($query) use ($semester){
                    $query->where('year_sem.ys_id',$semester->ys_id);
                }])->get();


                //if senior high department then display the senior high view
                if ($user->dep_id == 4){
                    foreach ($categoriesSubject as $category){
                        $display .= '<tr class="info">';
                        $display .= '    <th colspan="7" class="text-center">'.ucwords($category->category_name).'</th>';
                        $display .= '</tr>';
                        foreach ($category->subject as $subject){
                            $display .='<tr id="'.$subject->subj_code.'">';
                        $display .='    <td>'.$subject->subj_code.'</td>';
                        $display .='    <td>';
                        $display .= '<select class="preview-select-title" >';

                            foreach ($subject->yearsem as $yearSem){
                                $name = str_replace(' ', '', $yearSem->year .''. $yearSem->semister);
                                $display .='<select onchange="setNameSelect2($(this).closest(\'tr\').find(\'td select.js-example-basic-multiple\').attr(\'name\', \'subj_\'+$(this).val()+\'[]\'))" class="preview-select-title" name="ys_'.$name.'_sub_id[]">';
                                $display .='  <option class="hide" selected value="'.$subject->subj_id.'" >'.$subject->subj_name . '</option>';
                            }

                        $subjects = Subjects::orderBy('subj_name')->orderBy('subj_code')->get();
                        foreach ($subjects as $subjectList){
                            if($subject->subj_id == $subjectList->subj_id){
                                $display .= '<option selected value="'.$subjectList->subj_id.'">'.$subjectList->subj_code.' - '. $subjectList->subj_name.'</option>';
                            }
                            else{
                                $display .= '<option value="'.$subjectList->subj_id.'">'.$subjectList->subj_code.' - '. $subjectList->subj_name.'</option>';
                            }
                        }

                        $display .='        </select>';
                        $display .='    </td>';
                        $display .='    <td>'.$subject->lec_unit.'</td>';
                        $display .='    <td>'.$subject->lab_unit.'</td>';
                        $display .='    <td>'.($subject->lec_unit + $subject->lab_unit).'</td>  ';
                        $display .='    <td>';
                        $display .='        <select name="subj_'.$subject->subj_id.'[]" multiple="multiple" class="form-control js-example-basic-multiple" style="outline:none;border:none" placeholder="Select Pre-requisites">';

                        $preRequisites = PreRequisite::with('subject')->where('cs_id',$subject->cs_id)->get();
                        if (!empty($preRequisites)){
                            foreach ($preRequisites as  $preRequisite){
                                $display .= '<option selected value="' . $preRequisite->subject->subj_id . '">'. $preRequisite->subject->subj_code . '</option>';
                            }
                        }

                        foreach ($subjects as $subjectList){
                            $display .= '<option value="'.$subjectList->subj_id.'">'.$subjectList->subj_code.' - '. $subjectList->subj_name.'</option>';
                        }

                        $display .='        </select>';
                        $display .='    </td>';
                        $display .='    <td>';
                            foreach ($subject->yearsem as $yearSem){
                                $display .='        <a onclick="remove_subject($(this).attr(\'con\'),$(this).attr(\'tr\'))" con="ys_'.str_replace(' ','', $yearSem->year . "-" . $yearSem->semister).'" tr="'. $subject->subj_code .'" title="remove" href="javascript:;">';
                                $display .='        <i class="fa fa-times"></i>';
                                $display .='        </a>';
                            }

                        $display .='    </td>';
                        $display .='</tr>';
                        }

                        $display .= '        <tr>';
                        $display .= '            <td colspan="5">';
                        $display .= '                <button onclick="addShSubject($(this).attr(\'con\'))" con="ys_' . str_replace(' ', '', $semester->year . "-" . $semester->semister) . '" class="btn btn-xs btn-default btn-add-subject" type="button">';
                        $display .='                    Add Subject';
                        $display .= '                </button>';
                        $display .= '            </td>';
                        $display .= '        </tr>';
                    }
                    $display .= '    </body>';
                    $display .= '    <tfooter>';
                    $display .= '        <tr>';
                    $display .= '            <td colspan="5">';
                    $display .= '            <td colspan="2">    Total Unit:'. $totalUnit .'</td>';
                    $display .= '        </tr>';
                    $display .= '    </tfooter>';
                    $display .= '</table>';
                    $display .= '    </center>';
                    $display .= '</div>';
                }
                else{

                    $subjects = Subjects::with(['yearsem'=>function($query) use ($semester){
                        $query->where('year_sem.ys_id', $semester->ys_id);
                    }])->get()->chunk(10);
                    var_dump($subjects);
                    foreach ($subjects as $subject){
//                        dd($subject);
                        $display .='<tr id="'.$subject->subj_code.'">';
//                        $display .='    <td>'.$subject->subj_code.'</td>';
//                        $display .='    <td>';
////                        foreach ($subject->yearsem as $semester){
////                            $name = str_replace(' ', '', $semester->year .''. $semester->semister);
////                            $display .='        <select onchange="setNameSelect2($(this).closest(\'tr\').find(\'td select.js-example-basic-multiple\').attr(\'name\', \'subj_\'+$(this).val()+\'[]\'))" class="preview-select-title" name="ys_'.$name.'_sub_id[]">';
////                            $display .='            <option class="hide" selected value="'.$subject->subj_id.'" >'.$subject->subj_name . '</option>';
////                        }
////
////
//                        $subjects = Subjects::orderBy('subj_name')->orderBy('subj_code')->get();
//                        foreach ($subjects as $subject){
//                            $display .= '<option value="'.$subject->subj_id.'">'.$subject->subj_code.' - '. $subject->subj_name.'</option>';
//                        }
//
//                        $display .='        </select>';
//                        $display .='    </td>';
//                        $display .='    <td>'.$subject->lec_unit.'</td>';
//                        $display .='    <td>'.$subject->lab_unit.'</td>';
//                        $display .='    <td>'.($subject->lec_unit + $subject->lab_unit).'</td>  ';
//                        $display .='    <td>';
//                        $display .='        <select name="subj_'.$subject->subj_id.'[]" multiple="multiple" class="form-control js-example-basic-multiple" style="outline:none;border:none" placeholder="Select Pre-requisites">';
//
//                        $preRequisites = PreRequisite::with('subject')->where('cs_id',$subject->cs_id)->get();
//                        if (!empty($preRequisites)){
//                            foreach ($preRequisites as  $preRequisite){
//                                $display .= '<option selected value="' . $preRequisite->subject->subj_id . '">'. $preRequisite->subject->subj_code . '</option>';
//                            }
//                        }
//
//                        foreach ($subjects as $subject){
//                            $display .= '<option value="'.$subject->subj_id.'">'.$subject->subj_code.' - '. $subject->subj_name.'</option>';
//                        }
//
//                        $display .='        </select>';
//                        $display .='    </td>';
//                        $display .='    <td>';
//                        $display .='        <a onclick="remove_subject($(this).attr(\'con\'),$(this).attr(\'tr\'))" con="ys_'.str_replace(' ','', $curriculum->yearSem->year . "-" . $curriculum->yearSem->semister).'" tr="'. $curriculum->subj_code .'" title="remove" href="javascript:;">';
//                        $display .='        <i class="fa fa-times"></i>';
//                        $display .='        </a>';
//                        $display .='    </td>';
                        $display .='</tr>';
                    }
                    $display .= '    </body>';
                    $display .= '    <tfooter>';
                    $display .= '        <tr>';
                    $display .= '            <td colspan="5">';
                    $display .= '                <button onclick="add_subject($(this).attr(\'con\'))" con="ys_' . str_replace(' ', '', $semester->year . "-" . $semester->semister) . '" class="btn btn-xs btn-default btn-add-subject" type="button">';
                    $display .='                    Add Subject';
                    $display .= '                </button>';
                    $display .= '            </td>';
                    $display .= '            <td colspan="2">    Total Unit:'. $totalUnit .'</td>';
                    $display .= '        </tr>';
                    $display .= '    </tfooter>';
                    $display .= '</table>';
                    $display .= '    </center>';
                    $display .= '</div>';
                }




                $array[] =$display;
                $totalUnit = 0;
                echo json_encode($array);
            }

        }
    }



    public function getYearSem()
    {
        $ys = new Year_sem;
        $curr_subject = new Cur_subject;
        $cur_id = $this->input->get('cur_id');
        $ys->db->order_by("ys_id");
        $list = $ys->search(array("cur_id" => $cur_id));

        $array = array();
        $display = "";
        $totalUnit = 0;

        foreach ($list as $key => $value) {
            // CHECK IF YS NOT EMPTY
            $countSub = $curr_subject->search(array("ys_id" => $value->ys_id));
            if (!empty($countSub)) {
                $display .= "<div id='ys_" . str_replace(' ', '', $value->year . "-" . $value->semister) . "' class=\"curr-preview-body\">
                        <center>
                        	<input type='hidden' name='txt_ys[]' value='" . $value->year . " - " . $value->semister . "'>
                            <h6 class=\"m-t-30 m-b-0\">" . $value->year . " - " . $value->semister . "</h6>
                            <table class=\"table table-curr\">
                                <thead>
                                    <tr>
                                        <td>Course</td>
                                        <td>Title</td>
                                        <td>Lec</td>
                                        <td>Lab</td>
                                        <td>Unit</td>
                                        <td class='col-md-1'>Pre-requisites</td>
                                        <td></td>
                                    </tr>
                                </thead>
                                <tbody>";
                $query = $ys->db->query("SELECT * FROM subject,cur_subject WHERE cur_subject.ys_id = {$value->ys_id} AND cur_subject.subj_id = subject.subj_id ");
                $result = $query->result();
                foreach ($result as $key1 => $value1) {
                    $display .= "<tr id='" . $value1->subj_code . "'>
									                <td>" . $value1->subj_code . "</td>
									                <td>
									                    <select onchange=\"setNameSelect2($(this).closest('tr').find('td select.js-example-basic-multiple').attr('name', 'subj_'+$(this).val()+'[]'))\" name='ys_" . str_replace(' ',
                            '', $value->year . "-" . $value->semister) . "_sub_id[]' required class=\"preview-select-title\">
									                    	<option class='hide' selected value='" . $value1->subj_id . "'>" .$value1->subj_name . "</option>";
                    $subject = new Subject;
                    $query = $subject->db->query("SELECT * FROM subject ORDER BY subject.subj_name ASC");
                    $sub = $query->result();
                    foreach ($sub as $key2 => $value2) {
                        $display .= "<option value='" . $value2->subj_id . "'>" .$value2->subj_code.' - '. $value2->subj_name . "</option>";
                    }
                    $display .= "</select>
									                </td>
									                <td>" . $value1->lec_unit . "</td>
									                <td>" . $value1->lab_unit . "</td>
									                <td>" . ($value1->lec_unit + $value1->lab_unit) . "</td>
									                <td>
									                	<select name='subj_" . $value1->subj_id . "[]' placeholder='Select Pre-requisites' style='outline:none;border:none' class=\"form-control js-example-basic-multiple\" multiple=\"multiple\">";
                    $subject = new Subject;
                    $query = $subject->db->query("SELECT * FROM subject ORDER BY subject.subj_name ASC");
                    $sub = $query->result();
                    // GET PREREQUISITES //
                    $prq = new Pre_requisite;
                    $prq->toJoin = array("Subject" => "Pre_requisite");
                    $prqSub = $prq->search(array("cs_id" => $value1->cs_id));
                    if (!empty($prqSub)) {
                        foreach ($prqSub as $ky => $val) {
                            $display .= "<option selected value='" . $val->subj_id . "'>" . $val->subj_code . "</option>";
                        }
                    }
                    // END GET PREREQUISITES //
                    foreach ($sub as $key2 => $value2) {
                        $display .= "<option value='" . $value2->subj_id . "'>" . $value2->subj_code . "</option>";
                    }
                    $display .= "</select>
									                </td>
									                <td><a onclick=\"remove_subject($(this).attr('con'),$(this).attr('tr'))\" con='ys_" . str_replace(' ',
                            '', $value->year . "-" . $value->semister) . "' tr='" . $value1->subj_code . "' title='remove' href=\"javascript:;\"><i class='fa fa-times'></i></a></td>
									            </tr>";
                    $totalUnit += ($value1->lec_unit + $value1->lab_unit);
                }
                $display .= "</tbody>
                                <tfooter>
                                	<tr>
                                		<td colspan=\"5\"><button onclick=\"add_subject($(this).attr('con'))\" con='ys_" . str_replace(' ',
                        '', $value->year . "-" . $value->semister) . "' class='btn btn-xs btn-default btn-add-subject' type='button'>Add subject</button></td>
                                        <td colspan=\"2\">Total Unit: " . $totalUnit . "</td>
                                	</tr>
                                </tfooter>
                            </table>
                        </center>
                     </div>";
                $totalUnit = 0;
                $array[] = $display;
                $display = "";
            }
        }
        echo json_encode($array);
    }

    public function add_sem_year()
    {
        $display = "";
        $sy = $this->input->get("ys");
        $tr = $this->input->get("tr");

        $display .= "<div id='ys_" . str_replace(' ', '', $sy) . "' class=\"curr-preview-body\">
                        <center>
                        	<input type='hidden' name='txt_ys[]' value='" . $sy . "'>
                            <h6 class=\"m-t-30 m-b-0\">" . $sy . "</h6>
                            <table class=\"table table-curr\">
                                <thead>
                                    <tr>
                                        <td>Course</td>
                                        <td>Title</td>
                                        <td>Lec</td>
                                        <td>Lab</td>
                                        <td>Unit</td>
                                        <td>Pre-requisites</td>
                                        <td></td>
                                    </tr>
                                </thead>
                                <tbody>";

        $display .= "<tr id='" . $tr . "'>
									                <td>...</td>
									                <td>
									                    <select onchange=\"setNameSelect2($(this).closest('tr').find('td select.js-example-basic-multiple').attr('name', 'subj_'+$(this).val()+'[]'))\" name='ys_" . str_replace(' ',
                '', $sy) . "_sub_id[]' required class=\"preview-select-title\">
									                    	<option value='' selected class='hide'>Select subject ...</option>";
        $subject = new Subject;
        $query = $subject->db->query("SELECT * FROM subject");
        $sub = $query->result();
        foreach ($sub as $key2 => $value2) {
            $display .= "<option value='" . $value2->subj_id . "'>" . $value2->subj_name . "</option>";
        }
        $display .= "</select>
									                </td>
									                <td></td>
									                <td></td>
									                <td></td>
									                <td>
									                	<select name='' placeholder='Select Pre-requisites' style='outline:none;border:none' class=\"form-control js-example-basic-multiple\" multiple=\"multiple\">";
        $subject = new Subject;
        $query = $subject->db->query("SELECT * FROM subject");
        $sub = $query->result();

        foreach ($sub as $key1 => $value1) {
            $display .= "<option value='" . $value1->subj_id . "'>" . $value1->subj_code . "</option>";
        }
        $display .= "</select>
									                </td>
									                <td><a onclick=\"remove_subject($(this).attr('con'),$(this).attr('tr'))\" con='ys_" . str_replace(' ',
                '', $sy) . "' tr='" . $tr . "' title='remove' href=\"javascript:;\"><i class='fa fa-times'></i></a></td>
									            </tr>";

        $display .= "</tbody>
                                <tfooter>
                                	<tr>
                                		<td colspan=\"5\"><button onclick=\"add_subject($(this).attr('con'))\" con='ys_" . str_replace(' ',
                '', $sy) . "' class='btn btn-xs btn-default btn-add-subject' type='button'>Add subject</button></td>
                                        <td colspan=\"2\">Total Unit: </td>
                                	</tr>
                                </tfooter>
                            </table>
                        </center>
                     </div>";

        echo $display;
    }

    public function addShSubject()
    {
        $tr = $this->input->get("tr");
        $con = $this->input->get("con");
        $display = "";
        $display .= "<tr id='" . $tr . "'>
		                <td>...</td>
		                <td>
                    		<select onchange=\"setNameSelect2($(this).closest('tr').find('td select.js-example-basic-multiple').attr('name', 'subj_'+$(this).val()+'[]'))\" name='" . str_replace(' ',
                '', $con) . "_sub_id[]' required class=\"preview-select-title\">
		                    	<option value='' selected class='hide'>Select subject ...</option>";
        $subject = new Subject;
        $query = $subject->db->query("SELECT * FROM subject ORDER BY subj_name");
        $sub = $query->result();
        foreach ($sub as $key2 => $value2) {
            $display .= "<option value='" . $value2->subj_id . "'>".$value2->subj_code.' - '. $value2->subj_name . "</option>";
        }
        $display .= "</select>       
		                </td>
		                <td></td>
		                <td></td>
		                <td></td>
		                <td>
		                	<select name='' placeholder='Select Pre-requisites' style='outline:none;border:none' class=\"form-control js-example-basic-multiple\" multiple=\"multiple\">";
        $subject = new Subject;
        $query = $subject->db->query("SELECT * FROM subject");
        $sub = $query->result();
        foreach ($sub as $key1 => $value1) {
            $display .= "<option value='" . $value1->subj_id . "'>" . $value1->subj_code . "</option>";
        }
        $display .= "</select>
		                </td>
		                <td><a onclick=\"remove_subject($(this).attr('con'),$(this).attr('tr'))\" con='" . str_replace(' ', '', $con) . "' tr='" . $tr . "' title='remove' href=\"javascript:;\"><i class='fa fa-times'></i></a></td>
		            </tr>";

        echo $display;
    }

    public function add_subject()
    {
        $tr = $this->input->get("tr");
        $con = $this->input->get("con");
        $display = "";
        $display .= "<tr id='" . $tr . "'>
		                <td>...</td>
		                <td>
                    		<select onchange=\"setNameSelect2($(this).closest('tr').find('td select.js-example-basic-multiple').attr('name', 'subj_'+$(this).val()+'[]'))\" name='" . str_replace(' ',
                '', $con) . "_sub_id[]' required class=\"preview-select-title\">
		                    	<option value='' selected class='hide'>Select subject ...</option>";
        $subject = new Subject;
        $query = $subject->db->query("SELECT * FROM subject ORDER BY subj_name");
        $sub = $query->result();
        foreach ($sub as $key2 => $value2) {
            $display .= "<option value='" . $value2->subj_id . "'>".$value2->subj_code.' - '. $value2->subj_name . "</option>";
        }
        $display .= "</select>       
		                </td>
		                <td></td>
		                <td></td>
		                <td></td>
		                <td>
		                	<select name='' placeholder='Select Pre-requisites' style='outline:none;border:none' class=\"form-control js-example-basic-multiple\" multiple=\"multiple\">";
        $subject = new Subject;
        $query = $subject->db->query("SELECT * FROM subject");
        $sub = $query->result();
        foreach ($sub as $key1 => $value1) {
            $display .= "<option value='" . $value1->subj_id . "'>" . $value1->subj_code . "</option>";
        }
        $display .= "</select>
		                </td>
		                <td><a onclick=\"remove_subject($(this).attr('con'),$(this).attr('tr'))\" con='" . str_replace(' ', '', $con) . "' tr='" . $tr . "' title='remove' href=\"javascript:;\"><i class='fa fa-times'></i></a></td>
		            </tr>";

        echo $display;
    }

    public function save_revision()
    {
        $yearsem = new Year_sem;
        $sub = new Cur_subject;

        $cur_id = $this->input->post("cur_id");
        $ys = $this->input->post("txt_ys");

        //DELETE PREVIOUS SUBJECTS //
        $ysList = $yearsem->search(array("cur_id" => $cur_id));
        foreach ($ysList as $k1 => $v1) {
            $deleteResult = $sub->db->simple_query("DELETE FROM cur_subject WHERE ys_id = {$v1->ys_id}");
        }

        foreach ($ys as $key => $value) {
            $yearsem = new Year_sem;
            $s = explode(" - ", $value);
            // -------------------------- SAVE YEAR ------------------------- //
            // CHECK IF YEAR AND SEMISTER EXIST IN CURRICULUM//
            $currCheck = $yearsem->search(array("cur_id" => $cur_id, "year" => $s[0], "semister" => $s[1]));
            if (!empty($currCheck)) {
                // GET THE YS ID
                $ys_id = 0;
                foreach ($currCheck as $key1 => $value1) {
                    $ys_id = $value1->ys_id;
                }
                // SAVE SUBJECT //
                $subj_id = $this->input->post("ys_" . str_replace(' ', '', $value) . "_sub_id");
                if ($deleteResult) {
                    if (!empty($subj_id)) {
                        foreach ($subj_id as $key2 => $value2) {
                            // SAVE SUBJECT
                            $sub = new Cur_subject;
                            $sub->subj_id = $value2;
                            $sub->ys_id = $ys_id;
                            $sub->save();

                            $preq = $this->input->post("subj_" . $value2);
                            if ($sub->db->affected_rows() > 0) {
                                // SAVE PRE-REQUISITE //
                                $cs_id = $sub->db->insert_id();

//                                print_r($cs_id);


                                if (!empty($preq)) {
                                    foreach ($preq as $kpreq => $vpreq) {
                                        $pre_requisite = new Pre_requisite();
                                        $pre_result = $pre_requisite->search(['cs_id'=>$cs_id]);

                                        // GET PREREQUISITES //
                                        $prq = new Pre_requisite;
                                        $prq->cs_id = $cs_id;
                                        $prq->subj_id = $vpreq;
                                        $prq->save();
                                    }
                                }
                            }
                        }
                    }

                }
            } // iF NOT EXIST YEAR AND SEMISTER IN CURRICULUM
            else {
                // SAVE YEAR AND SEMISTER //
                $yearsem->year = $s[0];
                $yearsem->semister = $s[1];
                $yearsem->cur_id = $cur_id;
                $yearsem->save();
                // IF YEAR AND SEM SAVED
                if ($yearsem->db->affected_rows() > 0) {
                    $ys_id = $yearsem->db->insert_id();
                    // SAVE SUBJECT
                    $subj_id = $this->input->post("ys_" . str_replace(' ', '', $value) . "_sub_id");
                    foreach ($subj_id as $key2 => $value2) {
                        // SAVE SUBJECT
                        $sub = new Cur_subject;

                        $sub->subj_id = $value2;
                        $sub->ys_id = $ys_id;
                        $sub->save();

                        $preq = $this->input->post("subj_" . $value2);
                        if ($sub->db->affected_rows() > 0) {
                            // SAVE PRE-REQUISITE //
                            $cs_id = $sub->db->insert_id();
                            if (!empty($preq)) {
                                foreach ($preq as $kpreq => $vpreq) {
                                    // GET PREREQUISITES //
                                    $prq = new Pre_requisite;
                                    $prq->cs_id = $cs_id;
                                    $prq->subj_id = $vpreq;
                                    $prq->save();
                                }
                            }
                        }
                    }

                }
            }
        }
        echo json_encode(array("result" => true));
    }

    private function savePrerequisite($data = array())
    {
    }

    public function setActiveInactive()
    {
        $curr = new Curr_codelist;
        $cur_id = $this->input->get("cur_id");
        // GET CURRENT STATUS //
        $curInfo = $curr->search(array("cur_id" => $cur_id));
        $curStatus = $curInfo[$cur_id]->status;
        // UPDATE STATUS
        $str = "";
        $str2 = "";
        if ($curStatus == "active") {
            $curStatus = "inactive";
            $str = "Activate";
        } elseif ($curStatus == "inactive") {
            $curStatus = "active";
            $str = "Deactivate";
        }
        $resultUpdate = $curr->db->simple_query("UPDATE curr_codelist SET status = '{$curStatus}' WHERE cur_id = {$cur_id}");

        if ($resultUpdate) {
            $str2 = $str;
        }
        echo json_encode(array("result" => $resultUpdate, "str" => $str2));
    }

    public function getSubjectLoadTags()
    {
        $sub = new Subject;
        $query = $sub->db->query("SELECT * FROM subject ORDER BY subj_name ASC");
        $list = $query->result();
        if (!empty($list)) {
            foreach ($list as $key => $value) {
                $array[] = array("id" => "$value->subj_id", "text" => "$value->subj_code");
            }
        }
        echo json_encode($array);
    }
}
