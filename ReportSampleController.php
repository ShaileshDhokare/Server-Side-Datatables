<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use Illuminate\Support\Facades\Storage;

class ReportSampleController extends Controller
{
    
    public function index(){
        // dd( URL::to("/"));
        return view('pages.allsamples');
    }

    public function samplesListing(Request $request)
    {
        // DB::enableQueryLog();
        
        $col = array(
            0   =>  'amr_id',
            1   =>  'amr_title',
            2   =>  'amr_sample_code',
            3   =>  'category',
            4   =>  'amr_type',
            5   =>  'amr_user',
            6   =>  'amr_published_date',
            8   =>  'amr_file'
        );

        $totalData = DB::table('amr_sample')->count();
        
        $start = $request->get('start');
        $length = $request->get('length');
        $search = $request->get('search')['value'];
        $order_column = $col[$request->get('order')[0]['column']];
        $orderBy = $request->get('order')[0]['dir'];

        if ($search == NULL || empty(trim($search))) {
            $samples = DB::table('amr_sample')
                ->select('amr_id','amr_file', 'amr_sample_code', 'amr_user', 'amr_title', 'amr_type','category', 'amr_published_date')
                ->where('amr_status', 'Y')
                ->orderBy($order_column, $orderBy)
                ->offset( $start)
                ->limit($length)
                ->get();
            $totalFilter = DB::table('amr_sample')
                            ->where('amr_status', 'Y')
                            ->select('amr_id')
                            ->count();
        } else {
            $samples = DB::table('amr_sample')
                ->select('amr_id','amr_file', 'amr_sample_code', 'amr_user', 'amr_title', 'amr_type','category', 'amr_published_date')
                ->where('amr_status', 'Y')
                ->where('amr_id', 'like', '%' . $search . '%')
                ->orWhere('amr_sample_code', 'like', '%' . $search . '%')
                ->orWhere('amr_user', 'like', '%' . $search . '%')
                ->orWhere('amr_title', 'like', '%' . $search . '%')
                ->orWhere('amr_type', 'like', '%' . $search . '%')
                ->orWhere('category', 'like', '%' . $search . '%')
                ->orWhere('amr_published_date', 'like', '%' . $search . '%')
                ->orderBy($order_column, $orderBy)
                ->offset( $start)
                ->limit($length)
                ->get();
                
            $totalFilter = DB::table('amr_sample')
                ->select('amr_id')
                ->where('amr_status', 'Y')
                ->where('amr_id', 'like', '%' . $search . '%')
                ->orWhere('amr_sample_code', 'like', '%' . $search . '%')
                ->orWhere('amr_user', 'like', '%' . $search . '%')
                ->orWhere('amr_title', 'like', '%' . $search . '%')
                ->orWhere('amr_type', 'like', '%' . $search . '%')
                ->orWhere('category', 'like', '%' . $search . '%')
                ->orWhere('amr_published_date', 'like', '%' . $search . '%')
                ->count();
        }
        // dd(DB::getQueryLog());
         
        $data = array();
        foreach ($samples as $sample) {
            $subdata = array();
            $subdata[] = $sample->amr_id;
            $subdata[] = $sample->amr_title;
            $subdata[] = $sample->amr_sample_code;
            $subdata[] = $sample->category;
            $subdata[] = $sample->amr_type;
            $subdata[] = $sample->amr_user;
            $subdata[] = $sample->amr_published_date;
            $subdata[] = '<a onclick="downloadSample('.$sample->amr_id.');"><i class="fas fa-download ml-2 h6 text-primary"></i></a><a onclick="editSample('.$sample->amr_id.');"><i class="fas fa-pencil-alt ml-2 h6 text-warning"></i></a><a onclick="confirmDelete('.$sample->amr_id.');"><i class="fas fa-trash-alt ml-2 h6 text-danger"></i></a><a onclick="confirmActive('.$sample->amr_id.');"><i class="fas fa-check-square ml-2 h6 text-success"></i></a>';
            $data[] = $subdata;
        }
        $json_data = array(
            "draw"              =>  intval($request->get('draw')),
            "recordsTotal"      =>  intval($totalData),
            "recordsFiltered"   =>  intval($totalFilter),
            "data"              =>  $data
        );

        return response()->json($json_data);
    }

    
    public function deactivateSample(Request $request){
        $sampleId = $request->get('sampleId');
        $sample = DB::table('amr_sample')
                        ->where('amr_id', $sampleId)
                        ->update(['amr_status' => 'N']);
        if ($sample) {
            $message = "Sample is Deactivated successfully";
        } else {
            $message = "Your Request is failed";
        }
        return $message;
    }

