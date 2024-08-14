<?php

namespace App\Observers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Storage;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        if ($transaction->isDirty('image')) {
            $originalImage = $transaction->getOriginal('image');

            if (!empty($originalImage) && Storage::disk('public')->exists($originalImage)) {
                Storage::disk('public')->delete($originalImage);
            }
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        if (! is_null($transaction->image)) {
            Storage::disk('public')->delete($transaction->image);
        }
    }
}
