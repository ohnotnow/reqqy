<?php

namespace App\Livewire;

use App\Models\Application;
use Livewire\Component;

class SettingsPage extends Component
{
    public ?int $editingApplicationId = null;

    public string $name = '';

    public string $short_description = '';

    public string $overview = '';

    public bool $is_automated = false;

    public string $status = '';

    public string $url = '';

    public string $repo = '';

    public function createApplication(): void
    {
        $this->resetForm();
    }

    public function editApplication(int $id): void
    {
        $application = Application::findOrFail($id);

        $this->editingApplicationId = $application->id;
        $this->name = $application->name;
        $this->short_description = $application->short_description ?? '';
        $this->overview = $application->overview ?? '';
        $this->is_automated = $application->is_automated;
        $this->status = $application->status;
        $this->url = $application->url ?? '';
        $this->repo = $application->repo ?? '';
    }

    public function saveApplication(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'overview' => 'nullable|string',
            'is_automated' => 'boolean',
            'status' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'repo' => 'nullable|string|max:255',
        ]);

        Application::create($validated);

        $this->resetForm();
    }

    public function updateApplication(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'overview' => 'nullable|string',
            'is_automated' => 'boolean',
            'status' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'repo' => 'nullable|string|max:255',
        ]);

        $application = Application::findOrFail($this->editingApplicationId);
        $application->update($validated);

        $this->resetForm();
    }

    public function deleteApplication(int $id): void
    {
        Application::findOrFail($id)->delete();
    }

    public function resetForm(): void
    {
        $this->editingApplicationId = null;
        $this->name = '';
        $this->short_description = '';
        $this->overview = '';
        $this->is_automated = false;
        $this->status = '';
        $this->url = '';
        $this->repo = '';
    }

    public function render()
    {
        return view('livewire.settings-page', [
            'applications' => Application::orderBy('name')->get(),
        ]);
    }
}
