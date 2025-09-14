<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Image;

class AddImageController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function imageFileUpload(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|image|mimes:jpg,jpeg,png,gif,svg|max:4096',
        ]);

        $image = $request->file('file');
        $input['file'] = time() . '.' . $image->getClientOriginalExtension();

        $imgFile = Image::make($image->getRealPath());

        $imgFile->insert("https://simpletabadmin.ptab-vps-storage.com/images/Logo.png", 'bottom-right', 10, 10);

        $imgFile->text('' . Date('Y-m-d H:i:s') . ' lat : 99898998998 lng : -23232333111', 10, 10, function ($font) {
            $font->file(public_path('font/Titania-Regular.ttf'));
            $font->size(14);
            $font->color('#000000');
            $font->valign('top');
        })->save(public_path('/upload') . '/' . 'watermark.jpg');

        return back()
            ->with('success', 'File uploaded successfully ')
            ->with('fileName', $input['file']);
    }
}
