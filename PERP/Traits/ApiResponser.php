<?php

namespace PERP\Traits;

use Carbon\Carbon;

trait ApiResponser
{

    protected function token($personalAccessToken, $message = null, $code = 200)
    {
        $tokenData = [
            'access_token' => $personalAccessToken->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($personalAccessToken->token->expires_at)->toDateTimeString()
        ];

        return $this->success($tokenData, $message, $code);
    }

    protected function success($data, $message = null, $code = 200)
    {
        return response()->json([
            'status' => 'Success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function error($message = null, $code)
    {
        $mysqlErrorCodes = [
            "HY000",
            "42S22",
            "42S02",
        ];
        $code = !$code ? 412 : $code;
        $code = $code > 500 ? 412 : $code;
        $code = in_array($code, $mysqlErrorCodes) ? 500 : $code;

        $status = in_array($code, $mysqlErrorCodes) ? "Database Error" : "Error";

        $responseHeader = isset($_SERVER["SERVER_PROTOCOL"]) . " " . $code . " " . ucfirst($status);

        $headers = [
            $responseHeader,
        ];

        return response()->json(
            [
                'status' => $status,
                'message' => $message,
                'data' => null
            ],
            $code,
            $headers
        );
    }
}
