<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
 
class VirmachController extends Controller
{
    public function index()
    {
        return view('admin.virmach.index');
    }
 
    public function store(Request $request)
    {
        if($request->hasFile('profile_image')) {
          
            //get filename with extension
            $filenamewithextension = $request->file('profile_image')->getClientOriginalName();
      
            //get filename without extension
            $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);
      
            //get file extension
            $extension = $request->file('profile_image')->getClientOriginalExtension();
      
            //filename to store
            $filenametostore = $filename.'_'.uniqid().'.'.$extension;
      
            //Upload File to external server
            Storage::disk('ftp')->put($filenametostore, fopen($request->file('profile_image'), 'r+'));
      
            //Store $filenametostore in the database
        }
 
        return redirect('admin.virmach.index')->with('success', "Image uploaded successfully.");
    }
}