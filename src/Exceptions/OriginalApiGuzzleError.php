<?php

namespace Guzzper\Exceptions;


use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;


class OriginalApiGuzzleError extends Exception
{
    protected $message;
    protected $code;
    protected $trace;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->message = $message;
        $this->code = $code;
    }

    /**
     * @param Request $request
     * @return Response
     */
    function render(Request $request)
    {
        Log::error($this->message);
        $thirdPartyMessage  = json_decode($this->message,true);

        return new Response(
            json_encode(
                [
                    'appErrCode' => "THIRD_PARTY_APPLICATION_ERROR",
                    'errors' => $thirdPartyMessage
                ]
            ),
            $this->code,
            ['content-type' => 'application/json']
        );
    }
}
