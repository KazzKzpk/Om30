<?php

namespace App\Exceptions;

use App\Models\UserAddress;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

        // Update 404 status to 400 (api's)
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                if ($e->getPrevious() instanceof ModelNotFoundException) {
                    $modelNotFound = $e->getPrevious();
                    if ($modelNotFound->getModel() === User::class) {
                        return response()->json([
                            'error' => 'User not found.'
                        ], Response::HTTP_BAD_REQUEST);
                    }
                    elseif ($modelNotFound->getModel() === UserAddress::class) {
                        return response()->json([
                            'error' => 'Address not found.'
                        ], Response::HTTP_BAD_REQUEST);
                    }
                }
            }
        });
    }
}
