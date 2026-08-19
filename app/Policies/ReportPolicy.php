<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ReportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     *
     * @return Response|bool
     */
    public function viewAny(User $user): Response|bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User   $user
     * @param Report $report
     *
     * @return Response|bool
     */
    public function view(User $user, Report $report): Response|bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return $user->can('createReport');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User   $user
     * @param Report $report
     *
     * @return Response|bool
     */
    public function update(User $user, Report $report): Response|bool
    {
        return $user->can('updateReport');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User   $user
     * @param Report $report
     *
     * @return Response|bool
     */
    public function delete(User $user, Report $report): Response|bool
    {
        return $user->can('deleteReport');
    }
}
