<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\HeroBanner;
use Illuminate\Auth\Access\HandlesAuthorization;

class HeroBannerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HeroBanner');
    }

    public function view(AuthUser $authUser, HeroBanner $heroBanner): bool
    {
        return $authUser->can('View:HeroBanner');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HeroBanner');
    }

    public function update(AuthUser $authUser, HeroBanner $heroBanner): bool
    {
        return $authUser->can('Update:HeroBanner');
    }

    public function delete(AuthUser $authUser, HeroBanner $heroBanner): bool
    {
        return $authUser->can('Delete:HeroBanner');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:HeroBanner');
    }

    public function restore(AuthUser $authUser, HeroBanner $heroBanner): bool
    {
        return $authUser->can('Restore:HeroBanner');
    }

    public function forceDelete(AuthUser $authUser, HeroBanner $heroBanner): bool
    {
        return $authUser->can('ForceDelete:HeroBanner');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:HeroBanner');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:HeroBanner');
    }

    public function replicate(AuthUser $authUser, HeroBanner $heroBanner): bool
    {
        return $authUser->can('Replicate:HeroBanner');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HeroBanner');
    }

}