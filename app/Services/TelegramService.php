<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private $botToken;
    private $chatId;
    private $client;

    public function __construct()
    {
        $this->botToken = '8208505568:AAHXrwie82QuFQ1nUpi_hGOolR3UQLntjP0';
        // For private groups/channels, you need to use the chat ID (negative number)
        // You'll need to get this by adding the bot to the group and using getUpdates
        $this->chatId = '-1003098115317'; // This is a placeholder - you'll need the actual chat ID
        $this->client = new Client();
    }

    /**
     * Send message to Telegram channel
     */
    public function sendMessage(string $message, array $options = []): bool
    {
        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";
            
            $params = [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => $options['parse_mode'] ?? 'HTML',
                'disable_web_page_preview' => $options['disable_web_page_preview'] ?? true,
            ];

            $response = $this->client->post($url, [
                'json' => $params,
                'timeout' => 30,
            ]);

            $result = json_decode($response->getBody(), true);
            
            if ($result['ok']) {
                Log::info('Telegram message sent successfully', [
                    'chat_id' => $this->chatId,
                    'message_length' => strlen($message)
                ]);
                return true;
            } else {
                Log::error('Telegram API returned error', [
                    'error' => $result,
                    'message' => $message
                ]);
                return false;
            }

        } catch (RequestException $e) {
            Log::error('Failed to send Telegram message', [
                'error' => $e->getMessage(),
                'message' => $message,
                'chat_id' => $this->chatId
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Unexpected error sending Telegram message', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
            return false;
        }
    }

    /**
     * Send service proposal status change notification
     */
    public function sendProposalStatusNotification(
        string $serviceName,
        string $oldStatus,
        string $newStatus,
        string $actorName,
        int $proposalId,
        float $amount = null
    ): bool {
        $oldStatusText = $this->getStatusText($oldStatus);
        $newStatusText = $this->getStatusText($newStatus);
        
        $message = "🔄 <b>Thay đổi trạng thái đề xuất dịch vụ</b>\n\n";
        $message .= "📋 <b>Dịch vụ:</b> {$serviceName}\n";
        $message .= "👤 <b>Người thực hiện:</b> {$actorName}\n";
        $message .= "📊 <b>Trạng thái:</b> {$oldStatusText} ➜ {$newStatusText}\n";
        
        if ($amount) {
            $message .= "💰 <b>Số tiền:</b> " . number_format($amount, 0, ',', '.') . " VNĐ\n";
        }
        
        $message .= "🆔 <b>ID:</b> #{$proposalId}\n";
        $message .= "⏰ <b>Thời gian:</b> " . now()->format('d/m/Y H:i:s');

        return $this->sendMessage($message);
    }

    /**
     * Send service proposal created notification
     */
    public function sendProposalCreatedNotification(
        string $serviceName,
        string $creatorName,
        int $proposalId,
        float $amount,
        string $targetDomain = null
    ): bool {
        $message = "✨ <b>Đề xuất dịch vụ mới</b>\n\n";
        $message .= "📋 <b>Dịch vụ:</b> {$serviceName}\n";
        $message .= "👤 <b>Người tạo:</b> {$creatorName}\n";
        $message .= "💰 <b>Số tiền:</b> " . number_format($amount, 0, ',', '.') . " VNĐ\n";
        
        if ($targetDomain) {
            $message .= "🌐 <b>Domain:</b> {$targetDomain}\n";
        }
        
        $message .= "📊 <b>Trạng thái:</b> Chờ duyệt\n";
        $message .= "🆔 <b>ID:</b> #{$proposalId}\n";
        $message .= "⏰ <b>Thời gian:</b> " . now()->format('d/m/Y H:i:s');

        return $this->sendMessage($message);
    }

    /**
     * Get status display text
     */
    private function getStatusText(string $status): string
    {
        return match($status) {
            'pending' => '⏳ Chờ duyệt',
            'approved' => '✅ Đã duyệt',
            'rejected' => '❌ Từ chối',
            'partner_confirmed' => '🤝 Đối tác xác nhận',
            'partner_completed' => '🏁 Đối tác hoàn thành',
            'seoer_confirmed' => '👨‍💻 Seoer xác nhận',
            'admin_completed' => '🔧 Quản lý xác nhận hoàn thành',
            'payment_confirmed' => '💳 Trợ lý xác nhận hoàn thành',
            'completed' => '🎉 Đã hoàn thành',
            default => $status,
        };
    }

    /**
     * Test connection to Telegram
     */
    public function testConnection(): array
    {
        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/getMe";
            
            $response = $this->client->get($url, ['timeout' => 10]);
            $result = json_decode($response->getBody(), true);
            
            if ($result['ok']) {
                return [
                    'success' => true,
                    'bot_info' => $result['result'],
                    'message' => 'Kết nối Telegram thành công'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result,
                    'message' => 'Telegram API trả về lỗi'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Không thể kết nối đến Telegram'
            ];
        }
    }

    /**
     * Get updates to find chat ID
     */
    public function getUpdates(): array
    {
        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/getUpdates";
            
            $response = $this->client->get($url, ['timeout' => 10]);
            $result = json_decode($response->getBody(), true);
            
            if ($result['ok']) {
                return [
                    'success' => true,
                    'updates' => $result['result'],
                    'message' => 'Lấy updates thành công'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result,
                    'message' => 'Telegram API trả về lỗi'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Không thể lấy updates từ Telegram'
            ];
        }
    }
}
