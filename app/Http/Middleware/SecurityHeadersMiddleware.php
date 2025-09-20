<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security Headers Middleware
 *
 * Implements essential HTTP security headers to protect against:
 * - A05:2021 Security Misconfiguration
 * - XSS attacks
 * - Clickjacking
 * - MIME type sniffing
 * - Information disclosure
 */
class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Apply security headers
        $this->addSecurityHeaders($response);

        return $response;
    }

    /**
     * Add essential security headers to the response
     *
     * @param Response $response
     * @return void
     */
    private function addSecurityHeaders(Response $response): void
    {
        $headers = $this->getSecurityHeaders();

        foreach ($headers as $header => $value) {
            $response->headers->set($header, $value);
        }

        // Remove server information headers
        if (config('security.api_headers.remove_server_header', true)) {
            $response->headers->remove('Server');
        }

        if (config('security.api_headers.remove_x_powered_by', true)) {
            $response->headers->remove('X-Powered-By');
        }
    }

    /**
     * Get the security headers configuration
     *
     * @return array
     */
    private function getSecurityHeaders(): array
    {
        $config = config('security.api_headers', []);
        $cspConfig = config('security.csp', []);

        return [
            // Prevent MIME type sniffing
            'X-Content-Type-Options' => $config['x_content_type_options'] ?? 'nosniff',

            // Prevent clickjacking
            'X-Frame-Options' => $config['x_frame_options'] ?? 'DENY',

            // XSS Protection (legacy, but still useful)
            'X-XSS-Protection' => $config['x_xss_protection'] ?? '1; mode=block',

            // Control referrer information
            'Referrer-Policy' => $config['referrer_policy'] ?? 'strict-origin-when-cross-origin',

            // Feature Policy / Permissions Policy
            'Permissions-Policy' => $config['permissions_policy'] ?? 'camera=(), microphone=(), geolocation=()',

            // Content Security Policy
            'Content-Security-Policy' => $this->buildContentSecurityPolicy($cspConfig),

            // HSTS (if HTTPS is enforced)
            ...$this->getHstsHeaders(),

            // Additional API security headers
            'X-API-Version' => config('app.version', '1.0'),
            'X-Request-ID' => request()->header('X-Request-ID', uniqid()),
        ];
    }

    /**
     * Build Content Security Policy header value
     *
     * @param array $cspConfig
     * @return string
     */
    private function buildContentSecurityPolicy(array $cspConfig): string
    {
        $directives = [];

        $cspDirectives = [
            'default-src' => $cspConfig['default_src'] ?? "'self'",
            'script-src' => $cspConfig['script_src'] ?? "'self'",
            'style-src' => $cspConfig['style_src'] ?? "'self' 'unsafe-inline'",
            'img-src' => $cspConfig['img_src'] ?? "'self' data: https:",
            'font-src' => $cspConfig['font_src'] ?? "'self'",
            'connect-src' => $cspConfig['connect_src'] ?? "'self'",
            'media-src' => $cspConfig['media_src'] ?? "'none'",
            'object-src' => $cspConfig['object_src'] ?? "'none'",
            'child-src' => $cspConfig['child_src'] ?? "'none'",
            'frame-ancestors' => $cspConfig['frame_ancestors'] ?? "'none'",
            'form-action' => $cspConfig['form_action'] ?? "'self'",
            'base-uri' => $cspConfig['base_uri'] ?? "'self'",
        ];

        foreach ($cspDirectives as $directive => $value) {
            $directives[] = "{$directive} {$value}";
        }

        return implode('; ', $directives);
    }

    /**
     * Get HSTS headers if HTTPS is enabled
     *
     * @return array
     */
    private function getHstsHeaders(): array
    {
        if (!config('security.force_https', false)) {
            return [];
        }

        $maxAge = config('security.hsts_max_age', 31536000);
        $includeSubdomains = config('security.hsts_include_subdomains', true);
        $preload = config('security.hsts_preload', true);

        $hstsValue = "max-age={$maxAge}";

        if ($includeSubdomains) {
            $hstsValue .= '; includeSubDomains';
        }

        if ($preload) {
            $hstsValue .= '; preload';
        }

        return [
            'Strict-Transport-Security' => $hstsValue,
        ];
    }
}
