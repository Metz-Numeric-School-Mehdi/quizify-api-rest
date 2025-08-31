<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Role-based access control middleware for resources and ownership validation
 *
 * Implements protections against:
 * - A01:2021 Broken Access Control
 * - Privilege escalation
 * - Unauthorized resource access
 * - Direct object reference manipulation
 */
class RoleBasedAccessControl
{
    /**
     * Actions allowed for unauthenticated users
     */
    private const PUBLIC_ACTIONS = [
        'index',
        'show',
        'search'
    ];

    /**
     * Actions requiring administrative permissions
     */
    private const ADMIN_ACTIONS = [
        'massDelete',
        'restore',
        'forceDelete',
        'assignBadges'
    ];

    /**
     * Handle access control for the request
     *
     * @param Request $request The HTTP request
     * @param Closure $next The next middleware
     * @param string|null $permission Specific permission required
     * @return Response
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $action = $this->extractActionFromRoute($request);

        if ($this->isPublicAction($action)) {
            return $next($request);
        }

        if (!Auth::check()) {
            return $this->unauthorizedResponse('Authentification requise');
        }

        $user = Auth::user();

        if ($this->requiresAdminAccess($action) && !$this->hasAdminRole($user)) {
            return $this->forbiddenResponse('Privilèges administrateur requis');
        }

        if ($permission && !$this->hasPermission($user, $permission)) {
            return $this->forbiddenResponse("Permission '{$permission}' requise");
        }

        if ($this->requiresOwnershipCheck($request, $action)) {
            if (!$this->isResourceOwner($request, $user)) {
                return $this->forbiddenResponse('Accès autorisé uniquement au propriétaire');
            }
        }

        return $next($request);
    }

    /**
     * Extract action from current route
     *
     * @param Request $request
     * @return string|null
     */
    private function extractActionFromRoute(Request $request): ?string
    {
        $route = $request->route();
        if (!$route) {
            return null;
        }

        $action = $route->getActionMethod();
        return $action !== 'Closure' ? $action : null;
    }

    /**
     * Check if action is public
     *
     * @param string|null $action
     * @return bool
     */
    private function isPublicAction(?string $action): bool
    {
        return $action === null || in_array($action, self::PUBLIC_ACTIONS);
    }

    /**
     * Check if action requires administrative privileges
     *
     * @param string|null $action
     * @return bool
     */
    private function requiresAdminAccess(?string $action): bool
    {
        return $action && in_array($action, self::ADMIN_ACTIONS);
    }

    /**
     * Check if user has administrative role
     *
     * @param mixed $user
     * @return bool
     */
    private function hasAdminRole($user): bool
    {
        return $user &&
               $user->role &&
               in_array($user->role->name, ['admin', 'super_admin']);
    }

    /**
     * Check if user has specific permission
     *
     * @param mixed $user
     * @param string $permission
     * @return bool
     */
    private function hasPermission($user, string $permission): bool
    {
        return Gate::forUser($user)->allows($permission);
    }

    /**
     * Determine if ownership check is necessary
     *
     * @param Request $request
     * @param string|null $action
     * @return bool
     */
    private function requiresOwnershipCheck(Request $request, ?string $action): bool
    {
        $ownershipActions = ['show', 'update', 'destroy'];

        return $action &&
               in_array($action, $ownershipActions) &&
               $this->hasResourceId($request);
    }

    /**
     * Check if request contains a resource ID
     *
     * @param Request $request
     * @return bool
     */
    private function hasResourceId(Request $request): bool
    {
        $route = $request->route();
        return $route && $route->hasParameter('id');
    }

    /**
     * Check if user is resource owner
     *
     * @param Request $request
     * @param mixed $user
     * @return bool
     */
    private function isResourceOwner(Request $request, $user): bool
    {
        $resourceId = $request->route('id');
        $resourceType = $this->extractResourceType($request);

        if (!$resourceId || !$resourceType) {
            return false;
        }

        try {
            $modelClass = $this->getModelClass($resourceType);
            $resource = $modelClass::findOrFail($resourceId);

            return $this->checkOwnership($resource, $user);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Extract resource type from route
     *
     * @param Request $request
     * @return string|null
     */
    private function extractResourceType(Request $request): ?string
    {
        $uri = $request->route()->uri();
        $segments = explode('/', $uri);

        foreach ($segments as $segment) {
            if (!str_contains($segment, '{') && $segment !== 'api') {
                return rtrim($segment, 's');
            }
        }

        return null;
    }

    /**
     * Get model class corresponding to resource type
     *
     * @param string $resourceType
     * @return string
     */
    private function getModelClass(string $resourceType): string
    {
        $modelName = ucfirst($resourceType);
        return "App\\Models\\{$modelName}";
    }

    /**
     * Check resource ownership
     *
     * @param mixed $resource
     * @param mixed $user
     * @return bool
     */
    private function checkOwnership($resource, $user): bool
    {
        if (property_exists($resource, 'user_id')) {
            return $resource->user_id === $user->id;
        }

        if (method_exists($resource, 'user')) {
            return $resource->user?->id === $user->id;
        }

        if (property_exists($resource, 'created_by')) {
            return $resource->created_by === $user->id;
        }

        return false;
    }

    /**
     * Return 401 Unauthorized error response
     *
     * @param string $message
     * @return Response
     */
    private function unauthorizedResponse(string $message): Response
    {
        return response()->json([
            'error' => 'Unauthorized',
            'message' => $message,
            'code' => 401
        ], 401);
    }

    /**
     * Return 403 Forbidden error response
     *
     * @param string $message
     * @return Response
     */
    private function forbiddenResponse(string $message): Response
    {
        return response()->json([
            'error' => 'Forbidden',
            'message' => $message,
            'code' => 403
        ], 403);
    }
}
