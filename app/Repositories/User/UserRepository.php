<?php

namespace App\Repositories\User;

use App\Components\Repository;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * User repository for handling user data operations
 *
 * Provides methods for user management including profile updates,
 * file handling, and relationship loading
 */
class UserRepository extends Repository
{
    /**
     * The relations to eager load on every query
     *
     * @var array
     */
    protected $with = [
        'role',
        'team',
        'organization',
        'subscriptionPlan'
    ];

    /**
     * UserRepository constructor
     */
    public function __construct()
    {
        parent::__construct(new User());
    }

    /**
     * Get all users with relationships
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return $this->model->with($this->with)->get();
    }

    /**
     * Show a specific user with relationships
     *
     * @param int $id
     * @return Model
     */
    public function show($id)
    {
        return $this->model->with($this->with)->findOrFail($id);
    }

    /**
     * Store a newly created user in storage
     *
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->model::create($data);
    }

    /**
     * Update the specified user in storage
     *
     * @param array $data
     * @param int $id
     * @return Model
     */
    public function update($data, $id)
    {
        $user = $this->model::findOrFail($id);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return $user->load($this->with);
    }

    /**
     * Update user profile with file handling
     *
     * @param array $data
     * @param int $userId
     * @return Model
     */
    public function updateProfile(array $data, int $userId): Model
    {
        $user = $this->model::findOrFail($userId);

        // Handle password update if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Handle profile photo upload if provided
        if (isset($data['profile_photo']) && $data['profile_photo'] instanceof \Illuminate\Http\UploadedFile) {
            // Delete old photo if exists
            if ($user->profile_photo) {
                Storage::disk('minio')->delete($user->profile_photo);
            }

            $file = $data['profile_photo'];
            $filename = 'profile_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = Storage::disk('minio')->putFileAs('', $file, $filename);
            $data['profile_photo'] = $path;
        }

        $user->update($data);

        return $user->load($this->with);
    }

    /**
     * Get user profile photo URL
     *
     * @param Model $user
     * @return string|null
     */
    public function getProfilePhotoUrl(Model $user): ?string
    {
        if (!$user->profile_photo) {
            return null;
        }

        try {
            return Storage::disk('minio')->path($user->profile_photo);
        } catch (\Exception $e) {
            return null;
        }
    }
}
