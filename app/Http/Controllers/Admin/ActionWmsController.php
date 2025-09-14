<?php

namespace App\Http\Controllers\Admin;

use App\actionWms;
use App\Dapertement;
use App\Http\Controllers\Controller;
use App\proposalWms;
use App\Subdapertement;
use App\Traits\TraitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActionWmsController extends Controller
{
    use TraitModel;

    // list tindakan
    function index($id)
    {
        abort_unless(\Gate::allows('actionWm_access'), 403);
        $cek = actionWms::where('proposal_wm_id', $id)->get();
        $actionWms = actionWms::where('proposal_wm_id', $id)->get();
        // dd($actionWms);
        return view('admin.actionWms.index', compact('actionWms', 'id', 'cek'));
        // dd($actions);
    }


    public function create($id)
    {
        abort_unless(\Gate::allows('actionWm_create'), 403);

        $subdapertement_id = '';

        foreach (Auth::user()->roles as $data) {
            $roles[] = $data->id;
        }
        if (in_array('8', $roles)) {
            $subdapertement =  Subdapertement::with('dapertement')->get();
        } else {
            $subdapertement = Subdapertement::where('dapertement_id', Auth::user()->dapertement_id)->where('name', 'TEKNIK')->first();
        }
        return view('admin.actionWms.create', compact('id', 'subdapertement', 'subdapertement_id', 'roles'));
    }
    public function store(Request $request)
    {
        abort_unless(\Gate::allows('actionWm_create'), 403);
        $data = [
            'proposal_wm_id' => $request->proposal_wm_id,
            'subdapertement_id' => $request->subdapertement_id,
            'memo' => $request->memo,
            'category' => $request->category
        ];
        $cek = actionWms::where('proposal_wm_id', $request->id)->get();
        if (count($cek) < 1) {
            actionWms::create($data);
        } else {
            dd('tindakan sudah ada');
        }
        return redirect()->route('admin.actionWms.index', [$request->proposal_wm_id]);
    }

    public function edit($id)
    {
        abort_unless(\Gate::allows('actionWm_edit'), 403);

        $actionWm = actionWms::where('id', $id)->first();
        // dd($actionWm);
        return view('admin.actionWms.edit', compact('actionWm'));
    }

    public function update($id, Request $request)
    {
        abort_unless(\Gate::allows('actionWm_edit'), 403);

        $actionWm = actionWms::where('id', $id)->first();
        $img_path = "/images/WaterMeter";
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin/", \base_path());
        $basepath  = base_path().'/public';

        $data = $request->all();
        // upload image
        if ($request->file('old_image')) {
            foreach ($request->file('old_image') as $key => $image) {
                $resourceImage = $image;
                $nameImage = 'oldImage' . strtolower($id);
                $file_extImage = $image->extension();
                $nameImage = str_replace(" ", "-", $nameImage);
                $img_name = $img_path . "/" . $nameImage . "-" . $request->action_id . $key . "." . $file_extImage;
                $folder_upload = 'images/WaterMeter';
                $resourceImage->move($folder_upload, $img_name);
                $dataImageNamePreWork[] = $img_name;
                // dd($request->file('old_image')->move($folder_upload, $img_name));
            }

            if ($actionWm->old_image != '') {
                foreach (json_decode($actionWm->old_image) as $n) {
                    if (file_exists($n)) {

                        unlink($basepath . $n);
                    }
                }
            }
            $data = array_merge($data, ['old_image' => str_replace("\/", "/", json_encode($dataImageNamePreWork))]);
        }

        // upload image
        if ($request->file('new_image')) {
            foreach ($request->file('new_image') as $key => $image) {
                $resourceImage = $image;
                $nameImage = 'newImage' . strtolower($id);
                $file_extImage = $image->extension();
                $nameImage = str_replace(" ", "-", $nameImage);
                $img_name = $img_path . "/" . $nameImage . "-" . $request->action_id . $key . "." . $file_extImage;
                $folder_upload = 'images/WaterMeter';
                $resourceImage->move($folder_upload, $img_name);
                $dataImageNameTool[] = $img_name;
                // dd($request->file('old_image')->move($folder_upload, $img_name));
            }
            if ($actionWm->new_image != '') {
                foreach (json_decode($actionWm->new_image) as $n) {
                    if (file_exists($n)) {

                        unlink($basepath . $n);
                    }
                }
            }
            $data = array_merge($data, ['new_image' => str_replace("\/", "/", json_encode($dataImageNameTool))]);
        }

        if ($request->file('image_done')) {
            foreach ($request->file('image_done') as $key => $image) {
                $resourceImage = $image;
                $nameImage = 'imageDone' . strtolower($id);
                $file_extImage = $image->extension();
                $nameImage = str_replace(" ", "-", $nameImage);
                $img_name = $img_path . "/" . $nameImage . "-" . $request->action_id . $key . "." . $file_extImage;
                $folder_upload = 'images/WaterMeter';
                $resourceImage->move($folder_upload, $img_name);
                $dataImageNameDone[] = $img_name;
                if ($actionWm->image_done) {
                    foreach (json_decode($actionWm->image_done) as $n) {
                        if (file_exists($n)) {

                            unlink($basepath . $n);
                        }
                    }
                }
                $data = array_merge($data, ['image_done' => str_replace("\/", "/", json_encode($dataImageNameDone))]);
                // dd($request->file('old_image')->move($folder_upload, $img_name));
            }
        }

        // untuk menambah nomor urut start
        $proposal = proposalWms::join('action_wms', 'action_wms.proposal_wm_id', '=', 'proposal_wms.id')
            ->join('subdapertements', 'subdapertements.id', '=', 'action_wms.subdapertement_id')
            ->join('dapertements', 'subdapertements.dapertement_id', '=', 'dapertements.id')
            ->where('proposal_wms.id', $actionWm->proposal_wm_id)->first();
        if ($proposal->close_queue == "" && $request->status == "close") {

            $gU = $proposal->group_unit;
            if ($gU == "1") {
                $s = "BAP";
                // $n = 14;
            } else if ($gU == "2") {
                $s = "BAPUK";
                // $n = 15;
            } else if ($gU == "4") {
                $s = "BAPUP";
                // $n = 15;
            } else if ($gU == "5") {
                $s = "BAPUB";
                // $n = 15;
            } else if ($gU == "3") {
                $s = "BAPUS";
                // $n = 15;
            } else {
                $s = "";
                // $n = 15;
            }


            $last_code = $this->get_last_codeC('proposal_wm', $gU);

            $proposal = proposalWms::where('id', $actionWm->proposal_wm_id)->first();
            $proposal->status = $request->status;
            $proposal->close_queue = $last_code;
            $proposal->code = '/' . $s . '/' . date('n') . '/' . date('Y');
            $proposal->save(['updated_at' => false]);
            // dd('test1');
        } else {
            $proposal = proposalWms::where('id', $actionWm->proposal_wm_id)->first();
            $proposal->status = $request->status;
            $proposal->save(['updated_at' => false]);
            // dd('test2');
        }

        // untuk menambah nomor urut end

        $actionWm->update($data);
        return redirect()->route('admin.actionWms.index', [$actionWm->proposal_wm_id]);
    }

    public function destroy($id)
    {
        abort_unless(\Gate::allows('actionWm_delete'), 403);

        $actionWm = actionWms::where('id', $id)->first();
        $i = 0;
        //$basepath = str_replace("laravel-simpletab", "public_html/simpletabadmin", \base_path());
        $basepath  = base_path().'/public';
        if ($actionWm->old_image != '') {
            foreach (json_decode($actionWm->old_image) as $n) {
                if (file_exists($n)) {

                    unlink($basepath . $n);
                }
            }
        }
        if ($actionWm->new_image != '') {
            foreach (json_decode($actionWm->new_image) as $n) {
                if (file_exists($n)) {

                    unlink($basepath . $n);
                }
            }
        }
        if ($actionWm->image_done) {
            foreach (json_decode($actionWm->image_done) as $n) {
                if (file_exists($n)) {

                    unlink($basepath . $n);
                }
            }
        }
        actionWms::where('id', $id)->delete();
        return redirect()->back();
    }
}
