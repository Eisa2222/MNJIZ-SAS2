<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Message;

class MailService extends MicrosoftGraphBaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * جلب رسائل البريد من مجلد معين.
     *
     * @param string $accessToken
     * @param int $top
     * @param string $folder
     * @return array
     */
    public function getEmails($accessToken, $top = 20, $folder = 'inbox')
    {
        if (!$accessToken) {
            Log::error('Access token is required to fetch emails.');
            return [];
        }

        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        try {
            // تحسين الاستعلام باستخدام المعلمات المناسبة
            $url = "/me/mailFolders/{$folder}/messages?\$top={$top}&\$select=id,subject,from,toRecipients,bodyPreview,isRead,receivedDateTime,flag";
            $response = $graph->createRequest("GET", $url)
                ->setReturnType(Message::class)
                ->execute();

            return $response;
        } catch (GraphException $e) {
            Log::error('GraphException fetching emails: ' . $e->getMessage());
            Log::error('GraphException response: ' . $e->getResponseBody());
            return [];
        } catch (\Exception $e) {
            Log::error('Error fetching emails: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * جلب رسالة بريدية بواسطة معرفها.
     *
     * @param string $accessToken
     * @param string $id
     * @return Message|null
     */
    public function getEmailById($accessToken, $id)
    {
        if (!$accessToken) {
            Log::error('Access token is required to fetch email by ID.');
            return null;
        }

        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        try {
            // تحسين الاستعلام بجلب البيانات المطلوبة فقط
            $email = $graph->createRequest("GET", "/me/messages/{$id}?\$select=id,subject,from,toRecipients,ccRecipients,bccRecipients,body,isRead,receivedDateTime,sentDateTime,createdDateTime,flag")
                ->setReturnType(Message::class)
                ->execute();

            return $email;
        } catch (GraphException $e) {
            Log::error('GraphException fetching email by ID: ' . $e->getMessage());
            Log::error('GraphException response: ' . $e->getResponseBody());
            return null;
        } catch (\Exception $e) {
            Log::error('Error fetching email by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * إرسال بريد إلكتروني.
     *
     * @param string $accessToken
     * @param string $subject
     * @param string $body
     * @param array $toRecipients
     * @param array $ccRecipients
     * @param array $bccRecipients
     * @param array $attachments
     * @return bool
     */
    public function sendEmailTo($accessToken, $subject, $body, $toRecipients, $ccRecipients = [], $bccRecipients = [], $attachments = [])
    {
        if (!$accessToken) {
            Log::error('Access token is required to send email.');
            return false;
        }

        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        try {
            // تنظيف محتوى الجسم وإزالة أي بيانات غير ضرورية
            $bodyContent = strip_tags($body, '<p><a><br><strong><em><ul><ol><li>');

            $message = [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $bodyContent,
                ],
                'toRecipients' => $toRecipients,
            ];

            if (!empty($ccRecipients)) {
                $message['ccRecipients'] = $ccRecipients;
            }

            if (!empty($bccRecipients)) {
                $message['bccRecipients'] = $bccRecipients;
            }

            if (!empty($attachments)) {
                $message['attachments'] = $attachments;
            }

            $requestBody = [
                'message' => $message,
                'saveToSentItems' => true,
            ];

            $graph->createRequest("POST", "/me/sendMail")
                ->attachBody($requestBody)
                ->execute();

            return true;
        } catch (GraphException $e) {
            Log::error('GraphException sending email: ' . $e->getMessage());
            Log::error('GraphException response: ' . $e->getResponseBody());
            return false;
        } catch (\Exception $e) {
            Log::error('Error sending email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * الرد على رسالة بريد إلكتروني.
     *
     * @param string $accessToken
     * @param string $messageId
     * @param string $comment
     * @return bool
     */
    public function replyToEmail($accessToken, $messageId, $comment)
    {
        if (!$accessToken) {
            Log::error('Access token is required to reply to email.');
            return false;
        }

        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        try {
            $replyMessage = [
                'comment' => $comment,
            ];

            $graph->createRequest("POST", "/me/messages/{$messageId}/replyAll")
                ->attachBody($replyMessage)
                ->execute();

            // إرسال الرد بعد التحضير
            $graph->createRequest("POST", "/me/messages/{$messageId}/send")
                ->execute();

            return true;
        } catch (GraphException $e) {
            Log::error('GraphException replying to email: ' . $e->getMessage());
            Log::error('GraphException response: ' . $e->getResponseBody());
            return false;
        } catch (\Exception $e) {
            Log::error('Error replying to email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * حذف رسالة بريد إلكتروني.
     *
     * @param string $accessToken
     * @param string $messageId
     * @return bool
     */
    public function deleteEmail($accessToken, $messageId)
    {
        if (!$accessToken) {
            Log::error('Access token is required to delete email.');
            return false;
        }

        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        try {
            $graph->createRequest("DELETE", "/me/messages/{$messageId}")
                ->execute();

            return true;
        } catch (GraphException $e) {
            Log::error('GraphException deleting email: ' . $e->getMessage());
            Log::error('GraphException response: ' . $e->getResponseBody());
            return false;
        } catch (\Exception $e) {
            Log::error('Error deleting email: ' . $e->getMessage());
            return false;
        }
    }



    public function bulkDeleteEmails($accessToken, array $messageIds)
    {
        if (!$accessToken) {
            Log::error('Access token is required to bulk delete emails.');
            return false;
        }

        $graph = new Graph();
        $graph->setAccessToken($accessToken);

        try {
            // إنشاء طلب Batch لحذف الرسائل بشكل جماعي
            $requests = [];
            $i = 1;
            foreach ($messageIds as $messageId) {
                $requests[] = [
                    'id' => (string)$i,
                    'method' => 'DELETE',
                    'url' => "/me/messages/{$messageId}"
                ];
                $i++;
            }

            $batchRequest = [
                'requests' => $requests
            ];

            $graph->createRequest("POST", "/\$batch")
                ->attachBody($batchRequest)
                ->execute();

            return true;
        } catch (GraphException $e) {
            Log::error('GraphException bulk deleting emails: ' . $e->getMessage());
            Log::error('GraphException response: ' . $e->getResponseBody());
            return false;
        } catch (\Exception $e) {
            Log::error('Error bulk deleting emails: ' . $e->getMessage());
            return false;
        }
    }
}