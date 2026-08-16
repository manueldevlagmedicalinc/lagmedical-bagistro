<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lag Medical backup failed</title>
</head>
<body style="margin: 0; padding: 0; background: #f5f5f5; font-family: Arial, sans-serif; color: #111827;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background: #f5f5f5; padding: 24px;">
        <tr>
            <td align="center">
                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 680px; background: #ffffff; border-radius: 10px; overflow: hidden; border: 1px solid #e5e7eb;">
                    <tr>
                        <td style="padding: 24px; border-bottom: 1px solid #e5e7eb;">
                            <h1 style="margin: 0; font-size: 22px; line-height: 1.3; color: #111827;">Lag Medical backup failed</h1>
                            <p style="margin: 8px 0 0; color: #6b7280; font-size: 14px;">A backup run failed and needs review.</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse: collapse; font-size: 14px;">
                                <tr>
                                    <td style="padding: 10px; border: 1px solid #e5e7eb; background: #f9fafb; font-weight: bold; width: 160px;">Status</td>
                                    <td style="padding: 10px; border: 1px solid #e5e7eb; color: #b91c1c; font-weight: bold;">{{ ucfirst($backupLog->status) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px; border: 1px solid #e5e7eb; background: #f9fafb; font-weight: bold;">Started At</td>
                                    <td style="padding: 10px; border: 1px solid #e5e7eb;">{{ $backupLog->started_at?->format('Y-m-d H:i:s') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px; border: 1px solid #e5e7eb; background: #f9fafb; font-weight: bold;">Completed At</td>
                                    <td style="padding: 10px; border: 1px solid #e5e7eb;">{{ $backupLog->completed_at?->format('Y-m-d H:i:s') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px; border: 1px solid #e5e7eb; background: #f9fafb; font-weight: bold;">Remote Path</td>
                                    <td style="padding: 10px; border: 1px solid #e5e7eb;">{{ $backupLog->remote_path ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px; border: 1px solid #e5e7eb; background: #f9fafb; font-weight: bold;">Message</td>
                                    <td style="padding: 10px; border: 1px solid #e5e7eb; white-space: pre-line;">{{ $backupLog->message ?: '-' }}</td>
                                </tr>
                            </table>

                            <p style="margin: 24px 0 0; font-size: 14px; color: #374151;">Review the backup admin page for the full log history:</p>

                            <p style="margin: 12px 0 0;">
                                <a href="{{ route('admin.settings.backups.index') }}" style="display: inline-block; background: #2563eb; color: #ffffff; text-decoration: none; padding: 10px 14px; border-radius: 6px; font-size: 14px; font-weight: bold;">
                                    Open Backup Logs
                                </a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
