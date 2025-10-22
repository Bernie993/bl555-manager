<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $notification->title }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .notification-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .message {
            font-size: 16px;
            color: #555;
            margin-bottom: 25px;
            line-height: 1.8;
        }
        .details {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
        .details h4 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .details p {
            margin: 5px 0;
            font-size: 14px;
        }
        .action-button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
            transition: background-color 0.3s;
        }
        .action-button:hover {
            background-color: #0056b3;
            color: white;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
        .timestamp {
            color: #666;
            font-size: 14px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">BL555 Management System</div>
            <div class="notification-icon">
                @if(str_contains($notification->type, 'service'))
                    🔧
                @elseif(str_contains($notification->type, 'proposal'))
                    📋
                @elseif(str_contains($notification->type, 'withdrawal'))
                    💰
                @else
                    🔔
                @endif
            </div>
        </div>

        <div class="title">{{ $notification->title }}</div>
        
        <div class="message">
            {{ $notification->message }}
        </div>

        @if($notification->data)
        <div class="details">
            <h4>Chi tiết</h4>
            @if(isset($notification->data['service_name']))
                <p><strong>Dịch vụ:</strong> {{ $notification->data['service_name'] }}</p>
            @endif
            @if(isset($notification->data['amount']))
                <p><strong>Số tiền:</strong> {{ number_format($notification->data['amount']) }} VNĐ</p>
            @endif
            @if(isset($notification->data['proposal_names']) && $notification->data['proposal_names'])
                <p><strong>Đề xuất:</strong> {{ $notification->data['proposal_names'] }}</p>
            @endif
            @if(isset($notification->data['status']))
                <p><strong>Trạng thái:</strong> {{ $notification->data['status'] }}</p>
            @endif
            @if(isset($notification->data['old_status']) && isset($notification->data['new_status']))
                <p><strong>Trạng thái cũ:</strong> {{ $notification->data['old_status'] }}</p>
                <p><strong>Trạng thái mới:</strong> {{ $notification->data['new_status'] }}</p>
            @endif
            @if(isset($notification->data['actor_name']))
                <p><strong>Thực hiện bởi:</strong> {{ $notification->data['actor_name'] }}</p>
            @endif
        </div>
        @endif

        @if($actionUrl && $actionUrl !== config('app.url'))
        <div style="text-align: center;">
            <a href="{{ $actionUrl }}" class="action-button">
                Xem chi tiết
            </a>
        </div>
        @endif

        <div class="timestamp">
            Thời gian: {{ $notification->created_at->format('d/m/Y H:i:s') }}
        </div>

        <div class="footer">
            <p>Đây là email tự động từ hệ thống BL555. Vui lòng không trả lời email này.</p>
            <p>Nếu bạn có thắc mắc, vui lòng liên hệ với quản trị viên.</p>
        </div>
    </div>
</body>
</html>
