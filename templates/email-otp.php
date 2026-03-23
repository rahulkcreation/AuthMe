<?php
/**
 * AuthMe OTP Email Template
 *
 * Professional HTML email template used for sending
 * OTP codes for both registration and login.
 *
 * Variables available:
 *   $authme_otp_code    — 6-digit OTP code
 *   $authme_otp_purpose — 'registration' or 'login'
 *
 * @package AuthMe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( $authme_otp_purpose === 'login' ) {
    $purpose_label = 'Login Verification';
} elseif ( $authme_otp_purpose === 'password_reset' ) {
    $purpose_label = 'Reset Password Verification';
} else {
    $purpose_label = 'Registration Verification';
}
$site_name     = get_bloginfo( 'name' );
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( $purpose_label ); ?></title>
</head>
<body style="margin:0; padding:0; background-color:#f8fafc; font-family:-apple-system,BlinkMacSystemFont,'Inter','Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.06);">

                    <!-- Header Bar -->
                    <tr>
                        <td style="background-color:#2563eb; padding:28px 32px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:22px; font-weight:700; letter-spacing:-0.3px;">
                                🔐 <?php echo esc_html( $site_name ); ?>
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:36px 32px 24px;">
                            <h2 style="margin:0 0 8px; font-size:20px; font-weight:700; color:#0f172a; text-align:center;">
                                <?php echo esc_html( $purpose_label ); ?>
                            </h2>
                            <p style="margin:0 0 28px; font-size:14px; color:#64748b; text-align:center; line-height:1.6;">
                                Use the following one-time password to complete your <?php echo esc_html( strtolower( str_replace( ' Verification', '', $purpose_label ) ) ); ?>.
                                This code is valid for <strong>60 seconds</strong>.
                            </p>

                            <!-- OTP Code Display -->
                            <div style="text-align:center; margin:0 0 28px;">
                                <div style="display:inline-block; background-color:#f1f5f9; border:2px dashed #cbd5e1; border-radius:12px; padding:18px 40px;">
                                    <span style="font-size:36px; font-weight:800; letter-spacing:8px; color:#0f172a; font-family:'SF Mono','Fira Code','Consolas',monospace;">
                                        <?php echo esc_html( $authme_otp_code ); ?>
                                    </span>
                                </div>
                            </div>

                            <p style="margin:0; font-size:13px; color:#94a3b8; text-align:center; line-height:1.6;">
                                If you did not request this code, you can safely ignore this email.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px 32px; border-top:1px solid #e2e8f0; text-align:center;">
                            <p style="margin:0; font-size:12px; color:#94a3b8;">
                                &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $site_name ); ?> — Powered by AuthMe
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
