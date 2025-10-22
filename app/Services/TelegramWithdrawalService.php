<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class TelegramWithdrawalService
{
    private $botToken;
    private $chatId;
    private $client;

    public function __construct()
    {
        $this->botToken = '7970698782:AAFwjXdMPYNzOz3j3xakSEriqhCVYPXmZvw';
        // Chat ID cho channel "Thông báo BL555 Manage - Yêu cầu rút tiền"
        $this->chatId = '-1003179254288';
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
                Log::info('Telegram withdrawal message sent successfully', [
                    'chat_id' => $this->chatId,
                    'message_length' => strlen($message)
                ]);
                return true;
            } else {
                Log::error('Telegram withdrawal API returned error', [
                    'error' => $result,
                    'message' => $message
                ]);
                return false;
            }

        } catch (RequestException $e) {
            Log::error('Failed to send Telegram withdrawal message', [
                'error' => $e->getMessage(),
                'message' => $message,
                'chat_id' => $this->chatId
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Unexpected error sending Telegram withdrawal message', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
            return false;
        }
    }

    /**
     * Send withdrawal request notification
     */
    public function sendWithdrawalRequestNotification(
        int $withdrawalId,
        string $partnerName,
        float $amount,
        string $actorName,
        array $proposalNames = []
    ): bool {
        $message = "💰 <b>Yêu cầu rút tiền mới</b>\n\n";
        $message .= "🆔 <b>ID:</b> #{$withdrawalId}\n";
        $message .= "👤 <b>Đối tác:</b> {$partnerName}\n";
        $message .= "💵 <b>Số tiền:</b> " . number_format($amount, 0, ',', '.') . " VNĐ\n";
        
        if (!empty($proposalNames)) {
            $message .= "📋 <b>Đề xuất:</b> " . implode(', ', $proposalNames) . "\n";
        }
        
        $message .= "👨‍💼 <b>Người tạo:</b> {$actorName}\n";
        $message .= "📊 <b>Trạng thái:</b> ⏳ Chờ xử lý\n";
        $message .= "⏰ <b>Thời gian:</b> " . now()->format('d/m/Y H:i:s');

        return $this->sendMessage($message);
    }

    /**
     * Send withdrawal status change notification
     */
    public function sendWithdrawalStatusNotification(
        int $withdrawalId,
        string $partnerName,
        float $amount,
        string $oldStatus,
        string $newStatus,
        string $actorName,
        array $proposalNames = []
    ): bool {
        $oldStatusText = $this->getStatusText($oldStatus);
        $newStatusText = $this->getStatusText($newStatus);
        
        $message = "🔄 <b>Thay đổi trạng thái rút tiền</b>\n\n";
        $message .= "🆔 <b>ID:</b> #{$withdrawalId}\n";
        $message .= "👤 <b>Đối tác:</b> {$partnerName}\n";
        $message .= "💵 <b>Số tiền:</b> " . number_format($amount, 0, ',', '.') . " VNĐ\n";
        
        if (!empty($proposalNames)) {
            $message .= "📋 <b>Đề xuất:</b> " . implode(', ', $proposalNames) . "\n";
        }
        
        $message .= "👨‍💼 <b>Người thực hiện:</b> {$actorName}\n";
        $message .= "📊 <b>Trạng thái:</b> {$oldStatusText} ➜ {$newStatusText}\n";
        $message .= "⏰ <b>Thời gian:</b> " . now()->format('d/m/Y H:i:s');

        return $this->sendMessage($message);
    }

    /**
     * Get status display text
     */
    private function getStatusText(string $status): string
    {
        return match($status) {
            'pending' => '⏳ Chờ xử lý',
            'assistant_completed' => '✅ Trợ lý hoàn thành',
            'partner_confirmed' => '🤝 Đối tác xác nhận',
            'completed' => '🎉 Đã hoàn thành',
            'cancelled' => '❌ Đã hủy',
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
                    'message' => 'Kết nối Telegram withdrawal bot thành công'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result,
                    'message' => 'Telegram withdrawal API trả về lỗi'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Không thể kết nối đến Telegram withdrawal bot'
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
                    'message' => 'Lấy withdrawal updates thành công'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $result,
                    'message' => 'Telegram withdrawal API trả về lỗi'
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Không thể lấy withdrawal updates từ Telegram'
            ];
        }
    }
}