    public function activateSample(Request $request){
        $sampleId = $request->get('sampleId');
        $sample = DB::table('amr_sample')
                        ->where('amr_id', $sampleId)
                        ->update(['amr_status' => 'Y']);
        if ($sample) {
            $message = "Sample is Activated successfully";
        } else {
            $message = "Your Request is failed";
        }
        return $message;
    }

    public function uploadSample()
    {
        return view('pages.uploadsample');
    }

    
    public function storeSample(Request $request)
    {
        // dd($request);
        date_default_timezone_set("Asia/Kolkata");
        if ($request->hasFile('amr_file')) {
            if (!is_dir(public_path(). '/reportsamples')) {
                mkdir(public_path(). '/reportsamples', 0777, true);
            }
            $file =  $request->file('amr_file');
            $allowedfileExtension=['pdf','docx'];
            $extension = $file->getClientOriginalExtension();
            $check = in_array($extension, $allowedfileExtension);
            if(!$check){
                return redirect('/upload-new-sample')->with('error', 'Filetype is not valid');
            }
            // dd($extension);
            $destinationPath = public_path(). '/reportsamples';
            $filename = explode('.', $file->getClientOriginalName());
            if(count($filename) > 2){
                return redirect('/upload-new-sample')->with('error', 'Filename is not valid');
            }
            $new_filename = $filename[0].'_V1.1.'.$filename[1]; 
            $file->move($destinationPath, $new_filename);
        }
        
        $publish_date = $request->get('amr_published_date');
        $dates = explode('-', $publish_date);
        $month = jdmonthname(gregoriantojd($dates[1],13,2019),1);
        $amr_published_date = $month.' '.$dates[0];

        $amr_title = $request->get('amr_title');
        $amr_sample_code = $request->get('amr_sample_code');
        $category = $request->get('category');
        $amr_type = $request->get('amr_type');
        $amr_user = Auth::user()->name;
        $amr_version = 1.1;
       
        $sample_id = DB::table('amr_sample')->insertGetId(
                    ['amr_sample_code' => $amr_sample_code,
                    'amr_entry_date' => date('Y-m-d H:i:s'),
                    'amr_user' => $amr_user,
                    'amr_depart' => $category,
                    'amr_status' =>  'Y',
                    'category' => $category,
                    'amr_title' => $amr_title,
                    'amr_version' => $amr_version,
                    'amr_type' => $amr_type,
                    'amr_published_date' => $amr_published_date,
                    ]);
        if($sample_id){
            $sample = DB::table('sample_versions')->insert(
                        [
                            'report_id' => $sample_id,
                            'user_id' => Auth::user()->id,
                            'sample_filename' => $new_filename,
                            'version' => $amr_version,
                            'published_date' => $publish_date,
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
            
        }
        if($sample){
            $message = "Report Sample Uploaded Successfully.";
        }else{
            $message = "Failed to Upload Report Sample";
        }
        return redirect('/samples-listing')->with('success', $message);
    }

    public function editSample(Request $request)
    {
        $sampleId = $request->get('sampleId');

        $sample = DB::table('sample_versions')
                    ->leftJoin('amr_sample', 'sample_versions.report_id', '=', 'amr_sample.amr_id')
                    ->select('amr_sample.amr_title','amr_sample.amr_sample_code', 'sample_versions.*')
                    ->where('sample_versions.version_id', $sampleId)
                    ->first();

        return response()->json($sample);
    }

    public function updateSample(Request $request)
    {
        date_default_timezone_set("Asia/Kolkata");

        $version_id = $request->get('version_id');
        $version = $request->get('version');

        $data = array(
            'user_id' => Auth::user()->id,
            'created_at' => date('Y-m-d H:i:s')
        );
        if ($request->hasFile('sample_filename')) {
            if (!is_dir(public_path(). '/reportsamples')) {
                mkdir(public_path(). '/reportsamples', 0777, true);
            }
            $file =  $request->file('sample_filename');
            $allowedfileExtension=['pdf','docx'];
            $extension = $file->getClientOriginalExtension();
            $check = in_array($extension, $allowedfileExtension);
            if(!$check){
                return redirect('/samples-listing')->with('error', 'Filetype is not valid');
            }
            $destinationPath = public_path(). '/reportsamples';
            $filename = explode('.', $file->getClientOriginalName());
            if(count($filename) > 2){
                return redirect('/samples-listing')->with('error', 'Filename is not valid');
            }
            $new_filename = $filename[0].'_V'.$version.'.'.$filename[1];
            $file->move($destinationPath, $new_filename);
            $new_file = array("sample_filename" => $new_filename);
            $data = array_merge($data, $new_file);
        }
        if($request->get('amr_published_date') != null){
            $publish_date = $request->get('amr_published_date');
            $new_date = array('published_date' => $publish_date);
            $data = array_merge($data, $new_date);
        }
        $sample = DB::table('sample_versions')
                    ->where('version_id', $version_id)
                    ->update($data);

        if($sample){
            $message = "Report Sample Updated Successfully.";
        }else{
            $message = "Failed to Update Report Sample";
        }
        return redirect('/samples-listing')->with('success', $message);
    }
    public function downloadSample($id){
       
        $sample = DB::table('sample_versions')
                    ->select('version_id','sample_filename')
                    ->where('version_id', $id)
                    ->first();

        $file = public_path().'/reportsamples/'.$sample->sample_filename;
        
        if (file_exists($file)) {
            return response()->download(public_path('reportsamples/'.$sample->sample_filename));
        }else{
            $message = "Report Sample is not available";
            return redirect('/samples-listing')->with('success', $message);
        }
    }
    
    public function getVersions(Request $request){
        $sampleId = $request->get('sampleId');
        $sample = DB::table('sample_versions')
                       ->select('version_id', 'report_id', 'version', 'published_date')
                       ->where('report_id', $sampleId)
                       ->where('status', 'Y')
                       ->get();
        return response()->json($sample);
    }

    public function upgradeSample($id){
        $sample = DB::table('amr_sample')
                    ->where('amr_id', $id)
                    ->first();
        return view('pages.upgradesample', compact('sample'));
    }
    public function storeSampleVersion(Request $request){

        $sampleId = $request->get('amr_id');
        $sample = DB::table('amr_sample')
                    ->where('amr_id', $sampleId)
                    ->first();
        $sampleVersion = $sample->amr_version+0.1;
        
        date_default_timezone_set("Asia/Kolkata");
        if ($request->hasFile('amr_file')) {
            if (!is_dir(public_path(). '/reportsamples')) {
                mkdir(public_path(). '/reportsamples', 0777, true);
            }
            $file =  $request->file('amr_file');
            $allowedfileExtension=['pdf','docx'];
            $extension = $file->getClientOriginalExtension();
            $check = in_array($extension, $allowedfileExtension);
            if(!$check){
                return redirect('upgrade-sample/'.$sampleId)->with('error', 'Filetype is not valid');
            }
            $destinationPath = public_path(). '/reportsamples';
            $filename = explode('.', $file->getClientOriginalName());
            if(count($filename) > 2){
                return redirect('upgrade-sample/'.$sampleId)->with('error', 'Filename is not valid (Should not Contains Dot)');
            }
            $new_filename = $filename[0].'_V'.$sampleVersion.'.'.$filename[1]; 
            $file->move($destinationPath, $new_filename);
        }
        
        $publish_date = $request->get('amr_published_date');
        
        $amr_user = Auth::user()->name;
        $sample = DB::table('sample_versions')->insert(
                    [
                        'report_id' => $sampleId,
                        'user_id' => Auth::user()->id,
                        'sample_filename' => $new_filename,
                        'version' => $sampleVersion,
                        'published_date' => $publish_date,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                            
        if($sample){
            $sample = DB::table('amr_sample')
                    ->where('amr_id', $sampleId)
                    ->update(['amr_version' => $sampleVersion]);
            $message = "Report Sample Upgraded Successfully.";
        }else{
            $message = "Failed to Upgrade Report Sample";
        }
        return redirect('/samples-listing')->with('success', $message);

    }
    
}
