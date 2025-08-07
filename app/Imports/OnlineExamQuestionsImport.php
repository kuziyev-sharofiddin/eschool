<?php

namespace App\Imports;

use App\Models\ClassSubject;
use App\Models\OnlineExamQuestionCommon;
use App\Models\OnlineExamQuestionOption;
use App\Repositories\ClassSection\ClassSectionInterface;
use App\Repositories\ClassSubject\ClassSubjectInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Services\ResponseService;
use App\Services\CachingService;
use App\Repositories\OnlineExamQuestion\OnlineExamQuestionInterface;
use App\Repositories\OnlineExamQuestionCommon\OnlineExamQuestionCommonInterface;
use App\Repositories\OnlineExamQuestionOption\OnlineExamQuestionOptionInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;
use JsonException;

class OnlineExamQuestionsImport implements WithMultipleSheets
{
    private mixed $classSectionID;
    // private mixed $examID;
    private mixed $classSubjectID;


    public function __construct($classSectionID, $classSubjectID)
    {
        $this->classSectionID = $classSectionID;
        // $this->examID = $examID;
        $this->classSubjectID = $classSubjectID;
    }

    /**
     * @throws Throwable
     */
    public function sheets(): array
    {
        return [
            new FirstSheetImport($this->classSectionID, $this->classSubjectID)
        ];
    }
}

class FirstSheetImport implements ToCollection, WithHeadingRow
{
    private mixed $classSectionID;
    private mixed $classSubjectID;
    /**
     * @param $classSectionID
     * @param $classSubjectID
     */

    // Import the Class Section and Repositories
    public function __construct($classSectionID, $classSubjectID)
    {
        $this->classSectionID = $classSectionID;
        $this->classSubjectID = $classSubjectID;
    }

    /**
     * @throws JsonException
     * @throws Throwable
     */
    public function collection(Collection $collection)
    {
        // Validate incoming CSV data
        $validator = Validator::make($collection->toArray(), [
            '*.question' => 'required|string',
            '*.image_url' => 'nullable|url',
            '*.note' => 'nullable',
            // '*.option' => 'required',
            '*.answer' => 'required',
            '*.difficulty' => 'required|in:easy,medium,hard,Easy,Medium,Hard',
        ], [
            'question.required' => 'Question field is required.',
            'image_url.url' => 'Please enter the correct Image URL.',
            'option.required' => 'Option field is required.',
            'answer.required' => 'Answer field is required.',
            'difficulty.in' => 'Difficulty should be in easy,medium,hard.',
        ]);

        $validator->validate();

        DB::beginTransaction();
        try {

            $onlineExamQuestion = app(OnlineExamQuestionInterface::class);
            $classSections = app(ClassSectionInterface::class);
            $classSubject = app(ClassSubjectInterface::class);

            $section_ids = is_array($this->classSectionID) ? $this->classSectionID : [$this->classSectionID];
            if (empty($section_ids)) {
                throw new \Exception('Class section ID is required');
            }

            // START NEW CODE

            foreach ($collection as $row) {
                // questions and options
                $examQuestion = $onlineExamQuestion->create([
                    'question' => $row['question'],
                    'image_url' => $this->downloadAndStoreImage($row['image_url']) ?? null,
                    'note' => $row['note'] ?? null,
                    'difficulty' => strtolower($row['difficulty']),
                    'school_id' => Auth::user()->school_id,
                    'last_edited_by' => Auth::id(),
                ]);

                $optionCommon = [];
                foreach ($row as $column => $value) {
                    if (str_starts_with($column, 'option_')) {
                        $optionText = trim($value);
                        if ($optionText === '') continue;
                        $optionCommon[] = [
                            'question_id' => $examQuestion->id,
                            'option' => $optionText,
                            'is_answer' => strtolower($column) === strtolower($row['answer']) ? 1 : 0,
                            'school_id' => Auth::user()->school_id
                        ];
                    }
                }
                OnlineExamQuestionOption::insert($optionCommon);

                // question common
                $questionCommon = [];
                foreach ($section_ids as $section_id) {

                    $classSection = $classSections->builder()
                    ->where('id', $section_id)
                    ->first();

                    $classSubject = ClassSubject::where('class_id', $classSection->class_id)
                    ->where('subject_id', $this->classSubjectID)
                    ->first();

                    // $questionCommon = [];
                    $questionCommon[] = [
                        'online_exam_question_id' => $examQuestion->id,
                        'class_section_id' => $classSection->id,
                        'class_subject_id' => $classSubject->id,
                    ];
                }                
                OnlineExamQuestionCommon::insert($questionCommon);
                
            }

            // END NEW CODE

            DB::commit();
            return true;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function downloadAndStoreImage($url)
    {
        try {
            // Get the image content
            if ($url) {
                // $response = Http::get($url);
                // if (!$response->successful()) {
                //     throw new \Exception("Failed to download image from URL.");
                // }
                // $imageContent = $response->body();
                // $filename = uniqid() . time() . '.jpg';
                // Storage::disk('public')->put(Auth::user()->school_id ."/online-exam-question/{$filename}", $imageContent);
                // return Auth::user()->school_id ."/online-exam-question/{$filename}";

                $imageContents = Http::get($url)->body();
                // Get the file extension from URL
                $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
                // Ensure a valid extension
                if (!$extension) {
                    $extension = 'jpg'; // Default to jpg if no extension found
                }
                $imageName = time() . '_image .'.$extension;
                $path = Auth::user()->school_id. '/online-exam-question/';
                Storage::put("public/".$path."/{$imageName}", $imageContents);
                $imageUrl = $path."{$imageName}";
                return $imageUrl;
            }
            return null;
        } catch (\Exception $e) {
            return false;
        }
    }
}
