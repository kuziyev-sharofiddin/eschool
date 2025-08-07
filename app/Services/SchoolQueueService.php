<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SetupSchoolDatabase;
use App\Models\School;
use App\Repositories\ExtraSchoolData\ExtraSchoolDataInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

final class SchoolQueueService
{
    public function __construct(
        private readonly ExtraSchoolDataInterface $extraSchoolData
    ) {}

    /**
     * Process extra fields data for school creation
     */
    public function processExtraFields(array $extraFields, int $schoolId, ?int $schoolInquiryId = null): void
    {
        if (empty($extraFields) || !is_array($extraFields)) {
            return;
        }

        $extraDetails = [];

        foreach ($extraFields as $fields) {
            $data = null;
            
            if (isset($fields['data'])) {
                if (is_array($fields['data'])) {
                    try {
                        $data = json_encode($fields['data'], JSON_THROW_ON_ERROR);
                    } catch (\JsonException $e) {
                        $data = null;
                    }
                } else {
                    $data = $fields['data'];
                }
            }

            if (isset($fields['data']) && $fields['data'] instanceof UploadedFile) {
                $image = UploadService::upload($fields['data'], 'school');
                $data = $image;
            }

            $extraDetails[] = [
                'school_id' => $schoolId,
                'school_inquiry_id' => $schoolInquiryId,
                'form_field_id' => $fields['form_field_id'],
                'data' => $data,
            ];
        }

        if (!empty($extraDetails)) {
            $this->extraSchoolData->createBulk($extraDetails);
        }
    }

    /**
     * Dispatch school setup jobs
     */
    public function dispatchSchoolSetupJobs(
        int $schoolId,
        ?int $packageId = null,
        ?string $schoolCodePrefix = null,
        array $requestData = []
    ): void {
        // Dispatch database setup job
        SetupSchoolDatabase::dispatch(
            $schoolId,
            $packageId,
            $schoolCodePrefix
        );
        
    }

    /**
     * Create school admin user
     */
    public function createSchoolAdmin(array $adminData): mixed
    {
        return DB::table('users')->insertGetId([
            'first_name' => $adminData['first_name'],
            'last_name' => $adminData['last_name'],
            'mobile' => $adminData['mobile'],
            'email' => $adminData['email'],
            'password' => Hash::make($adminData['password']),
            'school_id' => $adminData['school_id'],
            'image' => $adminData['image'] ?? 'dummy_logo.jpg',
            'email_verified_at' => $adminData['email_verified_at'] ?? null,
            'two_factor_enabled' => $adminData['two_factor_enabled'] ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Generate database name for school
     */
    public function generateDatabaseName(string $schoolName, int $schoolId): string
    {
        $school_name = str_replace('.', '_', $schoolName);
        return 'eschool_saas_' . $schoolId . '_' . strtolower(strtok($school_name, " "));
    }
} 