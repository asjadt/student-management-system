<?php

namespace App\Http\Controllers;

use App\Http\Requests\DownloadStudentLetterPdfRequest;
use App\Http\Requests\StudentLetterCreateRequest;
use App\Http\Requests\StudentLetterGenerateRequest;
use App\Http\Requests\StudentLetterUpdateRequest;
use App\Http\Requests\StudentLetterUpdateViewRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\EmailLogUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\HTML_TO_DOC;
use App\Http\Utils\ModuleUtil;
use App\Http\Utils\UserActivityUtil;
use App\Mail\StudentLetterMail;
use App\Models\LetterTemplate;
use App\Models\Student;
use App\Models\StudentLetter;
use App\Models\StudentLetterEmailHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpWord\PhpWord;

use PDF;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\Shared\Html;


class StudentLetterController extends Controller
{

    use ErrorUtil, UserActivityUtil, BusinessUtil, BasicUtil, EmailLogUtil, ModuleUtil;


    /**
     *
     * @OA\Post(
     *      path="/v1.0/student-letters",
     *      operationId="createStudentLetter",
     *      tags={"student_letters"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store student letters",
     *      description="This method is to store student letters",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     * @OA\Property(property="issue_date", type="string", format="string", example="issue_date"),
     * @OA\Property(property="letter_content", type="string", format="string", example="letter_content"),
     * @OA\Property(property="status", type="string", format="string", example="status"),
     * @OA\Property(property="sign_required", type="string", format="string", example="sign_required"),
     * @OA\Property(property="letter_view_required", type="string", format="string", example="letter_view_required"),
     *
     * @OA\Property(property="student_id", type="string", format="string", example="student_id"),
     * @OA\Property(property="attachments", type="string", format="string", example="attachments"),
     *
     *
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

    public function createStudentLetter(StudentLetterCreateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            $this->isModuleEnabled("letter_template");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('student_letter_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();



                $request_data["created_by"] = $request->user()->id;
                $request_data["business_id"] = auth()->user()->business_id;

                if (empty(auth()->user()->business_id)) {
                    $request_data["business_id"] = NULL;
                    if ($request->user()->hasRole('superadmin')) {
                        $request_data["is_default"] = 1;
                    }
                }




                $student_letter =  StudentLetter::create($request_data);




                return response($student_letter, 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Post(
     *      path="/v1.0/student-letters/generate",
     *      operationId="generateStudentLetter",
     *      tags={"student_letters"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to generate student letters",
     *      description="This method is to generate student letters",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     * @OA\Property(property="letter_template_id", type="string", format="string", example="sign_required"),
     * @OA\Property(property="letter_view_required", type="string", format="string", example="letter_view_required"),
     * @OA\Property(property="student_id", type="string", format="string", example="student_id"),
     *     * @OA\Property(property="letter_issue_date", type="string", format="string", example="date"),
     *

     *
     *
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

    public function generateStudentLetter(StudentLetterGenerateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            $this->isModuleEnabled("letter_template");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('student_letter_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();

                $business = auth()->user()->business;


                $student = Student::where([
                    "id" => $request_data["student_id"]
                ])
                    ->first();



                $letter_template = LetterTemplate::where([
                    "id" => $request_data["letter_template_id"]
                ])->first();

                $template = $letter_template->template;

                $letterTemplateVariables = $this->getLetterTemplateVariablesFunc();

                foreach ($letterTemplateVariables as $item) {
                    if (strpos($item, '[') !== false) {
                        // Convert the placeholder to lowercase and remove square brackets
                        $variableName = strtolower(str_replace(['[', ']'], '', $item));

                        // Replace [FULL_NAME] with the concatenated full name
                        if ($item == "[FULL_NAME]") {
                            $fullName = trim(
                                ($student["title"] ?? '') . ' ' .
                                ($student["first_name"] ?? '') . ' ' .
                                ($student["middle_name"] ?? '') . ' ' .
                                ($student["last_name"] ?? '')
                            );
                            $template = str_replace($item, !empty($fullName) ? $fullName : '--', $template);
                        }
                        else if ($item == "[COURSE_TITLE]") {
                            $courseTitle = optional($student->course_title)->name ?? '--';
                            $template = str_replace($item, $courseTitle, $template);
                        }
                        else if ($item == "[COURSE_LEVEL]") {
                            $courseLevel = optional($student->course_title)->level ?? '--';
                            $template = str_replace($item, $courseLevel, $template);
                        }
                        else if ($item == "[AWARDING_BODY]") {
                            $awardingBodyName = optional(optional($student->course_title)->awarding_body)->name ?? '--';
                            $template = str_replace($item, $awardingBodyName, $template);
                        }
                        else if ($item == "[STUDENT_STATUS]") {
                            $studentStatus = optional($student->student_status)->name ?? '--';
                            $template = str_replace($item, $studentStatus, $template);
                        }
                        else if ($item == "[COMPANY_NAME]") {
                            $companyName = $business["name"] ?? '[COMPANY_NAME]';
                            $template = str_replace($item, $companyName, $template);
                        }
                        else if ($item == "[COMPANY_ADDRESS_LINE_1]") {
                            $addressLine1 = $business["address_line_1"] ?? '[COMPANY_ADDRESS_LINE_1]';
                            $template = str_replace($item, $addressLine1, $template);
                        }
                        else if ($item == "[COMPANY_CITY]") {
                            $companyCity = $business["city"] ?? '[COMPANY_CITY]';
                            $template = str_replace($item, $companyCity, $template);
                        }
                        else if ($item == "[COMPANY_POSTCODE]") {
                            $companyPostcode = $business["postcode"] ?? '[COMPANY_POSTCODE]';
                            $template = str_replace($item, $companyPostcode, $template);
                        }
                        else if ($item == "[COMPANY_COUNTRY]") {
                            $companyCountry = $business["country"] ?? '[COMPANY_COUNTRY]';
                            $template = str_replace($item, $companyCountry, $template);
                        }
                        else if ($item == "[She/He]") {
                            $sex = $student->sex=="Male"?"He":"She";
                            $template = str_replace($item, $sex, $template);
                        }  else if ($item == "[Her/His]") {
                            $sex = $student->sex=="Male"?"His":"Her";
                            $template = str_replace($item, $sex, $template);
                        }
                        else if ($item == "[Mr/Mrs]") {
                            $sex = $student->sex=="Male"?"Mr":"Mrs";
                            $template = str_replace($item, $sex, $template);
                        }



                        else if ($item == "[QR_CODE]") {
                            // Get the URL from the environment variable
                            $url = (!empty($business->url)?$business->url:"https://app.smartcollegeportal.com")."/public/student/view/" . base64_encode($student->id) . "/" . base64_encode($student->business_id);

                            // Generate the QR code image
                            $qrCode = new QrCode($url);
                            $qrCode->setSize(150);
                            $writer = new PngWriter();

                            // Generate the image as a string (binary data)
                            $image = $writer->write($qrCode)->getString();  // Correct method to get the binary data

                            // Convert the binary data to a base64 string to embed in the HTML
                            $base64Image = base64_encode($image);
                            $qrCodeImage = 'data:image/png;base64,' . $base64Image;

                            // Replace [QR_CODE] with the image in the template
                            $template = str_replace($item, '<img src="' . $qrCodeImage . '" alt="QR Code" />', $template);
                        }

                        else if (
                            $item == "[DATE_OF_BIRTH]"
                            || $item == "[COURSE_START_DATE]"
                            || $item == "[COURSE_END_DATE]"
                            || $item == "[PASSPORT_ISSUE_DATE]"
                            || $item == "[PASSPORT_EXPIRY_DATE]"
                        ) {
                            $dateValue = $student[$variableName] ?? null;

                            if ($dateValue && $dateValue !== '1970-01-01') {
                                $formattedDate = Carbon::parse($dateValue)->format('d-m-Y');
                                $template = str_replace($item, $formattedDate, $template);
                            } else {
                                $template = str_replace($item, '', $template);
                            }
                        }
                        else if ($item == "[LETTER_ISSUE_DATE]") {
                            $dateValue = $request_data["letter_issue_date"] ;
                            $formattedDate = Carbon::parse($dateValue)->format('d M Y');
                            $template = str_replace($item, $formattedDate, $template);

                        }
                         else {
                            $template = str_replace($item, $student[$variableName], $template);
                        }
                    }
                }

                
                return response(["template" => $template], 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    public function fixHtmlTags($html)
{
    // Ensure <img> tags are self-closed properly (e.g., <img /> instead of <img>)
    $html = preg_replace('/<img([^>]*)(?<!\/)>/i', '<img$1 />', $html);

    // Ensure there is no <br> or <hr> tag directly inside <p> without proper closing


     // Replace all <br> tags globally with a custom class
     $html = preg_replace('/<br\s*\/?>/i', '<br class="fix_br_tag" />', $html);

     // Replace all <hr> tags globally with a custom class
     $html = preg_replace('/<hr\s*\/?>/i', '<hr class="fix_hr_tag" />', $html);

    // Ensure <hr> tags outside <p> tags are also handled
    $html = preg_replace('/<hr\s*\/?>/i', '<hr class="fix_hr_tag" />', $html);

    Log::info('Content: ' . $html);

    // Ensure all unclosed tags are closed
    $this->closeUnclosedTags($html);

    return $html;
}


public function closeUnclosedTags(&$html)
{
    // Define a list of self-closing and non-self-closing tags
    $selfClosingTags = ['img', 'br', 'hr', 'input', 'meta', 'link', 'area', 'base', 'col', 'source', 'track'];
    $nonSelfClosingTags = ['p', 'div', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li'];

    // Use a stack to track open tags
    $openTags = [];

    // Regular expression to match all opening and closing tags
    preg_match_all('/<\/?([a-zA-Z0-9]+)([^>]*)(?=>|$)/', $html, $matches, PREG_OFFSET_CAPTURE);

    foreach ($matches[1] as $index => $tagName) {
        $tag = strtolower($tagName[0]);

        // If it's a closing tag, check if it matches the most recent opening tag
        if (substr($matches[0][$index][0], 0, 2) === '</') {
            if (!empty($openTags) && end($openTags) === $tag) {
                array_pop($openTags);
            } else {
                // Mismatch found, add closing tag to fix
                $html = substr_replace($html, "</$tag>", $matches[0][$index][1], 0);
            }
        } elseif (!in_array($tag, $selfClosingTags)) {
            // For opening tags, add them to the stack
            $openTags[] = $tag;
        }
    }

    // Append missing closing tags for all open tags
    foreach (array_reverse($openTags) as $tag) {
        $html .= "</$tag>";
    }
}







    /**
     *
     * @OA\Post(
     *      path="/v1.0/student-letters/download",
     *      operationId="downloadStudentLetter",
     *      tags={"student_letters"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to download pdf",
     *      description="This method is to download pdf",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"first_Name"},
     *             @OA\Property(property="student_letter_id", type="string", format="string",example="student_letter_id"),
     *             @OA\Property(property="student_id", type="string", format="string",example="student_id"),
     *             @OA\Property(property="type", type="string", format="string",example="word")
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

    public function downloadStudentLetter(DownloadStudentLetterPdfRequest $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            $this->isModuleEnabled("letter_template");
            $request_data = $request->validated();

            $student_letter =  StudentLetter::where([
                "id" => $request_data["student_letter_id"]
            ])
                ->first();
            $business = auth()->user()->business;


            if(empty($request_data["type"])) {
                $pdf = PDF::loadView('email.dynamic_mail', [
                    "html_content" => $student_letter->letter_content,
                    "letter_template_header" => $business->letter_template_header,
                    "letter_template_footer" => $business->letter_template_footer,
                ],
                );
                return $pdf->download(("letter" . '.pdf'));
            }
            if($request_data["type"] == "pdf") {
                $pdf = PDF::loadView('email.dynamic_mail', [
                    "html_content" => $student_letter->letter_content,
                    "letter_template_header" => $business->letter_template_header,
                    "letter_template_footer" => $business->letter_template_footer,
                ],
                );
                return $pdf->download(("letter" . '.pdf'));
            } else if($request_data["type"] == "word"){

    // Create a new PhpWord object
    $phpWord = new PhpWord();

    // Set header and footer content
    $header = $this->fixHtmlTags($business->letter_template_header);
    $footer = $this->fixHtmlTags($business->letter_template_footer);

    // Define HTML content to be added to the document
    $htmlContent = $this->fixHtmlTags($student_letter->letter_content);

    Log::info('Header Content: ' . $header);


    // Add a new section with a header and footer
    $section = $phpWord->addSection();
    $headerObj = $section->addHeader();
    Html::addHtml($headerObj, $header);  // Add the header HTML
    $footerObj = $section->addFooter();
    Html::addHtml($footerObj, $footer);  // Add the footer HTML

    // Add dynamic HTML content to the section
    Html::addHtml($section, $htmlContent);

    // Save the file to a storage path
    $filename = 'DynamicHtmlDocument.docx';
    $path = storage_path($filename);
    $phpWord->save($path, 'Word2007');

    // Return the file as a download response and delete after send
    return response()->download($path)->deleteFileAfterSend(true);


//                 $htd = new HTML_TO_DOC();
// $htd->headerContent = $business->letter_template_header;
// $htd->footerContent = $business->letter_template_footer;

// $htmlContent = "
//     <div class='content'>{$student_letter->letter_content}</div>
// ";

// $htd->createDoc($htmlContent, "my-document", 1);

                // $phpWord = new PhpWord();
                // $section = $phpWord->addSection();

                // // Set the HTML content with header, footer, and letter content
                // $htmlContent = "
                // <html>
                //     <head>
                //         <style>
                //             .header { text-align: center; font-size: 18px; }
                //             .footer { text-align: center; font-size: 12px; }
                //             .content { font-size: 14px; }
                //         </style>
                //     </head>
                //     <body>
                //         <div class='header'>" . ($business->letter_template_header) . "</div>
                //         <div class='footer'>" . ($business->letter_template_footer) . "</div>
                //         <div class='content'>" . ($student_letter->letter_content) . "</div>
                //     </body>
                // </html>
                // ";

                // // Add the HTML content to the Word document (convert HTML to Word format)
                // \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlContent);

                // // Save the Word document to a string (in memory)
                // $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

                // return response()->stream(
                //     function () use ($phpWord, $objWriter) {
                //         $objWriter->save('php://output'); // Output directly to the browser
                //     },
                //     200,
                //     [
                //         'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                //         'Content-Disposition' => 'attachment; filename="letter.docx"',
                //     ]
                // );

            } else {
                throw new Exception("some thing went wrong");
            }



        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Post(
     *      path="/v1.0/student-letters/send",
     *      operationId="sendStudentLetterEmail",
     *      tags={"student_letters"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to send pdf via email",
     *      description="This method is to send pdf via email",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"first_Name"},
     *             @OA\Property(property="student_letter_id", type="string", format="string",example="student_letter_id"),
     *             @OA\Property(property="student_id", type="string", format="string",example="student_id"),
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocessable Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="Not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */


