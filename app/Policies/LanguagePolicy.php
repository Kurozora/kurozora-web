<?php

namespace App\Policies;

use App\Models\Language;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class LanguagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user): Response|bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Language $language
     * @return Response|bool
     */
    public function view(User $user, Language $language): Response|bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return $user->can('createLanguage');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Language $language
     * @return Response|bool
     */
    public function update(User $user, Language $language): Response|bool
    {
        return $user->can('updateLanguage');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Language $language
     * @return Response|bool
     */
    public function delete(User $user, Language $language): Response|bool
    {
        return $user->can('deleteLanguage');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Language $language
     * @return Response|bool
     */
    public function restore(User $user, Language $language): Response|bool
    {
        return $user->can('restoreLanguage');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Language $language
     * @return Response|bool
     */
    public function forceDelete(User $user, Language $language): Response|bool
    {
        return $user->can('forceDeleteLanguage');
    }
}
