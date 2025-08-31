<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Secure exception handler for Quizify API
 *
 * Implements protections against:
 * - A05:2021 Security Misconfiguration
 * - Sensitive information exposure
 * - Stack traces in production
 * - Secure error logging
 */
class SecureApiExceptionHandler extends ExceptionHandler
{
    /**
     * Exceptions that should not be reported in logs
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        ValidationException::class,
        AuthenticationException::class,
    ];

    /**
     * Input fields that should never be displayed in exceptions
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'token',
        'api_key',
        'secret',
    ];

    /**
     * Record the exception securely
     *
     * @param Throwable $e The exception to record
     * @return void
     */
    public function report(Throwable $e): void
    {
        if ($this->shouldReport($e)) {
            $this->logSecureException($e);
        }

        parent::report($e);
    }

    /**
     * Render a secure exception response for HTTP requests
     *
     * @param Request $request The HTTP request
     * @param Throwable $e The raised exception
     * @return Response The secure response
     */
    public function render($request, Throwable $e): Response
    {
        if ($request->expectsJson()) {
            return $this->renderJsonException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Render a secure JSON response for the exception
     *
     * @param Request $request
     * @param Throwable $e
     * @return Response
     */
    private function renderJsonException(Request $request, Throwable $e): Response
    {
        $status = $this->getExceptionStatus($e);
        $message = $this->getSecureErrorMessage($e, $status);

        $response = [
            'error' => class_basename($e),
            'message' => $message,
            'code' => $status,
            'timestamp' => now()->toISOString(),
        ];

        if (app()->isLocal() && config('app.debug')) {
            $response['debug'] = $this->getDebugInformation($e);
        }

        $this->logSecurityEvent($request, $e, $status);

        return response()->json($response, $status);
    }

    /**
     * Get the appropriate HTTP status code for the exception
     *
     * @param Throwable $e
     * @return int
     */
    private function getExceptionStatus(Throwable $e): int
    {
        if ($e instanceof HttpException) {
            return $e->getStatusCode();
        }

        if ($e instanceof AuthenticationException) {
            return 401;
        }

        if ($e instanceof ValidationException) {
            return 422;
        }

        return 500;
    }

    /**
     * Generate a secure error message according to context
     *
     * @param Throwable $e
     * @param int $status
     * @return string
     */
    private function getSecureErrorMessage(Throwable $e, int $status): string
    {
        if (app()->isProduction() && config('security.error_handling.hide_error_details_in_production')) {
            return $this->getGenericErrorMessage($status);
        }

        if ($e instanceof ValidationException) {
            return 'Invalid validation data';
        }

        if ($e instanceof AuthenticationException) {
            return 'Authentication required';
        }

        if ($e instanceof HttpException) {
            return $e->getMessage() ?: $this->getGenericErrorMessage($status);
        }

        return $this->getGenericErrorMessage($status);
    }

    /**
     * Return a generic error message according to status code
     *
     * @param int $status
     * @return string
     */
    private function getGenericErrorMessage(int $status): string
    {
        $messages = [
            400 => 'Invalid request',
            401 => 'Authentication required',
            403 => 'Access forbidden',
            404 => 'Resource not found',
            422 => 'Invalid validation data',
            429 => 'Too many requests',
            500 => 'Internal server error',
            503 => 'Service temporarily unavailable',
        ];

        return $messages[$status] ?? config('security.error_handling.generic_error_message');
    }

    /**
     * Generate secure debug information
     *
     * @param Throwable $e
     * @return array
     */
    private function getDebugInformation(Throwable $e): array
    {
        $debug = [
            'exception' => get_class($e),
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
        ];

        if (config('security.error_handling.include_trace_in_logs')) {
            $debug['trace'] = collect($e->getTrace())
                ->take(10)
                ->map(function ($trace) {
                    return [
                        'file' => isset($trace['file']) ? basename($trace['file']) : 'unknown',
                        'line' => $trace['line'] ?? 'unknown',
                        'function' => $trace['function'] ?? 'unknown',
                    ];
                })
                ->toArray();
        }

        return $debug;
    }

    /**
     * Log the exception securely in logs
     *
     * @param Throwable $e
     * @return void
     */
    private function logSecureException(Throwable $e): void
    {
        $context = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'user_id' => Auth::id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ];

        if (config('security.error_handling.include_trace_in_logs')) {
            $context['trace'] = $e->getTraceAsString();
        }

        Log::channel(config('security.logging.security_log_channel', 'stack'))
           ->error('Exception capturée', $context);
    }

    /**
     * Log a security event
     *
     * @param Request $request
     * @param Throwable $e
     * @param int $status
     * @return void
     */
    private function logSecurityEvent(Request $request, Throwable $e, int $status): void
    {
        if (!config('security.logging.log_security_events')) {
            return;
        }

        $context = [
            'event_type' => 'exception_handled',
            'exception' => get_class($e),
            'status_code' => $status,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => Auth::id(),
            'timestamp' => now()->toISOString(),
        ];

        if ($this->isSuspiciousActivity($e, $status)) {
            $context['severity'] = 'high';
            $context['alert'] = true;

            if (config('security.logging.log_suspicious_activities')) {
                Log::channel(config('security.logging.security_log_channel', 'stack'))
                   ->warning('Activité suspecte détectée', $context);
            }

            if (config('security.logging.alert_on_security_breach')) {
                $this->sendSecurityAlert($context);
            }
        } else {
            Log::channel(config('security.logging.security_log_channel', 'stack'))
               ->info('Security event', $context);
        }
    }

    /**
     * Determine if the activity is suspicious
     *
     * @param Throwable $e
     * @param int $status
     * @return bool
     */
    private function isSuspiciousActivity(Throwable $e, int $status): bool
    {
        $suspiciousStatuses = [401, 403, 429];

        if (in_array($status, $suspiciousStatuses)) {
            return true;
        }

        $suspiciousPatterns = [
            'injection',
            'script',
            'eval',
            'union',
            'select',
            'drop',
            'delete',
            'update',
            'insert',
        ];

        $message = strtolower($e->getMessage());
        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Envoie une alerte de sécurité
     *
     * @param array $context
     * @return void
     */
    private function sendSecurityAlert(array $context): void
    {
        Log::channel('security')->critical('ALERTE SÉCURITÉ: Activité suspecte détectée', $context);
    }
}
