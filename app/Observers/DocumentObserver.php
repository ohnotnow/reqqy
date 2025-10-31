<?php

namespace App\Observers;

use App\Models\Document;
use App\Models\User;
use App\Notifications\NewDocumentCreated;

class DocumentObserver
{
    public function created(Document $document): void
    {
        $adminUsers = User::where('is_admin', true)->get();

        foreach ($adminUsers as $admin) {
            $admin->notify(new NewDocumentCreated($document));
        }
    }
}
