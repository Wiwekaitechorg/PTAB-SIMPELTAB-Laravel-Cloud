<?php

namespace App\Http\Controllers\api\v1\dapertement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ActionApi;
use App\TicketApi;
use Illuminate\Database\QueryException;
class ActionsApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   public function list($dapertement_id)
   {
        try {
            $action = ActionApi::where('dapertement_id', $dapertement_id)->with('dapertement')->with('ticket')->orderBy('id', 'DESC')->get();

            return response()->json([
                'message' => 'success',
                'data' => $action
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'failed',
                'data' => $ex,
                'request' => $request
            ]);
        }
   }

   public function edit(Request $request)
   {
       try {
            $rules=array(
                'action_id' => 'required',
                'description' => 'required',
            );

            $validator=\Validator::make($request->all(),$rules);
            if($validator->fails())
            {
                $messages=$validator->messages();
                $errors=$messages->all();
                return response()->json([
                    'message' => $errors,
                    'data' => $request->all()
                ]);
            }

            $action = ActionApi::findOrFail($request->action_id);

            $action->update($request->all());

            return response()->json([
                'message' => 'Data Category Update Success',
                'data' => $action
            ]);
       } catch (QueryException $ex) {
            return response()->json([
                'message' => $ex,
                'data' => $request->all()
            ]);
       }
   }

   public function liststaff($ticket_id)
    {
        try {
            $actions = ActionApi::with('staff')->with('dapertement')->with('ticket')->where('ticket_id', $ticket_id)->orderBy('start', 'desc')->get();
            return response()->json([
              'message' => 'Data Ticket',
              'data' => $actions
            ]);
          }catch (QueryException $ex) {
            return response()->json([
              'message' => 'Gagal Mengambil data',
              'data' => $ex
            ]);
          }
        
    }

    public function ticket($ticket_id)
    {
        try {
            $ticket = TicketApi::with('ticket_image')->with('customer')->with('category')->where('id', $ticket_id)->orderBy('id', 'DESC')->first();
            return response()->json([
              'message' => 'Data Ticket',
              'data' => $ticket
            ]);
        } catch (QueryException $ex) {
            return response()->json([
                'message' => 'Gagal Mengambil data',
                'data' => $ex
              ]);
        }
    }
}
