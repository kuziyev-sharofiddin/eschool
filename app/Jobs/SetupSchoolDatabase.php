<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\School;
use App\Services\SchoolDataService;
use App\Services\SubscriptionService;
use App\Services\CachingService;
use App\Repositories\SystemSetting\SystemSettingInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SetupSchoolDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300; // 5 minutes timeout
    public int $backoff = 60; // 1 minute between retries

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly int $schoolId,
        private readonly ?int $packageId = null,
        private readonly ?string $schoolCodePrefix = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        SchoolDataService $schoolService,
        SubscriptionService $subscriptionService,
        CachingService $cache,
        SystemSettingInterface $systemSettings
    ): void {
        try {
            DB::setDefaultConnection('mysql');
            Log::info("Starting school database setup for school ID: {$this->schoolId}");

            // Get school data
            $school = School::findOrFail($this->schoolId);
            
            // Create database
            DB::statement("CREATE DATABASE IF NOT EXISTS {$school->database_name}");

            // Run migrations
            $schoolService->createDatabaseMigration($school);

            // Setup pre-settings
            $schoolService->preSettingsSetup($school);

            // Assign package if provided
            if ($this->packageId) {
                $subscriptionService->createSubscription($this->packageId, $school->id, null, 1);
                $cache->removeSchoolCache(config('constants.CACHE.SCHOOL.SETTINGS'), $school->id);
            }

            // Update school code prefix if provided
            if ($this->schoolCodePrefix) {
                $settings = $cache->getSystemSettings();
                if (($settings['school_prefix'] ?? '') != $this->schoolCodePrefix) {
                    $settingsData[] = [
                        "name" => 'school_prefix',
                        "data" => $this->schoolCodePrefix,
                        "type" => "text"
                    ];
                    $systemSettings->upsert($settingsData, ["name"], ["data"]);
                    $cache->removeSystemCache(config('constants.CACHE.SYSTEM.SETTINGS'));
                }
            }

            // Update school status to active
            $school->update(['status' => 1, 'installed' => 1]);

            DB::setDefaultConnection('school');
            Config::set('database.connections.school.database', $school->database_name);
            DB::purge('school');
            DB::connection('school')->reconnect();
            DB::setDefaultConnection('school');
            School::where('id', $this->schoolId)->update(['status' => 1, 'installed' => 1]);

            $school = School::with('user')->findOrFail($this->schoolId);
            $settings = $cache->getSystemSettings();

            $email_body = $this->replacePlaceholders($school, $school->user, $settings, $school->code);
            
            $data = [
                'subject'     => 'Welcome to ' . ($settings['system_name'] ?? 'eSchool Saas'),
                'email'       => $school->support_email,
                'email_body'  => $email_body
            ];

            Mail::send('schools.email', $data, static function ($message) use ($data) {
                $message->to($data['email'])->subject($data['subject']);
            });

            // Send email verification if not already verified
            if (!$school->user->hasVerifiedEmail()) {
                $school->user->sendEmailVerificationNotification();
            }

            Log::info("Welcome email sent successfully for school ID: {$this->schoolId}");

            Log::info("School database setup completed successfully for school ID: {$this->schoolId}");

        } catch (Throwable $e) {
            Log::error("School database setup failed for school ID: {$this->schoolId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update school status to failed
            $school = School::find($this->schoolId);
            if ($school) {
                $school->update(['status' => 0, 'installed' => 0]);
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error("School database setup job failed permanently for school ID: {$this->schoolId}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Update school status to failed
        $school = School::find($this->schoolId);
        if ($school) {
            $school->update(['status' => 0, 'installed' => 0]);
        }
    }

    private function replacePlaceholders($school, $user, $settings, $schoolCode): string
    {
        $templateContent = $settings['email_template_school_registration'] ?? '';
        
        $placeholders = [
            '{school_admin_name}' => $user->full_name,
            '{code}' => $schoolCode,
            '{email}' => $user->email,
            '{password}' => $user->mobile,
            '{school_name}' => $school->name ?? '',
            '{super_admin_name}' => $settings['super_admin_name'] ?? 'Super Admin',
            '{support_email}' => $settings['mail_username'] ?? '',
            '{contact}' => $settings['mobile'] ?? '',
            '{system_name}' => $settings['system_name'] ?? 'eSchool Saas',
            '{url}' => url('/'),
        ];

        foreach ($placeholders as $placeholder => $replacement) {
            $templateContent = str_replace($placeholder, $replacement, $templateContent);
        }

        return $templateContent;
    }
} 