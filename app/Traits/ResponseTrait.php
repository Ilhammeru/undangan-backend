<?php

namespace App\Traits;

trait ResponseTrait {
    public function sendResponse($data, $status = 200, $message = 'SUCCESS') {
        return response()->json(
            ['data' => $data, 'message' => $message],
            $status
        );
    }

    public function sendFailedResponse($data, $status = 500, $message = 'FAILED') {
        return response()->json(
            ['data' => $data, 'message' => $message],
            $status
        );
    }

    public function sendFailedValidation($data, $status = 500, $message = 'VALIDATION_FAILED') {
        return response()->json(
            ['data' => $data, 'message' => $message],
            $status
        );
    }
}