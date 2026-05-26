<?php

// app/Http/Controllers/ModuleSettingsController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ModuleSettingsController extends Controller
{
    /**
     * Show module settings page (if module provides it)
     */
    public function index(string $slug)
    {
        $moduleBasePath = base_path("modules/{$slug}");

        // 1️⃣ Ensure module exists
        if (!is_dir($moduleBasePath)) {
            abort(404, 'Module not found');
        }

        // 2️⃣ Expected module settings view
        $viewPath = "{$moduleBasePath}/resources/views/settings.blade.php";

        // 3️⃣ If module has NO settings page
        if (!file_exists($viewPath)) {
            return view('modules.no-settings', [
                'slug' => $slug
            ]);
        }

        // 4️⃣ Register module view namespace dynamically
        view()->addNamespace(
            $slug,
            "{$moduleBasePath}/resources/views"
        );

        // 5️⃣ Render module-owned settings view
        return view("{$slug}::settings", [
            'module' => $slug
        ]);
    }
}
