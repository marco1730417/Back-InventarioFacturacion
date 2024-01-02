<?php

namespace App\Traits;

trait ApiResponse
{

    public function successResponse($data, $code = 200, $msj = '')
    {
        return response()->json(array("data" => $data, "status" => $code, "msj" => $msj), $code, ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    public function errorResponse($data, $code = 404, $msj = '')
    {
        return response()->json(array("data" => $data, "status" => $code, "msj" => $msj), $code);
    }

}
