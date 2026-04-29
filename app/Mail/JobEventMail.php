<?php
 
namespace App\Mail;
 
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
 
class JobEventMail extends Mailable
{
    use Queueable, SerializesModels;
 
    public array $data;
 
    public function __construct(array $data)
    {
        $this->data = $data;
    }
 
public function build()
{
    // Build a safe subject
    $jobTitle   = $this->data['job']['title'] ?? null;
    $jobId      = $this->data['job']['id']    ?? null;
    $subjectJob = $jobTitle ?? ('Job #' . ($jobId ?? ''));
    $subject    = ($this->data['action_label'] ?? 'Job Update') . ' – ' . $subjectJob;
 
    $fromAddr = config('mail.from.address');
    $fromName = config('mail.from.name');
 
    $m = $this->subject($subject)
        // (explicit From; DynamicMail also sets these in config, but this makes it bullet-proof)
        ->from($fromAddr, $fromName)
        ->view('emails.jobEvent')
        ->with($this->data);
 
    // Set envelope sender / Return-Path (helps deliverability on many hosts)
    $m->withSymfonyMessage(function (\Symfony\Component\Mime\Email $message) use ($fromAddr, $fromName) {
        if ($fromAddr) {
            $message->sender(new \Symfony\Component\Mime\Address($fromAddr, $fromName ?: null));
            $message->returnPath($fromAddr);
        }
    });
 
    // Optional: if you ever pass reply_to in $this->data, respect it
    if (!empty($this->data['reply_to'])) {
        $rt = $this->data['reply_to'];
        $m->replyTo($rt['email'] ?? $rt, $rt['name'] ?? null);
    }
 
    return $m;
}
 
 
}
 
 