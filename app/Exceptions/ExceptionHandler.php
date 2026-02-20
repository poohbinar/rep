<?php

namespace App\Exceptions;

use App\Exceptions\NotFoundException as SiteNotFoundHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ExceptionHandler
{
    public static function register($exceptions): void
    {
        // 404
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => __('Method :method Not Found', ['method' => $request->getRequestUri()]),
                    'code' => SymfonyResponse::HTTP_NOT_FOUND,
                ], SymfonyResponse::HTTP_NOT_FOUND);
            }
        });

        // 404 site
        $exceptions->render(function (SiteNotFoundHttpException $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], SymfonyResponse::HTTP_NOT_FOUND);

        });

        // 405
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'allowed_methods' => $e->getHeaders()['Allow'] ?? [],
                    'code' => SymfonyResponse::HTTP_METHOD_NOT_ALLOWED,
                ], SymfonyResponse::HTTP_METHOD_NOT_ALLOWED);
            }
        });

        // 401
        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], SymfonyResponse::HTTP_UNAUTHORIZED);
            }
        });

        // 422
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        });

        // 500
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson()) {

                if ($e instanceof ValidationException) {
                    return null;
                }
                if ($e instanceof HttpException && $e->getStatusCode() < 500) {
                    return null;
                }

                return response()->json([
                    'success' => false,
                    'message' => app()->isProduction()
                        ? __('Something went wrong. Please try again later.')
                        : $e->getMessage(),
                ], SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }
}
