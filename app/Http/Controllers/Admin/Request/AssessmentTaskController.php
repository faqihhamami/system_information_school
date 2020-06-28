<?php

namespace App\Http\Controllers\Admin\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
// vendor
use Carbon\Carbon;
// model
use App\Models\ScAssessmentTask;

class AssessmentTaskController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->random = rand(1000000, 10000000);
        $this->timestamp = Carbon::now();
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $order = $request->get('orderby');
        $search = $request->get('search');
        $columns = $request->get('columns');
        $type = $request->get('type');
        if($order == true){
            // search default
            if($search == 'default'){
                if($columns < 101 && $columns > 1){
                    return $this->searchDefault($order, $type, $columns);
                }
            // searching
            }else{
                if($columns < 101 && $columns > 1){
                    return $this->searching($order, $type, $search, $columns);
                }else{
                    return $this->searching($order, $type, $search, 25);
                }
            }
        // default url api/class
        }else{
            if($columns < 101 && $columns > 1){
                return $this->searchDefault('id', 'asc', $columns);
            }
            if($type == 'default'){
                return $this->searchDefault('id', 'asc', '25');
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $Validator = Validator::make($request->all(), [
            'sc_student_id' => 'required|numeric',
            'title' => 'required|string|min:2|max:191',
            'description' => 'required|string|min:2|max:191',
            'score' => 'required|numeric',
        ]);
        if($Validator->fails()){
            return response()->json(['message' => $validator->errors()], 401);
        }else{
            ScAssessmentTask::create([
                'id' => $this->random,
                'sc_student_id' => $request->sc_student_id,
                'title' => $request->title,
                'description' => $request->description,
                'score' => $request->score
            ]);
            return response()->json(['message' => 'Successfuly create data'], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  $ScassessmentTask
     * @return \Illuminate\Http\Response
     */
    public function show($ScassessmentTask)
    {
        return response()->json(ScAssessmentTask::join('sc_students', 'sc_assessment_tasks.sc_student_id', '=', 'sc_students.id')
            ->join('users', 'sc_students.user_id', '=', 'users.id')
            ->join('sc_schools', 'sc_students.sc_school_id', '=', 'sc_schools.id')
            ->join('sc_classes', 'sc_students.sc_class_id', '=', 'sc_classes.id')
            ->orderBy('id', 'asc')
            ->where('sc_assessment_tasks.id', $ScassessmentTask)
            ->select(
                'sc_assessment_tasks.id', 'sc_assessment_tasks.title', 'sc_assessment_tasks.description', 'sc_assessment_tasks.score', 'sc_assessment_tasks.created_at', 'sc_assessment_tasks.updated_at',
                'sc_students.id as sc_student_id', 'sc_students.user_id',
                'sc_schools.id as sc_school_id', 'sc_schools.name as sc_school_name',
                'sc_classes.id as sc_class_id', 'sc_classes.name as sc_class_name',
                'users.name as user_name', 'users.nisn')
            ->get(), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $ScassessmentTask
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $ScassessmentTask)
    {
        $Validator = Validator::make($request->all(), [
            'sc_student_id' => 'required|numeric',
            'title' => 'required|string|min:2|max:191',
            'description' => 'required|string|min:2|max:191',
            'score' => 'required|numeric',
        ]);
        if($Validator->fails()){
            return response()->json(['message' => $validator->errors()], 401);
        }else{
            ScAssessmentTask::where('id', $ScassessmentTask)->update([
                'sc_student_id' => $request->sc_student_id,
                'title' => $request->title,
                'description' => $request->description,
                'score' => $request->score,
                'updated_at' => $this->timestamp
            ]);
            return response()->json(['message' => 'Successfuly create data'], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  $ScassessmentTask
     * @return \Illuminate\Http\Response
     */
    public function destroy($ScassessmentTask)
    {
        ScAssessmentTask::where('id', $ScassessmentTask)->delete();
        return response()->json(['message' => 'Successfuly delete data'], 200);
    }
    ////////////////////////////////end resources////////////////////////////////

    /**
     * Display a listing of the resource.
     *
     * @param $$order $type $columns
     * @return \Illuminate\Http\Response
     */
    public function searchDefault($order, $type, $columns)
    {
        return response()->json(ScAssessmentTask::join('sc_students', 'sc_assessment_tasks.sc_student_id', '=', 'sc_students.id')
            ->join('users', 'sc_students.user_id', '=', 'users.id')
            ->join('sc_schools', 'sc_students.sc_school_id', '=', 'sc_schools.id')
            ->join('sc_classes', 'sc_students.sc_class_id', '=', 'sc_classes.id')
            ->orderBy($order, $type)
            ->select(
                'sc_assessment_tasks.id', 'sc_assessment_tasks.title', 'sc_assessment_tasks.description', 'sc_assessment_tasks.score', 'sc_assessment_tasks.created_at', 'sc_assessment_tasks.updated_at',
                'sc_students.user_id',
                'sc_schools.id as sc_school_id', 'sc_schools.name as sc_school_name',
                'sc_classes.id as sc_class_id', 'sc_classes.name as sc_class_name',
                'users.name as user_name', 'users.nisn')
            ->paginate($columns), 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @param $$order $type $search $paginate
     * @return \Illuminate\Http\Response
     */
    public function searching($order, $type, $search, $paginate)
    {
        /*id schoolname, classname, tit;e, score, nisn*/
        return response()->json(ScAssessmentTask::join('sc_students', 'sc_assessment_tasks.sc_student_id', '=', 'sc_students.id')
            ->join('users', 'sc_students.user_id', '=', 'users.id')
            ->join('sc_schools', 'sc_students.sc_school_id', '=', 'sc_schools.id')
            ->join('sc_classes', 'sc_students.sc_class_id', '=', 'sc_classes.id')
            ->orderBy($order, $type)
            ->where('sc_assessment_tasks.id', 'like', '%' . $search . '%')
            ->orWhere('sc_schools.name', 'like', '%' . $search . '%')
            ->orWhere('sc_classes.name', 'like', '%' . $search . '%')
            ->orWhere('sc_assessment_tasks.title', 'like', '%' . $search . '%')
            ->orWhere('sc_assessment_tasks.score', 'like', '%' . $search . '%')
            ->select(
                'sc_assessment_tasks.id', 'sc_assessment_tasks.title', 'sc_assessment_tasks.description', 'sc_assessment_tasks.score', 'sc_assessment_tasks.created_at', 'sc_assessment_tasks.updated_at',
                'sc_students.user_id',
                'sc_schools.id as sc_school_id', 'sc_schools.name as sc_school_name',
                'sc_classes.id as sc_class_id', 'sc_classes.name as sc_class_name',
                'users.name as user_name', 'users.nisn')
            ->paginate($columns), 200);
    }
}
