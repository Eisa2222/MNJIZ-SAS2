<?php

namespace App\Http\Controllers\Microsoft;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MailService;
use App\Services\MicrosoftGraphBaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmailController extends Controller
{
    protected $microsoftGraphService;
    protected $mailService;

    public function __construct(MicrosoftGraphBaseService $microsoftGraphService, MailService $mailService)
    {
        $this->microsoftGraphService = $microsoftGraphService;
        $this->mailService = $mailService;
    }

    protected function getValidAccessToken(User $user)
    {
        // تحقق من صلاحية رمز الوصول وقم بتجديده إذا لزم الأمر
        if ($user->microsoft_token_expires && $user->microsoft_token_expires->lt(now())) {
            $newAccessToken = $this->microsoftGraphService->refreshAccessToken($user);
            if (!$newAccessToken) {
                return null;
            }
            return $newAccessToken;
        }

        return $user->microsoft_token;
    }

    public function index()
    {
        $user = Auth::user();

        if (!$user->microsoft_token) {
            return redirect()->route('microsoft.login')->with('error', 'يرجى ربط حساب Microsoft الخاص بك.');
        }

        $accessToken = $this->getValidAccessToken($user);

        if (!$accessToken) {
            return redirect()->route('dashboard')->with('error', 'فشل في تجديد رمز الوصول. يرجى إعادة ربط حساب Microsoft.');
        }

        $folders = ['inbox', 'sentitems', 'drafts', 'junkemail', 'deleteditems', 'archive'];
        $counts = [];

        foreach ($folders as $folder) {
            $counts[$folder] = $this->getTotalCount($accessToken, $folder);
        }

        $emails = $this->mailService->getEmails($accessToken, 10, 'inbox');

        // تحسين معالجة البيانات
        $emails = collect($emails)->map(function ($email) {
            return [
                'id' => $email->getId(),
                'subject' => $email->getSubject(),
                'from' => [
                    'name' => optional($email->getFrom()->getEmailAddress())->getName(),
                    'address' => optional($email->getFrom()->getEmailAddress())->getAddress(),
                ],
                'isRead' => $email->getIsRead(),
                'dateTime' => optional($email->getReceivedDateTime())->format('c'),
                'flagStatus' => optional($email->getFlag())->getFlagStatus(),
            ];
        });

        return view('onedrive.email.emails', [
            'emails' => $emails,
            'counts' => $counts,
        ]);
    }

    public function read($id)
    {
        $user = Auth::user();

        if (!$user->microsoft_token) {
            return response()->json(['error' => 'يرجى ربط حساب Microsoft الخاص بك.'], 401);
        }

        $accessToken = $this->getValidAccessToken($user);

        if (!$accessToken) {
            return response()->json(['error' => 'فشل في تجديد رمز الوصول. يرجى إعادة ربط حساب Microsoft.'], 401);
        }

        $email = $this->mailService->getEmailById($accessToken, $id);

        if ($email) {
            $dateTime = $email->getReceivedDateTime() ?: $email->getSentDateTime() ?: $email->getCreatedDateTime();
            $dateTime = $dateTime ? $dateTime->format('c') : null;

            $emailData = [
                'id' => $email->getId(),
                'subject' => $email->getSubject(),
                'from' => [
                    'name' => optional($email->getFrom()->getEmailAddress())->getName(),
                    'address' => optional($email->getFrom()->getEmailAddress())->getAddress(),
                ],
                'toRecipients' => $email->getToRecipients(),
                'ccRecipients' => $email->getCcRecipients(),
                'bccRecipients' => $email->getBccRecipients(),
                'body' => optional($email->getBody())->getContent(),
                'isRead' => $email->getIsRead(),
                'dateTime' => $dateTime,
                'flagStatus' => optional($email->getFlag())->getFlagStatus(),
            ];

            return response()->json($emailData);
        } else {
            return response()->json(['error' => 'Cannot fetch email'], 500);
        }
    }

    protected function getTotalCount($accessToken, $folder)
    {
        $graph = new \Microsoft\Graph\Graph();
        $graph->setAccessToken($accessToken);

        try {
            $response = $graph->createRequest("GET", "/me/mailFolders/{$folder}")
                ->setReturnType(\Microsoft\Graph\Model\MailFolder::class)
                ->execute();

            return $response->getTotalItemCount() ?? 0;
        } catch (\Exception $e) {
            Log::error("Error fetching total count for folder {$folder}: " . $e->getMessage());
            return 0;
        }
    }

    protected function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'to' => 'required|email',
            'cc' => 'nullable|string',
            'bcc' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (!$user->microsoft_token) {
            return response()->json(['error' => 'يرجى ربط حساب Microsoft الخاص بك.'], 401);
        }

        $accessToken = $this->getValidAccessToken($user);

        if (!$accessToken) {
            return response()->json(['error' => 'فشل في تجديد رمز الوصول. يرجى إعادة ربط حساب Microsoft.'], 401);
        }

        $subject = $request->input('subject');
        $body = $request->input('body');
        $to = $request->input('to');
        $toName = $request->input('to_name');
        $cc = $request->input('cc');
        $bcc = $request->input('bcc');
        $attachments = $request->file('attachments', []);

        // بناء قائمة المستلمين
        $toRecipients = [
            [
                'emailAddress' => [
                    'address' => $to,
                    'name' => $toName ?? '',
                ],
            ],
        ];

        $ccRecipients = $this->processRecipients($cc);
        $bccRecipients = $this->processRecipients($bcc);

        $attachmentsData = $this->processAttachments($attachments);

        $result = $this->mailService->sendEmailTo($accessToken, $subject, $body, $toRecipients, $ccRecipients, $bccRecipients, $attachmentsData);

        if ($result) {
            return response()->json(['success' => 'تم إرسال البريد الإلكتروني بنجاح.']);
        } else {
            Log::error('Failed to send email for user ID: ' . $user->id);
            return response()->json(['error' => 'فشل في إرسال البريد الإلكتروني.'], 500);
        }
    }

    public function reply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_id' => 'required|string',
            'comment' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (!$user->microsoft_token) {
            return response()->json(['error' => 'يرجى ربط حساب Microsoft الخاص بك.'], 401);
        }

        $accessToken = $this->getValidAccessToken($user);

        if (!$accessToken) {
            return response()->json(['error' => 'فشل في تجديد رمز الوصول. يرجى إعادة ربط حساب Microsoft.'], 401);
        }

        $messageId = $request->input('message_id');
        $comment = $request->input('comment');

        $result = $this->mailService->replyToEmail($accessToken, $messageId, $comment);

        if ($result) {
            return response()->json(['success' => 'تم الرد على البريد الإلكتروني بنجاح.']);
        } else {
            Log::error('Failed to reply to email ID: ' . $messageId . ' for user ID: ' . $user->id);
            return response()->json(['error' => 'فشل في الرد على البريد الإلكتروني.'], 500);
        }
    }

    public function delete($id)
    {
        $user = Auth::user();

        if (!$user->microsoft_token) {
            return response()->json(['error' => 'يرجى ربط حساب Microsoft الخاص بك.'], 401);
        }

        $accessToken = $this->getValidAccessToken($user);

        if (!$accessToken) {
            return response()->json(['error' => 'فشل في تجديد رمز الوصول. يرجى إعادة ربط حساب Microsoft.'], 401);
        }

        $result = $this->mailService->deleteEmail($accessToken, $id);

        if ($result) {
            return response()->json(['success' => 'تم حذف البريد الإلكتروني بنجاح.']);
        } else {
            Log::error('Failed to delete email ID: ' . $id . ' for user ID: ' . $user->id);
            return response()->json(['error' => 'فشل في حذف البريد الإلكتروني.'], 500);
        }
    }

    public function getFolderEmails($folder)
    {
        $user = Auth::user();

        if (!$user->microsoft_token) {
            return response()->json(['error' => 'يرجى ربط حساب Microsoft الخاص بك.'], 401);
        }

        $validFolders = ['inbox', 'sentitems', 'drafts', 'junkemail', 'deleteditems', 'archive'];
        if (!in_array(strtolower($folder), $validFolders)) {
            return response()->json(['error' => 'تم تحديد مجلد غير صالح.'], 400);
        }

        $accessToken = $this->getValidAccessToken($user);

        if (!$accessToken) {
            return response()->json(['error' => 'فشل في تجديد رمز الوصول. يرجى إعادة ربط حساب Microsoft.'], 401);
        }

        $emails = $this->mailService->getEmails($accessToken, 10, $folder);

        $emailData = collect($emails)->map(function ($email) use ($folder) {
            $dateTime = null;
            switch (strtolower($folder)) {
                case 'sentitems':
                    $dateTime = $email->getSentDateTime();
                    break;
                case 'drafts':
                    $dateTime = $email->getCreatedDateTime();
                    break;
                default:
                    $dateTime = $email->getReceivedDateTime();
                    break;
            }

            return [
                'id' => $email->getId(),
                'subject' => $email->getSubject(),
                'from' => [
                    'name' => optional($email->getFrom()->getEmailAddress())->getName(),
                    'address' => optional($email->getFrom()->getEmailAddress())->getAddress(),
                ],
                'isRead' => $email->getIsRead(),
                'dateTime' => $dateTime ? $dateTime->format('c') : null,
                'flagStatus' => optional($email->getFlag())->getFlagStatus(),
            ];
        });

        return response()->json(['emails' => $emailData]);
    }

    private function processRecipients($recipients)
    {
        $recipientList = [];
        if ($recipients) {
            $emails = explode(',', $recipients);
            foreach ($emails as $email) {
                $email = trim($email);
                if ($this->validateEmail($email)) {
                    $recipientList[] = [
                        'emailAddress' => [
                            'address' => $email,
                            'name' => '',
                        ],
                    ];
                }
            }
        }
        return $recipientList;
    }

    private function processAttachments($attachments)
    {
        $attachmentsData = [];
        foreach ($attachments as $file) {
            $attachmentsData[] = [
                '@odata.type' => '#microsoft.graph.fileAttachment',
                'name' => $file->getClientOriginalName(),
                'contentBytes' => base64_encode(file_get_contents($file->getRealPath())),
            ];
        }
        return $attachmentsData;
    }


    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emailIds' => 'required|array',
            'emailIds.*' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (!$user->microsoft_token) {
            return response()->json(['error' => 'يرجى ربط حساب Microsoft الخاص بك.'], 401);
        }

        $accessToken = $this->getValidAccessToken($user);

        if (!$accessToken) {
            return response()->json(['error' => 'فشل في تجديد رمز الوصول. يرجى إعادة ربط حساب Microsoft.'], 401);
        }

        $emailIds = $request->input('emailIds');

        $result = $this->mailService->bulkDeleteEmails($accessToken, $emailIds);

        if ($result) {
            return response()->json(['success' => 'تم حذف الرسائل بنجاح.']);
        } else {
            Log::error('Failed to bulk delete emails for user ID: ' . $user->id);
            return response()->json(['error' => 'فشل في حذف الرسائل.'], 500);
        }
    }
}
