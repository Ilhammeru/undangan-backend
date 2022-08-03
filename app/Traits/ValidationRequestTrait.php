<?php

namespace App\Traits;

use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait ValidationRequestTrait {
    use ResponseTrait;

    public function validateReq(Request $request, $rules, $messages) {
        $validation = Validator::make(
            $request->all(),
            $rules,
            $messages
        );
        if ($validation->fails()) {
            $error = $validation->errors()->all();
            return [
                'error' => $error
            ];
        } else {
            return true;
        }
    }
}