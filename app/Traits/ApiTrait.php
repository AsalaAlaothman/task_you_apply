<?php

namespace App\Traits;
use Illuminate\Support\Facades\Validator;

trait ApiTrait {

    public $pagNumber = 10;

    public function apiResponse($status = 'ok' , $status_code = 200, $errors = null, $message = null, $result= null){
        $array = [
            'status' => $status_code,
            'status_code' =>$status,
            'errors' => $errors,
            'message' => $message,
            'result' => $result,
        ];

        return response($array,$status);
    }

    public function success(){
        return [
            200,201,202
        ];
    }

    public function notFound(){
        return $this->apiResponse(null,'Not Found',404);
    }

    public function err(){
        return $this->apiResponse(null ,'unkown error' , 520);
    }

    public function apiValidator($request,$array){
        $validate = Validator::make($request->all(),$array);

        if($validate->fails()){
            return $this->apiResponse(null,$validate->errors(),422);
        }
    }
}
