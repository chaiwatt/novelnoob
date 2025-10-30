<?php

namespace App\Http\Controllers;

use Throwable;

use App\Models\Novel;
use App\Models\NovelChapter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class NovelGenerationController extends Controller
{
    private const PLOT_COST = 5;
    private const OUTLINE_COST = 25;
    private const CHAPTER_COST = 10;

    /**
     * Helper method to check user type and redirect if not 'writer'.
     *
     * @return \Illuminate\Http\RedirectResponse|null
     */
    private function checkUserAccessForApi(): ?JsonResponse
    {
        // dd(Auth::user());
        // 1. ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
        if (!Auth::check()) {
            // ถ้าไม่ได้ล็อกอิน: ส่ง 401 Unauthorized กลับไป
            return response()->json([
                'error' => 'UNAUTHENTICATED',
                'message' => 'คุณต้องล็อกอินเพื่อเข้าถึงฟังก์ชันนี้',
                'redirect_to' => route('login') // ส่ง URL กลับไปให้ JS Redirect เอง
            ], 401);
        }

        $user = Auth::user();

        // 2. ตรวจสอบประเภทผู้ใช้ (อนุญาตเฉพาะ 'writer')
        if ($user->type !== 'writer') {
            
            $redirectRoute = ($user->type === 'admin') 
                             ? route('admin.dashboard') 
                             : route('dashboard.index'); // หรือ '/'

            // ถ้าล็อกอินแล้วแต่ไม่ใช่ 'writer': ส่ง 403 Forbidden กลับไป
            return response()->json([
                'error' => 'FORBIDDEN_ROLE',
                'message' => 'บัญชีของคุณไม่มีสิทธิ์ในการสร้างนิยาย',
                'redirect_to' => $redirectRoute // ส่ง URL Redirect ตาม role กลับไป
            ], 403);
        }

        // ถ้าเป็น writer ให้ส่งค่า null กลับไป เพื่อให้เมธอดหลักทำงานต่อ
        return null;
    }

    private function checkCredits(int $cost): ?JsonResponse
    {
        $user = Auth::user();
        if ($user->credits < $cost) {
            return response()->json([
                'error' => 'INSUFFICIENT_CREDITS',
                'message' => "เครดิตไม่เพียงพอ (ต้องการ {$cost} เครดิต, คุณมี {$user->credits} เครดิต)",
                // อาจเพิ่ม redirect_to หน้าเติมเงิน
            ], 402); // 402 Payment Required
        }
        return null;
    }
    /**
     * Generate a novel plot based on user input.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generatePlot(Request $request)
    {
        
        // ⭐️ เรียกใช้ Helper Function เพื่อตรวจสอบสิทธิ์
        $errorResponse = $this->checkUserAccessForApi();

        if ($errorResponse) {
            return $errorResponse;
        }

        $creditError = $this->checkCredits(self::PLOT_COST);
        if ($creditError) return $creditError;



        set_time_limit(300);
        // 1. ตรวจสอบข้อมูลที่ส่งมา (เหมือนเดิม)
        $validator = Validator::make($request->all(), [
            'title_prompt' => 'required|string|max:255',
            'style_text' => 'required|string',
            'plot_context' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // 2. ดึงข้อมูลและ API Key
        $apiKeysString = env('GEMINI_API_KEY');

        if (!$apiKeysString) {
            // กรณีไม่มีการกำหนดค่า API Key เลย
            return response()->json(['error' => 'Gemini API key is not configured.'], 500);
        }

        // แยกสตริงที่คั่นด้วยคอมมาออกเป็นอาเรย์ของคีย์
        $apiKeys = explode(',', $apiKeysString);

        // กรองเพื่อลบช่องว่างที่อาจมีจากการแยก (trim) และลบคีย์ที่ว่างเปล่า
        $apiKeys = array_map('trim', $apiKeys);
        $apiKeys = array_filter($apiKeys);
        $apiKey = $apiKeys[array_rand($apiKeys)];

        if (!$apiKey) {
            return response()->json(['error' => 'Gemini API key is not configured.'], 500);
        }

        $titlePrompt = $request->input('title_prompt');
        $styleText = $request->input('style_text');
        $plotContext = $request->input('plot_context');

        // 3. สร้าง Prompt สำหรับส่งให้ AI
        $prompt = $this->createPlotPrompt($titlePrompt, $styleText, $plotContext);

        // 4. เรียกใช้ Gemini API
        try {
            $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ];

            // $response = Http::post($apiUrl, $payload);
            $response = Http::timeout(300)->post($apiUrl, $payload);

            // 5. จัดการกับการตอบกลับจาก API
            if ($response->successful()) {
                // ดึงข้อความที่ AI สร้างขึ้นจาก JSON response
                // candidates[0] -> content -> parts[0] -> text
                $generatedPlot = $response->json('candidates.0.content.parts.0.text');

                if ($generatedPlot) {
                    Auth::user()->decrement('credits', self::PLOT_COST);
                    return response()->json([
                        'status' => 'success',
                        'plot' => $generatedPlot,
                        'credits_remaining' => Auth::user()->fresh()->credits
                    ]);
                } else {
                    // กรณีที่โครงสร้าง response ไม่ถูกต้อง
                    return response()->json(['error' => 'Failed to parse Gemini API response. '], 500);
                }
            } else {
                // กรณีที่ API trả về lỗi (เช่น 400, 500)
                return response()->json([
                    'error' => 'Error from Gemini API. ',
                    'details' => $response->json() // ส่งรายละเอียด error กลับไปด้วย
                ], $response->status());
            }

        } catch (Throwable $e) {
            // จัดการกับข้อผิดพลาดในการเชื่อมต่อ
            report($e); // บันทึก error ลง log
            return response()->json([
                'error' => 'Could not connect to the generation service.',
                'details' => $e->getMessage() // เพิ่ม message จาก exception เพื่อช่วยดีบัก
            ], 503);
        }
    }

    /**
     * Creates a structured prompt for the Gemini API to generate a novel plot.
     *
     * @param string $titlePrompt
     * @param string $styleText
     * @param string $plotContext
     * @return string
     */
    private function createPlotPrompt(string $titlePrompt, string $styleText, string $plotContext): string
    {
        // การสร้าง Prompt ที่ดีและมีโครงสร้างชัดเจน จะช่วยให้ AI เข้าใจและสร้างผลลัพธ์ได้ดีขึ้น
        return <<<PROMPT
        คุณคือผู้ช่วยนักเขียนนิยายมืออาชีพ มีหน้าที่สร้างพล็อตเรื่องย่อที่น่าสนใจ
        จากข้อมูลต่อไปนี้ โปรดสร้างพล็อตเรื่องย่อความยาวประมาณ 2-3 ย่อหน้า ให้มีความน่าติดตามและชวนให้ผู้อ่านอยากรู้เรื่องราวต่อไป

        --- ข้อมูลสำหรับสร้างพล็อต ---
        1.  **แนวทางชื่อเรื่อง:** "{$titlePrompt}"
        2.  **สไตล์การเขียน/แนวเรื่อง:** "{$styleText}"
        3.  **บริบทและฉากของเรื่อง:** "{$plotContext}"
        ---

        **คำสั่ง:**
        - เขียนพล็อตเรื่องให้กระชับและดึงดูดใจ
        - ไม่ต้องเขียนคำนำหรือบทสรุปอื่นๆ นอกจากตัวพล็อตเรื่อง
        - ตอบกลับเป็นภาษาไทยเท่านั้น
        PROMPT;
    }


        /**
     * Generate a full novel outline, save it, and create chapter records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateOutline(Request $request)
    {
        
        // ⭐️ เรียกใช้ Helper Function เพื่อตรวจสอบสิทธิ์
        $errorResponse = $this->checkUserAccessForApi();

        if ($errorResponse) {
            return $errorResponse;
        }

        $creditError = $this->checkCredits(self::OUTLINE_COST);
        if ($creditError) return $creditError;

        set_time_limit(300);

        $validator = Validator::make($request->all(), [
            'title_prompt' => 'required|string|max:255',
            'character_nationality' => 'required|string',
            'setting_prompt' => 'required|string|min:20',
            'style_to_use' => 'required|string',
            'act_count' => 'required|integer|in:3,4,5',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

                $apiKeysString = env('GEMINI_API_KEY');

        if (!$apiKeysString) {
            // กรณีไม่มีการกำหนดค่า API Key เลย
            return response()->json(['error' => 'Gemini API key is not configured.'], 500);
        }

        // แยกสตริงที่คั่นด้วยคอมมาออกเป็นอาเรย์ของคีย์
        $apiKeys = explode(',', $apiKeysString);

        // กรองเพื่อลบช่องว่างที่อาจมีจากการแยก (trim) และลบคีย์ที่ว่างเปล่า
        $apiKeys = array_map('trim', $apiKeys);
        $apiKeys = array_filter($apiKeys);
        $apiKey = $apiKeys[array_rand($apiKeys)];

        if (!$apiKey) {
            return response()->json(['error' => 'Gemini API key is not configured.'], 500);
        }

        $blueprintData = $request->all();
        
        $jsonSchema = [
            'type' => 'OBJECT',
            'properties' => [
                'story' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'title' => ['type' => 'STRING'],
                        'theme' => ['type' => 'STRING'],
                        'acts' => [
                            'type' => 'ARRAY',
                            'items' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'act' => ['type' => 'INTEGER'],
                                    'summary' => ['type' => 'STRING'],
                                    'chapters' => [
                                        'type' => 'ARRAY',
                                        'items' => [
                                            'type' => 'OBJECT',
                                            'properties' => [
                                                'no' => ['type' => 'INTEGER'],
                                                'title' => ['type' => 'STRING'],
                                                'summary' => ['type' => 'STRING'],
                                            ],
                                            'required' => ['no', 'title', 'summary']
                                        ]
                                    ]
                                ],
                                'required' => ['act', 'summary', 'chapters']
                            ]
                        ]
                    ],
                    'required' => ['title', 'theme', 'acts']
                ],
                'story_bible' => [
                    'type' => 'OBJECT',
                    'properties' => [
                        'characters' => [
                            'type' => 'ARRAY',
                            'items' => [
                                'type' => 'OBJECT',
                                'properties' => [
                                    'name' => ['type' => 'STRING'],
                                    'role' => ['type' => 'STRING'],
                                ],
                                'required' => ['name', 'role']
                            ]
                        ],
                        'world_and_lore' => [
                            'type' => 'ARRAY',
                            'items' => ['type' => 'STRING']
                        ]
                    ],
                    'required' => ['characters', 'world_and_lore']
                ]
            ],
            'required' => ['story', 'story_bible']
        ];
        
        $prompt = $this->createOutlinePrompt($blueprintData);
        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;
        
        $payload = [
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'response_schema' => $jsonSchema,
            ],
        ];

        try {
            $response = Http::timeout(240)->post($apiUrl, $payload);

            if ($response->successful()) {
                $responseText = $this->cleanJsonResponse($response->json('candidates.0.content.parts.0.text'));
                $outlineData = json_decode($responseText, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    Auth::user()->decrement('credits', self::OUTLINE_COST);
                    $novel = Novel::create([
                        'user_id' => Auth::id() ?? 1,
                        'title' => $outlineData['story']['title'] ?? $request->input('title_prompt'),
                        'status' => 'outline_generated',
                        'outline_data' => $outlineData,
                        'title_prompt' => $request->input('title_prompt'),
                        'character_nationality' => $request->input('character_nationality'),
                        'setting_prompt' => $request->input('setting_prompt'),
                        'style' => $request->input('style_to_use'),
                        'act_count' => $request->input('act_count'),
                        'style_guide' => $request->input('custom_style_guide'),
                        'genre_rules' => $request->input('custom_genre_rules'),
                    ]);

                    foreach ($outlineData['story']['acts'] as $act) {
                        foreach ($act['chapters'] as $chapterData) {
                            NovelChapter::create([
                                'novel_id' => $novel->id,
                                'chapter_number' => $chapterData['no'],
                                'title' => $chapterData['title'],
                                'status' => 'pending',
                            ]);
                        }
                    }
                    $novel->load('chapters');
                    return response()->json([
                        'novel' => $novel,
                        'credits_remaining' => Auth::user()->fresh()->credits // ส่งเครดิตล่าสุดกลับไปด้วย
                    ]);
                } else {
                    return response()->json(['error' => 'Failed to decode JSON from AI response.', 'raw_response' => $responseText], 500);
                }
            } else {
                return response()->json(['error' => 'Error from Gemini API.', 'details' => $response->json()], $response->status());
            }

        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'error' => 'Could not connect to the generation service.',
                'details' => $e->getMessage() // เพิ่ม message จาก exception เพื่อช่วยดีบัก
            ], 503);
        }
    }

    private function createOutlinePrompt(array $data): string
    {
        extract($data);

        // --- EDIT: Map act_count to total chapters ---
        $chapterMap = [
            '3' => 15,
            '4' => 20,
            '5' => 25,
        ];
        $totalChapters = $chapterMap[$act_count] ?? 15; // Default to 15 if not found

        $structureInstruction = "สำคัญมาก: โครงเรื่องต้องมีทั้งหมด {$act_count} องก์ (acts) และมีจำนวนบทรวมทั้งสิ้น {$totalChapters} บท โดยเฉลี่ยบทให้กระจายไปในแต่ละองก์อย่างเหมาะสม";

        return implode("\n\n", [
            "คุณคือผู้ช่วยนักเขียนนิยายมืออาชีพ ภารกิจของคุณคือการสร้างโครงเรื่อง (Plot Outline) ทั้งหมดสำหรับนิยายเรื่องใหม่ โดยต้องสร้างเนื้อหาให้สอดคล้องกับข้อมูลและกฎต่อไปนี้:",
            "--- โครงสร้างที่ต้องปฏิบัติตาม ---",
            $structureInstruction, // Add the new explicit instruction here
            "--- แนวคิดหลักของเรื่อง ---",
            "- **แนวทางชื่อเรื่อง:** {$title_prompt}",
            "- **สัญชาติตัวละคร:** {$character_nationality}",
            "- **ฉาก/เรื่องราว:** {$setting_prompt}",
            "--- กฎการสร้างสรรค์เนื้อหา ---",
            "- **ชื่อเรื่อง (title):** ต้องน่าสนใจและสอดคล้องกับแนวคิดหลัก",
            "- **ตัวละคร (characters):** ต้องมีชื่อและบทบาทที่สอดคล้องกับสัญชาติที่กำหนด",
            "- **บทสรุปของบท (summary):** แต่ละบทต้องมีบทสรุป (summary) ที่มีความยาวประมาณ 100 คำ",
            "- **การทับศัพท์:** หากมีการใช้ชื่อตัวละครหรือสถานที่ภาษาต่างประเทศ ให้ทับศัพท์เป็นภาษาไทยเสมอ (เช่น John -> จอห์น)",
            "- **เนื้อหาโดยรวม:** สร้างเนื้อหาใหม่ทั้งหมดให้สอดคล้องกับแนวคิดหลักที่ให้มา"
        ]);
    }

    /**
     * Cleans markdown formatting from a JSON response string.
     *
     * @param string|null $responseText
     * @return string
     */
    private function cleanJsonResponse(?string $responseText): string
    {
        if ($responseText === null) {
            return '';
        }
        if (preg_match('/```json\s*([\s\S]+?)\s*```/', $responseText, $matches)) {
            return trim($matches[1]);
        }
        return trim($responseText);
    }
    /**
     * Write content for a specific novel chapter using AI.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NovelChapter  $chapter
     * @return \Illuminate\Http\JsonResponse
     */
/**
     * Write content for a specific novel chapter using AI.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NovelChapter  $chapter
     * @return \Illuminate\Http\JsonResponse
     */
    public function writeChapter(Request $request, NovelChapter $chapter)
    {
        // ⭐️ เรียกใช้ Helper Function เพื่อตรวจสอบสิทธิ์
        $errorResponse = $this->checkUserAccessForApi();

        if ($errorResponse) {
            return $errorResponse;
        }

        $creditError = $this->checkCredits(self::CHAPTER_COST);
        if ($creditError) return $creditError;

        set_time_limit(600);

        $novel = $chapter->novel;
        $previousChapter = $chapter->chapter_number > 1
            ? NovelChapter::where('novel_id', $novel->id)->where('chapter_number', $chapter->chapter_number - 1)->first()
            : null;

        $writingPrompt = $this->createChapterWritingPrompt($novel, $chapter, $previousChapter);

        try {
            $apiKeysString = env('GEMINI_API_KEY');

            if (!$apiKeysString) {
                // กรณีไม่มีการกำหนดค่า API Key เลย
                return response()->json(['error' => 'Gemini API key is not configured.'], 500);
            }

            // แยกสตริงที่คั่นด้วยคอมมาออกเป็นอาเรย์ของคีย์
            $apiKeys = explode(',', $apiKeysString);

            // กรองเพื่อลบช่องว่างที่อาจมีจากการแยก (trim) และลบคีย์ที่ว่างเปล่า
            $apiKeys = array_map('trim', $apiKeys);
            $apiKeys = array_filter($apiKeys);
            $apiKey = $apiKeys[array_rand($apiKeys)];
            $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;


            $writingPayload = ['contents' => [['parts' => [['text' => $writingPrompt]]]]];
            $writingResponse = Http::timeout(300)->post($apiUrl, $writingPayload);

            if (!$writingResponse->successful()) {
                return response()->json(['error' => 'Error from Gemini API during content writing.', 'details' => $writingResponse->json()], $writingResponse->status());
            }

            $generatedContent = $writingResponse->json('candidates.0.content.parts.0.text');
            // dd($writingResponse, $apiKey);
            $summaryPrompt = $this->createContentSummaryPrompt($generatedContent);
            $summaryPayload = ['contents' => [['parts' => [['text' => $summaryPrompt]]]]];
            $summaryResponse = Http::timeout(300)->post($apiUrl, $summaryPayload);
            $newSummary = $summaryResponse->successful()
                ? $summaryResponse->json('candidates.0.content.parts.0.text')
                : 'Unable to generate summary.';

            $paragraphs = preg_split('/\n\s*\n/', $generatedContent, -1, PREG_SPLIT_NO_EMPTY);
            $endingSummary = implode("\n\n", array_slice($paragraphs, -2));

            $chapter->update([
                'content' => $generatedContent,
                'summary' => $newSummary,
                'ending_summary' => $endingSummary,
                'status' => 'completed',
            ]);
            Auth::user()->decrement('credits', self::CHAPTER_COST);
            return response()->json([
                'status' => 'success', 
                'chapter' => $chapter,
                'credits_remaining' => Auth::user()->fresh()->credits
            ]);

        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'error' => 'Could not connect to the generation service.',
                'details' => $e->getMessage() // เพิ่ม message จาก exception เพื่อช่วยดีบัก
            ], 503);
        }
    }

    /**
     * Creates a prompt for the AI to write a chapter's content, with clear separation for the first chapter vs. subsequent chapters.
     */
    private function createChapterWritingPrompt(Novel $novel, NovelChapter $chapter, ?NovelChapter $previousChapter): string
    {
        $initialSummary = 'ไม่มีข้อมูลสรุปเบื้องต้น';
        $outline = $novel->outline_data;
        if (isset($outline['story']['acts']) && is_array($outline['story']['acts'])) {
            foreach ($outline['story']['acts'] as $act) {
                if (isset($act['chapters']) && is_array($act['chapters'])) {
                    foreach ($act['chapters'] as $chapterData) {
                        if (isset($chapterData['no']) && $chapterData['no'] == $chapter->chapter_number) {
                            $initialSummary = $chapterData['summary'] ?? 'ไม่มีข้อมูลสรุปเบื้องต้น';
                            break 2;
                        }
                    }
                }
            }
        }

        // CASE 2: The Very First Chapter
        if (!$previousChapter) {
            $styleMapping = [
                'style_detective' => 'แนวสืบสวนสอบสวน',
                'style_erotic' => 'แนวอิโรติก',
                'style_romance' => 'แนวโรแมนติก',
                'style_sci-fi' => 'แนววิทยาศาสตร์',
                'style_drama' => 'แนวดราม่า',
            ];
            $genreName = $styleMapping[$novel->style] ?? '';
            
            $promptParts = [
                "คุณคือผู้ช่วยนักเขียนนิยายมืออาชีพ ภารกิจของคุณคือการเริ่มต้นเขียนบทแรกของนิยายเรื่องใหม่",
                "--- ข้อมูลภาพรวมของนิยาย ---",
                "- **ชื่อเรื่อง:** " . $novel->title,
                "- **แนวเรื่อง:** " . $genreName,
                "- **ธีมเรื่อง:** " . ($novel->outline_data['story']['theme'] ?? 'N/A'),
                "--------------------------------------------------------------------",
                "--- เป้าหมายหลักของบทแรกนี้คือ ---",
                $initialSummary,
                "--------------------------------------------------------------------",
                "--- !! คำสั่งสำหรับบทแรก !! ---",
                "1. โปรดเริ่มต้นเรื่องราว{$genreName}อย่างน่าประทับใจและตรงตามบรรยากาศของแนวเรื่อง",
                "2. ดำเนินเรื่องตาม 'เป้าหมายหลักของบทแรกนี้' ที่ให้มา",
                "3. เขียนให้ได้ความยาวประมาณ " . $chapter->word_count . " คำ",
                "4. ไม่ต้องเขียนชื่อบทหรือคำว่า 'บทที่' ซ้ำอีก ให้เริ่มต้นเขียนเนื้อหาได้เลย",
                "5. ตอบกลับมาเป็นเนื้อหานิยายเท่านั้น ไม่ต้องมีคำอธิบายอื่นใด",
            ];
        } 
        // CASE 1: Subsequent Chapters
        else {
            $promptParts = [
                "คุณคือผู้ช่วยนักเขียนนิยายมืออาชีพ ภารกิจที่สำคัญที่สุดของคุณ คือการเขียน 'ย่อหน้าถัดไป' ของเรื่องราวที่มีอยู่ โดยต้องรักษาความต่อเนื่องของฉากและอารมณ์อย่างสมบูรณ์แบบ",
                "--- เนื้อเรื่องล่าสุด (ฉากปัจจุบัน) ---",
                $previousChapter->content,
                "--- จบเนื้อเรื่องล่าสุด ---",
                "\n--- กฎการเขียน (ต้องปฏิบัติตามอย่างเคร่งครัด) ---",
                "**กฎข้อที่ 1 (สำคัญที่สุด):** หน้าที่ของคุณคือเขียน **ย่อหน้าถัดไป** เท่านั้น",
                "**กฎข้อที่ 2 (ห้ามเด็ดขาด):** **ห้ามคัดลอกหรือเขียนทวนประโยคสุดท้าย** ของ 'เนื้อเรื่องล่าสุด' โดยเด็ดขาด ให้อ่านเพื่อทำความเข้าใจบริบท แล้วเริ่มเขียนย่อหน้าใหม่ต่อจากนั้นทันที",
                "**กฎข้อที่ 3:** รักษาความต่อเนื่องของฉากและอารมณ์อย่างเคร่งครัด - ตัวละครต้องอยู่ในสถานที่เดิมและมีอารมณ์ต่อเนื่องจากย่อหน้าสุดท้าย ห้ามย้ายฉากหรือเปลี่ยนอารมณ์ของตัวละครอย่างกะทันหันโดยไม่มีเหตุผล",
                "**กฎข้อที่ 4:** ใช้ 'เป้าหมายของบทนี้' เป็นเพียงทิศทางไกลๆ เท่านั้น ไม่ใช่สิ่งที่ต้องทำให้สำเร็จในย่อหน้าถัดไป",
                "**กฎข้อที่ 5:** เขียนให้ได้ความยาวประมาณ " . $chapter->word_count . " คำ",
                "**กฎข้อที่ 6:** ตอบกลับมาเป็นเนื้อหานิยายเท่านั้น ไม่ต้องมีคำอธิบายอื่นใด",
                "\n--- เป้าหมายของบทนี้ (ทิศทาง) ---",
                $initialSummary,
            ];
        }

        return implode("\n\n", $promptParts);
    }



    /**
     * Creates a prompt to summarize a chapter's content.
     */
    private function createContentSummaryPrompt(string $chapterContent): string
    {
        return "คุณคือบรรณาธิการมืออาชีพ\nจากเนื้อหานิยายต่อไปนี้ โปรดสรุปใจความสำคัญของบทนี้ให้ได้ใจความ กระชับ และสมบูรณ์ที่สุด ความยาว 1 ย่อหน้า ประมาณ 150-200 คำ\n\n--- เนื้อหานิยาย ---\n{$chapterContent}\n---\n\nคำสั่ง: จงตอบกลับมาเป็นบทสรุปเท่านั้น ไม่ต้องมีคำอธิบายอื่นใด";
    }

        /**
     * Update a chapter's content manually from user input.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NovelChapter  $chapter
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateChapter(Request $request, NovelChapter $chapter)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Update the content field with the new text from the user
        $chapter->content = $request->input('content');
        $chapter->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Chapter updated successfully.',
            'chapter' => $chapter
        ]);
    }

        public function downloadTxt(Request $request, Novel $novel)
    {
        // 1. ตรวจสอบสิทธิ์ (Middleware จัดการ 'auth' and 'writer' แล้ว)
        // แต่เราต้องตรวจสอบว่า Novel นี้เป็นของ User ที่ login อยู่
        if ($novel->user_id !== Auth::id()) {
            return response()->json(['error' => 'Forbidden. You do not own this novel.'], 403);
        }

        // 2. ดึงเฉพาะบทที่ 'completed'
        $chapters = $novel->chapters()
            ->where('status', 'completed')
            ->orderBy('chapter_number', 'asc')
            ->get();

        // 3. ตรวจสอบว่ามีบทที่เสร็จแล้วหรือไม่
        if ($chapters->isEmpty()) {
            return response()->json(['error' => 'No completed chapters to download.'], 404);
        }

        // 4. รวบรวมเนื้อหา
        $fileContent = $novel->title . "\n\n";
        $fileContent .= "========================================\n\n";

        foreach ($chapters as $chapter) {
            $fileContent .= "--- บทที่ {$chapter->chapter_number}: {$chapter->title} ---\n\n";
            $fileContent .= $chapter->content;
            $fileContent .= "\n\n\n"; // เพิ่มช่องว่าง 3 บรรทัดระหว่างบท
        }

        // 5. ส่ง Response กลับไปเป็น text/plain
        // JavaScript (Fetch API) จะรับข้อความนี้ไปสร้างเป็น Blob และไฟล์ .txt
        return response($fileContent, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }


}
