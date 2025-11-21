<?php

namespace App\Console\Commands;

use App\Models\FinalOnboarding;
use App\Models\SystemSetting;
use App\Mail\PartnershipRenewalReminderMail;
use App\Mail\PartnershipExpiredMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendPartnershipRenewalReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'partnerships:send-renewal-reminders
                            {--days=10 : Days before expiry to send reminder}
                            {--dry-run : Run without actually sending emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send renewal reminder emails to partners with expiring partnerships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysBeforeExpiry = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("Checking for partnerships expiring within {$daysBeforeExpiry} days...");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No emails will be sent');
        }

        // Process expiring partnerships (send reminders)
        $this->processExpiringPartnerships($daysBeforeExpiry, $dryRun);

        // Process expired partnerships (mark as expired)
        $this->processExpiredPartnerships($dryRun);

        $this->info('Renewal reminder process completed.');

        return Command::SUCCESS;
    }

    /**
     * Process partnerships that are expiring soon
     */
    private function processExpiringPartnerships(int $daysBeforeExpiry, bool $dryRun): void
    {
        $expiringPartnerships = FinalOnboarding::with(['kycSubmission', 'partnershipModel'])
            ->needsReminder($daysBeforeExpiry)
            ->get();

        $this->info("Found {$expiringPartnerships->count()} partnerships needing reminders.");

        $sentCount = 0;
        $failedCount = 0;

        foreach ($expiringPartnerships as $partnership) {
            $email = $partnership->partner_email;
            $name = $partnership->partner_name;
            $daysLeft = $partnership->days_until_expiry;

            if (!$email) {
                $this->warn("No email found for partnership ID: {$partnership->id}");
                $failedCount++;
                continue;
            }

            $this->line("Processing: {$name} ({$email}) - {$daysLeft} days until expiry");

            if (!$dryRun) {
                try {
                    Mail::to($email)->send(new PartnershipRenewalReminderMail($partnership));

                    // Update reminder tracking
                    $partnership->update([
                        'reminder_sent_at' => now(),
                        'reminder_count' => $partnership->reminder_count + 1,
                        'renewal_status' => 'pending_renewal',
                    ]);

                    Log::info('Partnership renewal reminder sent', [
                        'final_onboarding_id' => $partnership->id,
                        'partner_email' => $email,
                        'days_until_expiry' => $daysLeft,
                        'reminder_count' => $partnership->reminder_count,
                    ]);

                    $sentCount++;
                    $this->info("  -> Reminder sent successfully");
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::error('Failed to send renewal reminder', [
                        'final_onboarding_id' => $partnership->id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error("  -> Failed to send: {$e->getMessage()}");
                }
            } else {
                $sentCount++;
                $this->info("  -> Would send reminder (dry run)");
            }
        }

        $this->info("Reminders: {$sentCount} sent, {$failedCount} failed");
    }

    /**
     * Process partnerships that have expired
     */
    private function processExpiredPartnerships(bool $dryRun): void
    {
        $expiredPartnerships = FinalOnboarding::with(['kycSubmission', 'partnershipModel'])
            ->expired()
            ->where('renewal_status', '!=', 'expired')
            ->get();

        $this->info("Found {$expiredPartnerships->count()} expired partnerships to process.");

        foreach ($expiredPartnerships as $partnership) {
            $email = $partnership->partner_email;
            $name = $partnership->partner_name;

            $this->line("Marking expired: {$name} ({$email})");

            if (!$dryRun) {
                try {
                    // Mark as expired
                    $partnership->update([
                        'renewal_status' => 'expired',
                    ]);

                    // Send expiry notification if email exists
                    if ($email) {
                        Mail::to($email)->send(new PartnershipExpiredMail($partnership));
                    }

                    Log::info('Partnership marked as expired', [
                        'final_onboarding_id' => $partnership->id,
                        'partner_email' => $email,
                    ]);

                    $this->info("  -> Marked as expired");
                } catch (\Exception $e) {
                    Log::error('Failed to process expired partnership', [
                        'final_onboarding_id' => $partnership->id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error("  -> Failed: {$e->getMessage()}");
                }
            } else {
                $this->info("  -> Would mark as expired (dry run)");
            }
        }
    }
}
