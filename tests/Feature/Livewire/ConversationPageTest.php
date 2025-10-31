<?php

namespace Tests\Feature\Livewire;

use App\Livewire\ConversationPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class ConversationPageTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(ConversationPage::class)
            ->assertStatus(200);
    }
}
