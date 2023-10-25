<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Auth;
use App\Models\Review;

class ReviewController extends ApiController {

    public function __construct() {
        //we are checking for language code which will be sent in header of all the APIs
        if (isset(getallheaders()['lang']))
        \App::setLocale(getallheaders()['lang']);
    }


    /*
     * @author
     * @Param Null
     * @Function used for add review to the doctor
     * @return json
     */

    public function addreview(Request $request) {
        $input = $request->all();
        $validator = Validator::make($request->all(), [
                    'doctor_id' => 'required',
                    'rating' => 'required',
                    'booking_id' => 'required',
        ]);
        if ($this->validate($validator)) {
            try {
                $data = Review::where('patient_id', Auth::user()->id)
                                    ->where('doctor_id', $input['doctor_id'])
                                    ->where('booking_id', $input['booking_id'])
                                    ->first();
                if (empty($data)) {
                    $data = new Review();
                    $data->patient_id = Auth::user()->id;
                    $data->doctor_id = $input['doctor_id'];
                    $data->booking_id = $input['booking_id'];
                    $data->rating = $input['rating'];
                    $data->review = isset($input['review'])?$input['review']:'';
                    $data->save();

                    $this->status = "true";
                    $this->message = trans('api_common.review.add');
                }else{
                    $this->status = "false";
                    $this->message = trans('api_common.review.exist');
                }
            } catch (\Exception $ex) {
                $this->status = "false";
                $this->message = trans('api_common.somewrong');
                $this->data = [];
            }
        }
        $this->response();
    }

    /*
     * @author
     * @Param Null
     * @Function used for add comment on review
     * @return json
     */

    public function commentreview(Request $request) {
        $input = $request->all();
        $validator = Validator::make($request->all(), [
                    'review_id' => 'required',
                    'comment' => 'required',
        ]);
        if ($this->validate($validator)) {
            try {
                $data = Review::where('id', $input['review_id'])->first();
                if (!empty($data)) {
                    $data->comment = isset($input['comment'])?$input['comment']:'';
                    $data->save();
                    $data = Review::where('id',$input['review_id'])->with('patientInfo','bookingInfo','bookingInfo.doctorInfo')->first();
                    $this->status = "true";
                    $this->message = trans('api_common.review.comment');
                    $this->data = $data;
                }else{
                    $this->status = "false";
                    $this->message = trans('api_common.review.invalid_review');
                }
            } catch (\Exception $ex) {
                $this->status = "false";
                $this->message = trans('api_common.somewrong');
                $this->data = [];
            }
        }
        $this->response();
    }
    
    /*
     * @author
     * @Param Null
     * @Function used for doctor reviews list
     * @return json
     */
    
    public function getReviews(Request $request) {
        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required'
        ]);
        if ($this->validate($validator)) {
            try {
                $data = Review::where('doctor_id',$input['doctor_id'])->with('patientInfo','bookingInfo','bookingInfo.doctorInfo')->orderBy('id','desc')->paginate(10);
                if (isset($data[0])) {
                    $this->status = "true";
                    $this->message = trans('api_common.review.get');
                    $this->data = $data;
                } else {
                    $this->status = "true";
                    $this->message = trans('api_common.review.not_found');
                    $this->data = [];
                }
            } catch (\Exception $ex) {
                $this->status = "false";
                $this->message = trans('api_common.somewrong');
                $this->data = [];
            }
        }
        $this->response();
    }

}
