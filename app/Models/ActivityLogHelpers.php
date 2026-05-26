<?php

namespace App\Models;

trait ActivityLogHelpers
{
    /**
     * Models\ActivityLogHelpers.php
     * 
     * Get readable changes from old/new values
     */
    public function getChanges(): array
    {
        $changes = [];
        $ignoreFields = ['password', 'remember_token', 'avatar', 'updated_at', 'id', 'created_at'];
        
        if (!$this->metadata || !isset($this->metadata['old_values']) || !isset($this->metadata['new_values'])) {
            return $changes;
        }

        $oldValues = $this->metadata['old_values'];
        $newValues = $this->metadata['new_values'];

        foreach ($oldValues as $field => $oldValue) {
            if (in_array($field, $ignoreFields)) {
                continue;
            }

            $newValue = $newValues[$field] ?? null;

            if ($oldValue !== $newValue) {
                $changes[$field] = [
                    'old' => $oldValue ?? '(empty)',
                    'new' => $newValue ?? '(empty)',
                ];
            }
        }

        return $changes;
    }

    /**
     * Get summary of what changed
     */
    public function getChangeSummary(): string
    {
        if ($this->action_type !== 'update') {
            return '';
        }

        $changes = $this->getChanges();

        if (empty($changes)) {
            return 'No changes recorded';
        }

        $fields = array_keys($changes);
        
        if (count($fields) === 1) {
            return ucfirst($fields[0]) . ' was updated';
        }

        return count($fields) . ' fields were updated';
    }
}