<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($request->is('api/*')) {
            // return response()->json([
            //     'message' => 'Record not found.'
            // ], 404);
            return $this->handleApiException($request, $e);
        }
        return parent::render($request, $e);
    }


    private function handleApiException($request, $e): \Illuminate\Http\JsonResponse
    {
        if ($e instanceof HttpResponseException) {
            $e = $e->getResponse();
        }

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            $e = $this->unauthenticated($request, $e);
        }

        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return response()->json($e->errors(), 422);
        }

        return $this->customApiResponse($e);
    }

    private function customApiResponse($e): \Illuminate\Http\JsonResponse
    {
        if (method_exists($e, 'getStatusCode')) {
            $statusCode = $e->getStatusCode();
        } else {
            $statusCode = 500;
        }
        $response = [];
        switch ($statusCode) {
            case 401:
                $response['message'] = 'Unauthorized';
                break;
            case 403:
                $response['message'] = 'Forbidden';
                break;
            case 404:
                $response['message'] = 'Not Found';
                break;
            case 405:
                $response['message'] = 'Method Not Allowed';
                break;
            case 422:
                $response['message'] = $e->original['message'];
                $response['errors'] = $e->original['errors'];
                break;
            default:
                $response['message'] = ($statusCode === 500) ? 'Whoops, looks like something went wrong' : $e->getMessage();
                break;
        }
        if (config('app.debug')) {
            //$response['trace'] = $e->getTrace();
            $response['code'] = $e->getCode();
        }
        $response['status'] = $statusCode;
        $response['error'] = $e->getMessage().' \n File :'.$e->getFile().' \n Line :'.$e->getLine();
        return response()->json($response, $statusCode);
    }
}
