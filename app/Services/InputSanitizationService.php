<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Input sanitization and validation service
 *
 * Implements protections against:
 * - A03:2021 Injection (XSS, SQL, Script)
 * - Input data manipulation
 * - Malicious content injection
 * - Unauthorized code execution
 */
class InputSanitizationService
{
    /**
     * Malicious patterns detected in inputs
     */
    private const MALICIOUS_PATTERNS = [
        'sql_injection' => [
            '/(\bunion\b.*\bselect\b)|(\bselect\b.*\bunion\b)/i',
            '/\b(insert|update|delete|drop|create|alter|exec|execute)\b/i',
            '/(\bor\b|\band\b).*[\'"].*[\'"].*(\bor\b|\band\b)/i',
            '/[\'"].*(\bor\b|\band\b).*[\'"].*=/i',
        ],
        'xss_injection' => [
            '/<script[^>]*>.*?<\/script>/is',
            '/<iframe[^>]*>.*?<\/iframe>/is',
            '/javascript:|vbscript:|onload=|onerror=|onclick=/i',
            '/<object[^>]*>.*?<\/object>/is',
            '/<embed[^>]*>.*?<\/embed>/is',
        ],
        'code_injection' => [
            '/eval\s*\(/i',
            '/exec\s*\(/i',
            '/system\s*\(/i',
            '/shell_exec\s*\(/i',
            '/passthru\s*\(/i',
        ],
        'path_traversal' => [
            '/\.\.\/|\.\.\\\\/',
            '/\.\.\%2f|\.\.\%5c/i',
            '/\%252e\%252e\%252f/i',
        ],
    ];

    /**
     * Custom validation rules by field type
     */
    private const FIELD_VALIDATION_RULES = [
        'email' => [
            'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            'max_length' => 255,
            'required_format' => true,
        ],
        'username' => [
            'pattern' => '/^[a-zA-Z0-9._-]{3,20}$/',
            'max_length' => 20,
            'min_length' => 3,
        ],
        'password' => [
            'min_length' => 8,
            'max_length' => 128,
            'require_special_chars' => true,
            'require_numbers' => true,
            'require_uppercase' => true,
        ],
        'title' => [
            'max_length' => 255,
            'strip_tags' => true,
            'encode_html' => true,
        ],
        'description' => [
            'max_length' => 1000,
            'allowed_tags' => ['p', 'br', 'strong', 'em', 'u'],
            'encode_html' => true,
        ],
    ];

