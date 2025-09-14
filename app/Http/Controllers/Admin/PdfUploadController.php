<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Database\QueryException;
use App\Audited;

class PdfUploadController extends Controller
{

    public function fileUpload()
    {
        $audited = Audited::all();
        return view('admin.pdf.index',compact('audited'));

    }
    public function fileUploadCreate(){
        return view('admin.pdf.PdfUpload');
    }

    public function fileUploadPost(Request $request)
    {
       
        $request->validate([
            'file' => 'required|mimes:pdf',
        ]);

        if ($request->file('file')) {
  
        $img_path = "/pdf";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';
        $resourceImage = $request->file;
        $nameImage = time();
        $file_extImage = $request->file->extension();
        $nameImage = str_replace(" ", "-", $nameImage);
        $img_name = $img_path . "/" . $nameImage . "." . $file_extImage;

        $resourceImage->move($basepath . $img_path, $img_name);
        
        $success='Upload File Berhasil';
        $pdf='https://simpletabadmin.ptab-vps-storage.com/pdf/'.$nameImage.".".$file_extImage;
        
        }
        $data = array(
            'name' => $request->name,
            'periode' => $request->periode,
            'file' => $nameImage.".".$file_extImage,
        );

     
        $success='Upload File Berhasil';
        $audited = Audited::create($data);
        return redirect()->route('admin.file.upload');
    }

    public function fileUploadDestroy(Audited $audited){
        abort_unless(\Gate::allows('lock_action_delete'), 403);

        $audited->delete();

        return redirect()->route('admin.file.upload');

    }
   
}
