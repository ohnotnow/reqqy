<?php

namespace App\Livewire;

use App\ApplicationCategory;
use App\Models\Application;
use Livewire\Attributes\Url;
use Livewire\Component;

class SettingsPage extends Component
{
    #[Url]
    public string $activeTab = 'internal';

    public ?int $editingApplicationId = null;

    public string $formCategory = '';

    public string $name = '';

    public string $short_description = '';

    public string $overview = '';

    public bool $is_automated = false;

    public string $status = '';

    public string $url = '';

    public string $repo = '';

    public ?int $promotingApplicationId = null;

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function promoteApplication(int $id): void
    {
        $application = Application::findOrFail($id);

        $this->promotingApplicationId = $application->id;
        $this->name = $application->name;
        $this->short_description = $application->short_description ?? '';
        $this->overview = '';
        $this->is_automated = false;
        $this->status = '';
        $this->url = '';
        $this->repo = '';
    }

    public function savePromotion(): void
    {
        $validated = $this->validate([
            'status' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'repo' => 'nullable|string|max:255',
            'overview' => 'nullable|string',
            'is_automated' => 'boolean',
        ]);

        $application = Application::findOrFail($this->promotingApplicationId);
        $application->promoteToInternal();
        $application->update($validated);

        $this->promotingApplicationId = null;
        $this->resetForm();
        $this->activeTab = 'internal';
    }

    public function createApplication(): void
    {
        $this->resetForm();
        $this->formCategory = $this->activeTab;
    }

    public function editApplication(int $id): void
    {
        $application = Application::findOrFail($id);

        $this->editingApplicationId = $application->id;
        $this->formCategory = $application->category->value;
        $this->name = $application->name;
        $this->short_description = $application->short_description ?? '';
        $this->overview = $application->overview ?? '';
        $this->is_automated = $application->is_automated;
        $this->status = $application->status ?? '';
        $this->url = $application->url ?? '';
        $this->repo = $application->repo ?? '';
    }

    public function saveApplication(): void
    {
        $rules = $this->getValidationRules();
        $validated = $this->validate($rules);

        $validated['category'] = ApplicationCategory::from($this->formCategory);

        Application::create($validated);

        $this->resetForm();
    }

    public function updateApplication(): void
    {
        $rules = $this->getValidationRules();
        $validated = $this->validate($rules);

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
        $this->formCategory = '';
        $this->name = '';
        $this->short_description = '';
        $this->overview = '';
        $this->is_automated = false;
        $this->status = '';
        $this->url = '';
        $this->repo = '';
    }

    protected function getValidationRules(): array
    {
        $category = ApplicationCategory::tryFrom($this->formCategory);

        $rules = [
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string',
        ];

        if ($category === ApplicationCategory::Internal) {
            $rules['overview'] = 'nullable|string';
            $rules['is_automated'] = 'boolean';
            $rules['status'] = 'required|string|max:255';
            $rules['url'] = 'nullable|string|max:255';
            $rules['repo'] = 'nullable|string|max:255';
        } elseif ($category === ApplicationCategory::External) {
            $rules['url'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    public function render()
    {
        $internalApplications = Application::where('category', ApplicationCategory::Internal)
            ->orderBy('name')
            ->get();

        $externalApplications = Application::where('category', ApplicationCategory::External)
            ->orderBy('name')
            ->get();

        $proposedApplications = Application::where('category', ApplicationCategory::Proposed)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.settings-page', [
            'internalApplications' => $internalApplications,
            'externalApplications' => $externalApplications,
            'proposedApplications' => $proposedApplications,
        ]);
    }
}