    /**
     * Sanitize and validate a data array
     *
     * @param array $data The data to sanitize
     * @param array $rules Optional validation rules
     * @return array The sanitized and validated data
     * @throws \InvalidArgumentException If malicious data is detected
     */
    public function sanitizeAndValidate(array $data, array $rules = []): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $sanitized[$key] = $this->sanitizeValue($value, $key, $rules[$key] ?? []);
        }

        $this->detectMaliciousContent($sanitized);

        return $sanitized;
    }

    /**
     * Sanitize an individual value
     *
     * @param mixed $value The value to sanitize
     * @param string $fieldName The field name
     * @param array $fieldRules Field-specific rules
     * @return mixed The sanitized value
     */
    public function sanitizeValue($value, string $fieldName = '', array $fieldRules = [])
    {
        if (is_array($value)) {
            return $this->sanitizeArray($value, $fieldName);
        }

        if (!is_string($value)) {
            return $value;
        }

        $value = $this->basicSanitization($value);
        $value = $this->applyFieldSpecificRules($value, $fieldName, $fieldRules);

        return $value;
    }

    /**
     * Perform basic sanitization on a string
     *
     * @param string $value
     * @return string
     */
    private function basicSanitization(string $value): string
    {
        $value = trim($value);

        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

        $value = str_replace(["\0", "\r"], '', $value);

        if (mb_strlen($value) > config('security.input_sanitization.max_string_length', 1000)) {
            $value = mb_substr($value, 0, config('security.input_sanitization.max_string_length', 1000));
        }

        return $value;
    }

    /**
     * Apply field-specific rules
     *
     * @param string $value
     * @param string $fieldName
     * @param array $customRules
     * @return string
     */
    private function applyFieldSpecificRules(string $value, string $fieldName, array $customRules): string
    {
        $rules = array_merge(
            self::FIELD_VALIDATION_RULES[$fieldName] ?? [],
            $customRules
        );

        if (isset($rules['max_length']) && mb_strlen($value) > $rules['max_length']) {
            $value = mb_substr($value, 0, $rules['max_length']);
        }

        if (isset($rules['min_length']) && mb_strlen($value) < $rules['min_length']) {
            throw new \InvalidArgumentException("Field {$fieldName} must contain at least {$rules['min_length']} characters");
        }

        if (isset($rules['pattern']) && !preg_match($rules['pattern'], $value)) {
            throw new \InvalidArgumentException("Field {$fieldName} does not meet the required format");
        }

        if ($rules['strip_tags'] ?? false) {
            $allowedTags = $rules['allowed_tags'] ?? config('security.input_sanitization.allowed_html_tags', []);
            $value = strip_tags($value, '<' . implode('><', $allowedTags) . '>');
        }

        if ($rules['encode_html'] ?? config('security.input_sanitization.encode_html_entities', true)) {
            $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $value;
    }

    /**
     * Recursively sanitize an array
     *
     * @param array $array
     * @param string $context
     * @param int $depth
     * @return array
     */
    private function sanitizeArray(array $array, string $context = '', int $depth = 0): array
    {
        $maxDepth = config('security.input_sanitization.max_array_depth', 10);

        if ($depth > $maxDepth) {
            throw new \InvalidArgumentException('Array depth exceeded (possible denial of service attack)');
        }

        $sanitized = [];

        foreach ($array as $key => $value) {
            $sanitizedKey = $this->sanitizeValue($key, 'array_key');

            if (is_array($value)) {
                $sanitized[$sanitizedKey] = $this->sanitizeArray($value, $context, $depth + 1);
            } else {
                $sanitized[$sanitizedKey] = $this->sanitizeValue($value, $context);
            }
        }

        return $sanitized;
    }

    /**
     * Detect malicious content in data
     *
     * @param array $data
     * @return void
     * @throws \InvalidArgumentException If malicious content is detected
     */
    private function detectMaliciousContent(array $data): void
    {
        $flatData = $this->flattenArray($data);

        foreach ($flatData as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            foreach (self::MALICIOUS_PATTERNS as $category => $patterns) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        $this->logSecurityThreat($category, $key, $value, $pattern);
                        throw new \InvalidArgumentException("Potentially malicious content detected in field: {$key}");
                    }
                }
            }
        }
    }

    /**
     * Flatten a multidimensional array
     *
     * @param array $array
     * @param string $prefix
     * @return array
     */
    private function flattenArray(array $array, string $prefix = ''): array
    {
        $flattened = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $flattened = array_merge($flattened, $this->flattenArray($value, $newKey));
            } else {
                $flattened[$newKey] = $value;
            }
        }

        return $flattened;
    }

    /**
     * Validate password strength
     *
     * @param string $password
     * @return array Validation result with score and suggestions
     */
    public function validatePasswordStrength(string $password): array
    {
        $score = 0;
        $suggestions = [];
        $rules = self::FIELD_VALIDATION_RULES['password'];

        if (strlen($password) >= $rules['min_length']) {
            $score += 20;
        } else {
            $suggestions[] = "Password must contain at least {$rules['min_length']} characters";
        }

        if (preg_match('/[A-Z]/', $password)) {
            $score += 20;
        } else {
            $suggestions[] = 'Add at least one uppercase letter';
        }

        if (preg_match('/[a-z]/', $password)) {
            $score += 20;
        } else {
            $suggestions[] = 'Add at least one lowercase letter';
        }

        if (preg_match('/[0-9]/', $password)) {
            $score += 20;
        } else {
            $suggestions[] = 'Add at least one digit';
        }

        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $score += 20;
        } else {
            $suggestions[] = 'Add at least one special character';
        }

        return [
            'score' => $score,
            'strength' => $this->getPasswordStrengthLevel($score),
            'suggestions' => $suggestions,
            'is_valid' => $score >= 80,
        ];
    }

    /**
     * Determine password strength level
     *
     * @param int $score
     * @return string
     */
    private function getPasswordStrengthLevel(int $score): string
    {
        if ($score >= 80) return 'Strong';
        if ($score >= 60) return 'Medium';
        if ($score >= 40) return 'Weak';
        return 'Very weak';
    }

    /**
     * Log a detected security threat
     *
     * @param string $category
     * @param string $field
     * @param string $value
     * @param string $pattern
     * @return void
     */
    private function logSecurityThreat(string $category, string $field, string $value, string $pattern): void
    {
        $context = [
            'threat_type' => $category,
            'field' => $field,
            'pattern_matched' => $pattern,
            'value_length' => strlen($value),
            'value_hash' => hash('sha256', $value),
            'ip' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'user_id' => Auth::id(),
            'timestamp' => now()->toISOString(),
        ];

        Log::channel(config('security.logging.security_log_channel', 'stack'))
           ->warning("Security threat detected: {$category}", $context);
    }

    /**
     * Generate a secure token
     *
     * @param int $length
     * @return string
     */
    public function generateSecureToken(int $length = 32): string
    {
        return Str::random($length);
    }

    /**
     * Validate and sanitize a URL
     *
     * @param string $url
     * @return string|null The sanitized URL or null if invalid
     */
    public function sanitizeUrl(string $url): ?string
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parsedUrl = parse_url($url);

        if (!isset($parsedUrl['scheme']) || !in_array($parsedUrl['scheme'], ['http', 'https'])) {
            return null;
        }

        return $url;
    }
}
