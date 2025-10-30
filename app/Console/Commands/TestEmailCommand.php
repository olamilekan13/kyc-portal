<?php

namespace App\Console\Commands;

use App\Models\SystemSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration by sending a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? SystemSetting::get('kyc_notification_email', config('mail.from.address'));

        $this->info("Sending test email to: {$email}");

        try {
            Mail::raw('This is a test email from your KYC Portal. If you receive this, your email configuration is working correctly!', function ($message) use ($email) {
                $message->to($email)
                    ->subject('Test Email - KYC Portal');
            });

            $this->info("✓ Test email sent successfully!");
            $this->info("Please check the inbox for: {$email}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("✗ Failed to send test email!");
            $this->error("Error: " . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
