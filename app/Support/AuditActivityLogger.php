<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class AuditActivityLogger
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $context
     */
    public function log(
        string $logName,
        string $description,
        string $event,
        Model $subject,
        array $attributes = [],
        array $old = [],
        array $context = [],
    ): void {
        $activity = activity($logName)
            ->performedOn($subject)
            ->event($event);

        if (auth()->user() !== null) {
            $activity->causedBy(auth()->user());
        }

        $activity->withProperties([
            'attributes' => $attributes,
            'old' => $old,
            'context' => $context,
        ])->log($description);
    }
}
