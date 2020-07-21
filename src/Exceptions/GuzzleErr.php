<?php

namespace Guzzper\Exceptions;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class exOldPassword
 * @package App\Exceptions
 */
class GuzzleErr extends \Exception
{
    protected $message;
    protected $code;
    protected $trace;

    public function __construct($message = "", $code = 0, $trace = "", Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->message = $message;
        $this->code = $code;
        $this->trace = $trace;

    }

    /**
     * @param Request $request
     * @return Response
     */
    function render(Request $request)
    {
        return new Response(
            json_encode(
                [
                    'appErrCode' => "SOMETHING_WENT_WRONG",
                    'errors' => $this->getMessage()
                ]
            ),
            $this->code,
            ['content-type' => 'application/json']
        );
    }

}
