<?php

use App\Http\Middleware\EnsureActiveAccount;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [EnsureActiveAccount::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['otp']);

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request): Response {
            if (! $response->isClientError() && ! $response->isServerError()) {
                return $response;
            }

            $contentType = $response->headers->get('Content-Type', 'text/html');
            if ($request->expectsJson() || ! str_starts_with($contentType, 'text/html') || $request->isMethod('HEAD')) {
                return $response;
            }

            $response->setContent(view('errors.maintenance')->render());
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
            $response->headers->remove('Content-Length');

            return $response;
        });
    })->create();
