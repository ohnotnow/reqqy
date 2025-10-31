<?php

namespace App\Livewire;

use App\Models\Application;
use Livewire\Component;

class HomePage extends Component
{
    public ?int $selectedApplicationId = null;

    public function startNewFeature()
    {
        $this->validate([
            'selectedApplicationId' => 'required|exists:applications,id',
        ]);

        return $this->redirect(route('conversation', ['application_id' => $this->selectedApplicationId]));
    }

    public function startNewApplication()
    {
        return $this->redirect(route('conversation'));
    }

    public function render()
    {
        return view('livewire.home-page', [
            'applications' => Application::orderBy('name')->get(),
        ]);
    }
}
