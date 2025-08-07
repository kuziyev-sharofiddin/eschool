<?php

namespace App\Imports;

use App\Repositories\User\UserInterface;
use App\Services\CachingService;
use App\Services\ResponseService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Repositories\Subscription\SubscriptionInterface;
use Illuminate\Support\Facades\Hash;
use App\Repositories\Staff\StaffInterface;
use App\Services\SubscriptionService;
use App\Services\UserService;
use App\Repositories\FormField\FormFieldsInterface;
use Str;
use Throwable;
use TypeError;

class TeacherImport implements ToCollection, WithHeadingRow
{
    private mixed $is_send_notification;

    public function __construct($is_send_notification)
    {
        $this->is_send_notification = $is_send_notification;
    }
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        // dd($collection);
        $subscription = app(SubscriptionInterface::class);
        $user = app(UserInterface::class);
        $cache = app(CachingService::class);
        $staff = app(StaffInterface::class);
        $formFields = app(FormFieldsInterface::class);

        $validator = Validator::make($collection->toArray(), [
            '*.first_name'        => 'required',
            '*.last_name'         => 'required',
            '*.gender'            => 'required',
            '*.email'             => 'required|email',
            '*.mobile'            => 'required|numeric|digits_between:1,16',
            '*.dob'               => 'required|date',
            '*.qualification'     => 'required',
            '*.current_address'   => 'required',
            '*.permanent_address' => 'required',
            '*.salary' => 'required',
        ],[
            '*.dob.date' => 'Please ensure that the dob date format you use is either DD-MM-YYYY or MM/DD/YYYY.'
        ]);

        $validator->validate();

        // Check free trial package
        $today_date = Carbon::now()->format('Y-m-d');
        $subscription = $subscription->builder()->doesntHave('subscription_bill')->whereDate('start_date','<=',$today_date)->where('end_date','>=',$today_date)->whereHas('package',function($q){
            $q->where('is_trial',1);
        })->first();
        
        if ($subscription) {
            $systemSettings = $cache->getSystemSettings();
            $staff_count = $user->builder()->role('Teacher')->withTrashed()->orWhereHas('roles', function ($q) {
                $q->where('custom_role', 1)->whereNotIn('name', ['Teacher','Guardian']);
            })->whereNotNull('school_id')->Owner()->count();
            if ($staff_count >= $systemSettings['staff_limit']) {
                $message = "The free trial allows only ".$systemSettings['staff_limit']." staff.";
                ResponseService::errorResponse($message);
            }
        }

        $defaultSessionYear = $cache->getDefaultSessionYear();

        DB::beginTransaction();
        foreach($collection as $row)
        {
            try {
                $row = $row->toArray();
                
                // Find the index of the key after which to split the array to get extra details
                $splitIndex = array_search('salary', array_keys($row)) + 1;
                
                // Get The Extra Details fields if they exist
                $extraDetailsFields = array_slice($row, $splitIndex);
                
                $existingUser = $user->builder()->where('email', $row['email'])->first();
                
                $id = $existingUser ? $existingUser->id : null;
                $users = $user->updateOrCreate( ['id' => $id] ,[
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'mobile' => $row['mobile'],
                    'email' => $row['email'],
                    'dob' => date('Y-m-d', strtotime($row['dob'])),
                    'password'   => Hash::make($row['mobile']),
                    'gender' => $row['gender'],
                    'current_address' => $row['current_address'],
                    'permanent_address' => $row['permanent_address'],
                    'status'     => 0,
                    'two_factor_enabled' => 0,
                    'two_factor_secret' => null,
                    'two_factor_expires_at' => null,
                    'deleted_at' => '1970-01-01 01:00:00'
                ]);

                
                $users->assignRole('Teacher');

                $staff->updateOrCreate( ['user_id' => $users->id] ,[
                    'user_id'       => $users->id,
                    'qualification' => $row['qualification'],
                    'salary'        => $row['salary'],
                    'joining_date'  => isset($row['joining_date']) ? date('Y-m-d',strtotime($row['joining_date'])) : null,
                    'session_year_id' => $defaultSessionYear->id,
                    'join_session_year_id' => $defaultSessionYear->id
                ]);

                // Initialize extraDetails array
                $extraDetails = array();
                
                // Check that Extra Details Exists
                if (!empty($extraDetailsFields)) {
                    $extraFieldName = array_map(static function ($d) {
                        return str_replace("_", " ", $d);
                    }, array_keys($extraDetailsFields));
                    $formFieldsCollection = $formFields->builder()->whereIn('name', $extraFieldName)->get();
                    $extraFieldValidationRules = [];
                    foreach ($formFieldsCollection as $field) {
                        if ($field->is_required) {
                            $name = strtolower(str_replace(' ', '_', $field->name));
                            $extraFieldValidationRules[$name] = 'required';
                        }
                    }
                    $extraFieldValidator = Validator::make($row, $extraFieldValidationRules);
                    $extraFieldValidator->validate();


                    // Create Extra Details Array for Teacher's Extra Form Details
                    foreach ($extraDetailsFields as $key => $value) {
                        $formField = $formFieldsCollection->first(function ($data) use ($key) {
                            return strtolower($data->name) === str_replace("_", " ", $key);
                        });

                        if (!empty($formField)) {

                            // if Form Field is checkbox then make data in json format
                            $data = $formField->type == 'checkbox' ? explode(',', $value) : $value;
                            $extraDetails[] = array(
                                'input_type'    => $formField->type,
                                'form_field_id' => $formField->id,
                                'data'          => (is_array($data)) ? json_encode($data, JSON_THROW_ON_ERROR) : $data
                            );
                        }
                    }
                    //                     Make File Input Array to Store the Null Values
                    $getFileExtraField = $formFields->builder()->where('type', 'file')->get();
                    foreach ($getFileExtraField as $value) {
                        $extraDetails[] = array(
                            'input_type'    => 'file',
                            'form_field_id' => $value->id,
                            'data'          => NULL,
                        );
                    }
                    
                }

                $sendEmail = app(UserService::class);
                if ($this->is_send_notification) {
                    $sendEmail->sendStaffRegistrationEmail($users, $row['mobile']);
                }

            } catch (Throwable $e) {
                // IF Exception is TypeError and message contains Mail keywords then email is not sent successfully
                if (Str::contains($e->getMessage(), ['Failed', 'Mail', 'Mailer', 'MailManager'])) {
                    DB::commit();
                    continue;
                }
                DB::rollBack();
                throw $e;
            }
        }
        DB::commit();
        return true;
    }
}
