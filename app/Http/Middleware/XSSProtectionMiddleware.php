<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\InputSanitizationService;
use Illuminate\Support\Facades\Log;

/**
 * XSS Protection Middleware
 *
 * This middleware provides protection against various injection attacks
 * including XSS, SQL injection, and script injection attempts.
 * Part of OWASP A03:2021 Injection protection.
 */
class XSSProtectionMiddleware
{
    /**
     * The input sanitization service instance
     */
    private InputSanitizationService $sanitizationService;

    /**
     * Patterns to detect potentially malicious content
     */
    private array $maliciousPatterns = [
        '/<script[^>]*>.*?<\/script>/si',
        '/javascript:/i',
        '/vbscript:/i',
        '/onload=/i',
        '/onerror=/i',
        '/onclick=/i',
        '/onmouseover=/i',
        '/eval\s*\(/i',
        '/expression\s*\(/i',
        '/union\s+select/i',
        '/drop\s+table/i',
        '/delete\s+from/i',
        '/insert\s+into/i',
        '/update\s+.*set/i',
    ];

    /**
     * Create a new middleware instance
     */
    public function __construct(InputSanitizationService $sanitizationService)
    {
        $this->sanitizationService = $sanitizationService;
    }

    /**
     * Handle an incoming request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Validate and sanitize request headers
        $this->validateRequestHeaders($request);

        // Sanitize input data
        $this->sanitizeRequestData($request);

        // Check for malicious patterns
        if ($this->detectMaliciousPatterns($request)) {
            Log::channel('security')->warning('Malicious patterns detected in request', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);

            return response()->json([
                'error' => 'Invalid request detected',
                'message' => 'Your request contains potentially dangerous content'
            ], 400);
        }

        return $next($request);
    }

    /**
     * Sanitize request data to prevent injection attacks
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    private function sanitizeRequestData(Request $request): void
    {
        $input = $request->all();

        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $request->merge([
                    $key => $this->sanitizationService->sanitizeValue($value, $key)
                ]);
            } elseif (is_array($value)) {
                $sanitized = [];
                foreach ($value as $subKey => $subValue) {
                    $sanitized[$subKey] = $this->sanitizationService->sanitizeValue($subValue, $subKey);
                }
                $request->merge([
                    $key => $sanitized
                ]);
            }
        }
    }

    /**
     * Validate request headers for suspicious content
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    private function validateRequestHeaders(Request $request): void
    {
        $suspiciousHeaders = [
            'X-Forwarded-For',
            'X-Real-IP',
            'User-Agent',
            'Referer'
        ];

        foreach ($suspiciousHeaders as $header) {
            $value = $request->header($header);
            if ($value && $this->containsMaliciousPattern($value)) {
                Log::channel('security')->warning('Suspicious header detected', [
                    'header' => $header,
                    'value' => $value,
                    'ip' => $request->ip(),
                ]);
            }
        }
    }

    /**
     * Detect malicious patterns in the request
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    private function detectMaliciousPatterns(Request $request): bool
    {
        // Check all input data
        $allData = array_merge(
            $request->all(),
            [$request->getPathInfo(), $request->getQueryString()]
        );

        foreach ($allData as $data) {
            if (is_string($data) && $this->containsMaliciousPattern($data)) {
                return true;
            }
            if (is_array($data) && $this->checkArrayForMaliciousPatterns($data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a string contains malicious patterns
     *
     * @param string $input
     * @return bool
     */
    private function containsMaliciousPattern(string $input): bool
    {
        foreach ($this->maliciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Recursively check array for malicious patterns
     *
     * @param array $data
     * @return bool
     */
    private function checkArrayForMaliciousPatterns(array $data): bool
    {
        foreach ($data as $value) {
            if (is_string($value) && $this->containsMaliciousPattern($value)) {
                return true;
            }
            if (is_array($value) && $this->checkArrayForMaliciousPatterns($value)) {
                return true;
            }
        }
        return false;
    }
}
