<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Document;

class DocumentPolicy
{
    public function view(User $user, Document $doc): bool
    {
        if ($user->employee && $user->employee->id === $doc->employee_id) return true;
        if (method_exists($user,'hasRoleName') && ($user->hasRoleName('hr') || $user->hasRoleName('supervisor'))) return true;
        return false;
    }

    public function update(User $user, Document $doc): bool
    {
        if ($user->employee && $user->employee->id === $doc->employee_id) return true;
        if (method_exists($user,'hasRoleName') && $user->hasRoleName('hr')) return true;
        return false;
    }

    public function delete(User $user, Document $doc): bool
    {
        return $this->update($user, $doc);
    }

    public function create(User $user): bool
    {
        return (bool)($user->employee) || (method_exists($user,'hasRoleName') && $user->hasRoleName('hr'));
    }
}
