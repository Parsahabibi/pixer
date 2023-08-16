<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Marvel\Database\Models\Participant;
use Marvel\Mail\ConversationReminderMail;
use Mail;

class SendConversationReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $participant;

    /**
     * Create a new job instance.
     *
     * @param Participant $participant
     * @return void
     */
    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new ConversationReminderMail($this->participant);
        if('user' === $this->participant->type) {
            Mail::to($this->participant->user->email)->send($email);
        } else {
            Mail::to($this->participant->shop->owner->email)->send($email);
        }

        $this->participant->notify = 1;
        $this->participant->save();
    }
}
