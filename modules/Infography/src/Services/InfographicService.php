<?php

namespace App\Modules\Infographic\Services;

use App\Modules\Infographic\Models\Infographic;
use Illuminate\Support\Facades\Storage;

class InfographicService
{
    protected string $folder = 'infographic/files';

    public function create(array $data, $file = null): Infographic
    {
        if ($file) {
            $data['file_path'] = $this->storeFile($file);
        }

        return Infographic::create($data);
    }

    public function update(Infographic $infographic, array $data, $file = null): Infographic
    {
        if ($file) {
            // Delete old file if exists
            if ($infographic->file_path && Storage::disk('public')->exists($infographic->file_path)) {
                Storage::disk('public')->delete($infographic->file_path);
            }

            $data['file_path'] = $this->storeFile($file);
        }

        $infographic->update($data);

        return $infographic;
    }

    public function delete(Infographic $infographic): void
    {
        if ($infographic->file_path && Storage::disk('public')->exists($infographic->file_path)) {
            Storage::disk('public')->delete($infographic->file_path);
        }

        $infographic->delete();
    }

    protected function storeFile($file): string
    {
        return $file->store($this->folder, 'public');
    }
}