<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

interface LoggableEvent
{
    public function getPerformedOn(): ?Model;

    public function getCausedBy(): ?User;

    public function getDescription(): string;

    public function getProperties(): array;
}
