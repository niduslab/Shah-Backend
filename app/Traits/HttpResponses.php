<?php

namespace App\Traits;

trait HttpResponses {
  protected function success($data, $message = null, $code = 200) {
    return response()->json([
      "status" => "Request was successful",
      "data" => $data,
      "message" => $message,
    ], $code);
  }

  protected function error($data, $message = null, $code) {
    return response()->json([
      "status" => "Error has occured!",
      "data" => $data,
      "message" => $message,
    ], $code);
  }
}