    public function sendStudentLetterEmail(DownloadStudentLetterPdfRequest $request)
    {
        try {
            $request_data = $request->validated();

            $student_letter = StudentLetter::where([
                "id" => $request_data["student_letter_id"]
            ])->first();

            $student = Student::where([
                "id" => $request_data["student_id"]
            ])
                ->first();

                $emailSent = true;
                $errorMessage = null;

                if (env('SEND_EMAIL') == true) {
                    // Log email sender actions
                    // $this->checkEmailSender(auth()->user()->id, 0);

                    $pdf = PDF::loadView('email.dynamic_mail', ['html_content' => $student_letter->letter_content]);

                    try {
                        // Send the email
                        Mail::to($student->email)->send(new StudentLetterMail($pdf));

                    } catch (Exception $e) {
                        // Set error message
                        $errorMessage = $e->getMessage();
                        $emailSent = false;
                    } finally {
                        // Ensure that email sender actions are always logged
                        // $this->storeEmailSender(auth()->user()->id, 0);
                    }
                }

                // Update the student_letter record if email was sent
                if ($emailSent) {
                    $student_letter->email_sent = true;
                    $student_letter->save();
                }

                // Create a history record
                StudentLetterEmailHistory::create([
                    'student_letter_id' => $student_letter->id,
                    'sent_at' => $emailSent ? now() : null,
                    'recipient_email' => $student->email,
                    'email_content' => $student_letter->letter_content,
                    'status' => $emailSent ? 'sent' : 'failed',
                    'error_message' => $emailSent ? null : $errorMessage
                ]);


            return response()->json(['message' => 'Email sent successfully.'], 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Put(
     *      path="/v1.0/student-letters",
     *      operationId="updateStudentLetter",
     *      tags={"student_letters"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update student letters ",
     *      description="This method is to update student letters ",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *      @OA\Property(property="id", type="number", format="number", example="1"),
     * @OA\Property(property="issue_date", type="string", format="string", example="issue_date"),
     * @OA\Property(property="letter_content", type="string", format="string", example="letter_content"),
     * @OA\Property(property="status", type="string", format="string", example="status"),
     * @OA\Property(property="sign_required", type="string", format="string", example="sign_required"),
     * @OA\Property(property="letter_view_required", type="string", format="string", example="letter_view_required"),
     *
     * @OA\Property(property="student_id", type="string", format="string", example="student_id"),
     * @OA\Property(property="attachments", type="string", format="string", example="attachments"),
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

    public function updateStudentLetter(StudentLetterUpdateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            $this->isModuleEnabled("letter_template");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('student_letter_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }
                $request_data = $request->validated();



                $student_letter_query_params = [
                    "id" => $request_data["id"],
                ];

                $student_letter = StudentLetter::where($student_letter_query_params)->first();

                if ($student_letter) {
                    $student_letter->fill(collect($request_data)->only([

                        "issue_date",
                        "letter_content",
                        "status",
                        "sign_required",
                        "letter_view_required",
                        "student_id",
                        "attachments",
                        // "is_default",
                        // "is_active",
                        // "business_id",
                        // "created_by"
                    ])->toArray());
                    $student_letter->save();
                } else {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }




                return response($student_letter, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }
       /**
     *
     * @OA\Put(
     *      path="/v1.0/student-letters/view",
     *      operationId="updateStudentLetterView",
     *      tags={"student_letters"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update student letters ",
     *      description="This method is to update student letters ",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *      @OA\Property(property="id", type="number", format="number", example="1"),
     * @OA\Property(property="letter_viewed", type="string", format="string", example="issue_date"),
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

     public function updateStudentLetterView(StudentLetterUpdateViewRequest $request)
     {

         try {
             $this->storeActivity($request, "DUMMY activity", "DUMMY description");
             $this->isModuleEnabled("letter_template");
             return DB::transaction(function () use ($request) {

                //  if (!$request->user()->hasPermissionTo('student_letter_update')) {
                //      return response()->json([
                //          "message" => "You can not perform this action"
                //      ], 401);
                //  }
                 $request_data = $request->validated();



                 $student_letter_query_params = [
                     "id" => $request_data["id"],
                     "student_id" => auth()->user()->id,
                 ];

                 $student_letter = StudentLetter::where($student_letter_query_params)->first();

                 if ($student_letter) {
                     $student_letter->fill(collect($request_data)->only([
                         "letter_viewed",
                         // "is_default",
                         // "is_active",
                         // "business_id",
                         // "created_by"
                     ])->toArray());
                     $student_letter->save();
                 } else {
                     return response()->json([
                         "message" => "something went wrong."
                     ], 500);
                 }




                 return response($student_letter, 201);
             });
         } catch (Exception $e) {
             error_log($e->getMessage());
             return $this->sendError($e, 500, $request);
         }
     }


     public function query_filters($query)
     {

         return   $query->where('student_letters.business_id', auth()->user()->business_id)

         ->when(!empty(request()->id), function ($query) {
             return $query->where('student_letters.id', request()->id);
         })

         ->when(!empty(request()->start_issue_date), function ($query) {
             return $query->where('student_letters.issue_date', ">=", request()->start_issue_date);
         })

         ->when(!empty(request()->end_issue_date), function ($query) {
             return $query->where('student_letters.issue_date', "<=", (request()->end_issue_date . ' 23:59:59'));
         })

         ->when(!empty(request()->status), function ($query) {
             return $query->where('student_letters.status', request()->status);
         })

         ->when(
             empty(request()->student_id),
             function ($query) {
                 return $query;
             },
             function ($query) {
                 return $query->where('student_letters.student_id', request()->student_id);
             }
         )

         ->when(!empty(request()->search_key), function ($query) {
             return $query->where(function ($query) {
                 $term = request()->search_key;
                 $query
                     ->where("student_letters.letter_content", "like", "%" . $term . "%")
                     ->orWhere("student_letters.status", "like", "%" . $term . "%");
             });
         })

         ->when(!empty(request()->start_date), function ($query) {
             return $query->where('student_letters.created_at', ">=", request()->start_date);
         })

         ->when(!empty(request()->end_date), function ($query) {
             return $query->where('student_letters.created_at', "<=", (request()->end_date . ' 23:59:59'));
         })
         ;
     }
 /**
     *
     * @OA\Get(
     *      path="/v1.0/student-letters-get",
     *      operationId="getStudentLetters",
     *      tags={"student_letters"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *         @OA\Parameter(
     *         name="start_issue_date",
     *         in="query",
     *         description="start_issue_date",
     *         required=true,
     *  example="6"
     *      ),
     *         @OA\Parameter(
     *         name="end_issue_date",
     *         in="query",
     *         description="end_issue_date",
     *         required=true,
     *  example="6"
     *      ),
     *         @OA\Parameter(
     *         name="letter_content",
     *         in="query",
     *         description="letter_content",
     *         required=true,
     *  example="6"
     *      ),
     *         @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="status",
     *         required=true,
     *  example="6"
     *      ),
     *         @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="per_page",
     *         required=true,
     *  example="6"
     *      ),

     *     @OA\Parameter(
     * name="is_active",
     * in="query",
     * description="is_active",
     * required=true,
     * example="1"
     * ),
     *     @OA\Parameter(
     * name="start_date",
     * in="query",
     * description="start_date",
     * required=true,
     * example="2019-06-29"
     * ),
     * *  @OA\Parameter(
     * name="end_date",
     * in="query",
     * description="end_date",
     * required=true,
     * example="2019-06-29"
     * ),
     * *  @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=true,
     * example="search_key"
     * ),
     * *  @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=true,
     * example="ASC"
     * ),
     * *  @OA\Parameter(
     * name="id",
     * in="query",
     * description="id",
     * required=true,
     * example="ASC"
     * ),
     * *  @OA\Parameter(
     * name="is_single_search",
     * in="query",
     * description="is_single_search",
     * required=true,
     * example="ASC"
     * ),
     *    * *  @OA\Parameter(
     * name="student_id",
     * in="query",
     * description="student_id",
     * required=true,
     * example="ASC"
     * ),
     *
     *      summary="This method is to get student letters  ",
     *      description="This method is to get student letters ",
     *

     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

     public function getStudentLetters(Request $request)
     {
         try {
             $this->storeActivity($request, "DUMMY activity", "DUMMY description");
             $this->isModuleEnabled("letter_template");
             if (!$request->user()->hasPermissionTo('student_letter_view')) {
                 return response()->json([
                     "message" => "You can not perform this action"
                 ], 401);
             }

             $query = StudentLetter::with([
                 "student" => function ($query) {
                     $query->select("students.id", "students.first_Name", "students.middle_Name", "students.last_Name");
                 }
             ]);
             $query = $this->query_filters($query);
             $student_letters = $this->retrieveData($query, "id","student_letters");


             return response()->json($student_letters, 200);
         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }
    /**
     *
     * @OA\Get(
     *      path="/v2.0/student-letters-get",
     *      operationId="getStudentLettersV2",
     *      tags={"student_letters"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *         @OA\Parameter(
     *         name="start_issue_date",
     *         in="query",
     *         description="start_issue_date",
     *         required=true,
     *  example="6"
     *      ),
     *         @OA\Parameter(
     *         name="end_issue_date",
     *         in="query",
     *         description="end_issue_date",
     *         required=true,
     *  example="6"
     *      ),
     *         @OA\Parameter(
     *         name="letter_content",
     *         in="query",
     *         description="letter_content",
     *         required=true,
     *  example="6"
     *      ),
     *         @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="status",
     *         required=true,
     *  example="6"
     *      ),
     *         @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="per_page",
     *         required=true,
     *  example="6"
     *      ),

     *     @OA\Parameter(
     * name="is_active",
     * in="query",
     * description="is_active",
     * required=true,
     * example="1"
     * ),
     *     @OA\Parameter(
     * name="start_date",
     * in="query",
     * description="start_date",
     * required=true,
     * example="2019-06-29"
     * ),
     * *  @OA\Parameter(
     * name="end_date",
     * in="query",
     * description="end_date",
     * required=true,
     * example="2019-06-29"
     * ),
     * *  @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=true,
     * example="search_key"
     * ),
     * *  @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=true,
     * example="ASC"
     * ),
     * *  @OA\Parameter(
     * name="id",
     * in="query",
     * description="id",
     * required=true,
     * example="ASC"
     * ),
     * *  @OA\Parameter(
     * name="is_single_search",
     * in="query",
     * description="is_single_search",
     * required=true,
     * example="ASC"
     * ),
     *    * *  @OA\Parameter(
     * name="student_id",
     * in="query",
     * description="student_id",
     * required=true,
     * example="ASC"
     * ),
     *
     *      summary="This method is to get student letters  ",
     *      description="This method is to get student letters ",
     *

     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

    public function getStudentLettersV2(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            $this->isModuleEnabled("letter_template");
            if (!$request->user()->hasPermissionTo('student_letter_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $query = StudentLetter::with([
                "student" => function ($query) {
                    $query->select("students.id", "students.first_Name", "students.middle_Name", "students.last_Name");
                }
            ]);
            $query = $this->query_filters($query)
            ->select(
                "student_letters.id",
                'student_letters.issue_date',
                'student_letters.status',
                'student_letters.letter_content',
                'student_letters.sign_required',
                'student_letters.student_id',
            );
            $student_letters = $this->retrieveData($query, "id","student_letters");


            return response()->json($student_letters, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }




        /**
     *
     * @OA\Get(
     *      path="/v1.0/student-letters-histories",
     *      operationId="getStudentLetterHistories",
     *      tags={"student_letters"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

*     @OA\Parameter(
 *         name="student_letter_id",
 *         in="query",
 *         description="Filter by student letter ID.",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *  *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         description="Filter by status.",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 * *     @OA\Parameter(
 *         name="start_sent_at",
 *         in="query",
 *         description="Filter by start sent date. Format: YYYY-MM-DD",
 *         required=false,
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="end_sent_at",
 *         in="query",
 *         description="Filter by end sent date. Format: YYYY-MM-DD",
 *         required=false,
 *         @OA\Schema(type="string", format="date")
 *     ),
     *
     *         @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="per_page",
     *         required=true,
     *  example="6"
     *      ),

     *     @OA\Parameter(
     * name="is_active",
     * in="query",
     * description="is_active",
     * required=true,
     * example="1"
     * ),
     *     @OA\Parameter(
     * name="start_date",
     * in="query",
     * description="start_date",
     * required=true,
     * example="2019-06-29"
     * ),
     * *  @OA\Parameter(
     * name="end_date",
     * in="query",
     * description="end_date",
     * required=true,
     * example="2019-06-29"
     * ),
     * *  @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=true,
     * example="search_key"
     * ),
     * *  @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=true,
     * example="ASC"
     * ),
     * *  @OA\Parameter(
     * name="id",
     * in="query",
     * description="id",
     * required=true,
     * example="ASC"
     * ),
     * *  @OA\Parameter(
     * name="is_single_search",
     * in="query",
     * description="is_single_search",
     * required=true,
     * example="ASC"
     * ),
     *    * *  @OA\Parameter(
     * name="student_id",
     * in="query",
     * description="student_id",
     * required=true,
     * example="ASC"
     * ),
     *
     *      summary="This method is to get student letters  ",
     *      description="This method is to get student letters ",
     *

     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

     public function getStudentLetterHistories(Request $request)
     {
         try {
             $this->storeActivity($request, "DUMMY activity", "DUMMY description");
             $this->isModuleEnabled("letter_template");
             if (!$request->user()->hasPermissionTo('student_letter_view')) {
                 return response()->json([
                     "message" => "You can not perform this action"
                 ], 401);
             }

             $all_manager_department_ids = $this->get_all_departments_of_manager();

             $student_letter_histories = StudentLetterEmailHistory::


                 when(
                    empty($request->student_id),
                    function ($query) use ($request) {
                        return $query
                        // ->whereHas("student_letters", function ($query)  {
                        //     $query->whereNotIn("students.id", [auth()->user()->id]);
                        // })
                        ;
                    },
                    function ($query) use ($request) {
                        return $query->whereHas("student_letters", function ($query) use($request) {
                            $query->whereIn("students.id", [$request->student_id]);
                        });

                    }
                )
                ->when(!empty($request->student_letter_id), function ($query) use ($request) {
                    return $query->where('student_letter_email_histories.student_letter_id', $request->student_letter_id);
                })
                 ->when(!empty($request->id), function ($query) use ($request) {
                     return $query->where('student_letter_email_histories.id', $request->id);
                 })
                 ->when(!empty($request->start_sent_at), function ($query) use ($request) {
                     return $query->where('student_letter_email_histories.sent_at', ">=", $request->start_sent_at);
                 })
                 ->when(!empty($request->end_sent_at), function ($query) use ($request) {
                     return $query->where('student_letter_email_histories.sent_at', "<=", ($request->end_sent_at . ' 23:59:59'));
                 })
                 ->when(!empty($request->status), function ($query) use ($request) {
                     return $query->where('student_letter_email_histories.status', $request->status);
                 })
                 ->when(!empty($request->search_key), function ($query) use ($request) {
                     return $query->where(function ($query) use ($request) {
                         $term = $request->search_key;
                         $query

                             ->where("student_letter_email_histories.letter_content", "like", "%" . $term . "%")
                             ->orWhere("student_letter_email_histories.recipient_email", "like", "%" . $term . "%");
                     });
                 })

                 ->when(!empty($request->start_date), function ($query) use ($request) {
                     return $query->where('student_letter_email_histories.created_at', ">=", $request->start_date);
                 })
                 ->when(!empty($request->end_date), function ($query) use ($request) {
                     return $query->where('student_letter_email_histories.created_at', "<=", ($request->end_date . ' 23:59:59'));
                 })
                 ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                     return $query->orderBy("student_letter_email_histories.id", $request->order_by);
                 }, function ($query) {
                     return $query->orderBy("student_letter_email_histories.id", "DESC");
                 })
                 ->when($request->filled("is_single_search") && $request->boolean("is_single_search"), function ($query) use ($request) {
                     return $query->first();
                 }, function ($query) {
                     return $query->when(!empty(request()->per_page), function ($query) {
                         return $query->paginate(request()->per_page);
                     }, function ($query) {
                         return $query->get();
                     });
                 });

             if ($request->filled("is_single_search") && empty($student_letters)) {
                 throw new Exception("No data found", 404);
             }


             return response()->json($student_letter_histories, 200);
         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }

    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/student-letters/{ids}",
     *      operationId="deleteStudentLettersByIds",
     *      tags={"student_letters"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="ids",
     *         in="path",
     *         description="ids",
     *         required=true,
     *  example="1,2,3"
     *      ),
     *      summary="This method is to delete student letter by id",
     *      description="This method is to delete student letter by id",
     *

     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

    public function deleteStudentLettersByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            $this->isModuleEnabled("letter_template");
            if (!$request->user()->hasPermissionTo('student_letter_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $idsArray = explode(',', $ids);
            $existingIds = StudentLetter::whereIn('id', $idsArray)
                ->where('student_letters.business_id', auth()->user()->business_id)

                ->select('id')
                ->get()
                ->pluck('id')
                ->toArray();
            $nonExistingIds = array_diff($idsArray, $existingIds);

            if (!empty($nonExistingIds)) {

                return response()->json([
                    "message" => "Some or all of the specified data do not exist."
                ], 404);
            }





            StudentLetter::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
