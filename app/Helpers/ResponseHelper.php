<?php

namespace App\Helpers;

trait ResponseHelper
{
    public function output($code, $data = null, $message = null)
    {
        $result = [
            'code' => $code
        ];

        $data != null ? $result['data'] = $data :  null;

        return response()->json($data, $code);
    }
}
