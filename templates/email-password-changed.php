<?php
/**
 * AuthMe — Password Changed Email Template
 *
 * Sent to the user after a successful password reset.
 * Variables available:
 *   $authme_user_name  — display name of the user
 *   $authme_site_name  — site name
 *   $authme_site_url   — site URL
 *
 * @package AuthMe
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$site_name = esc_html( get_bloginfo( 'name' ) );
$site_url  = esc_url( home_url() );
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Password Changed — <?php echo $site_name; ?></title>
<style>
    body { margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
    .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.06); }
    .header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 40px 40px 32px; text-align: center; }
    .header .icon { width: 56px; height: 56px; background: rgba(255,255,255,0.15); border-radius: 50%; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; }
    .header h1 { color: #ffffff; font-size: 22px; font-weight: 700; margin: 0; letter-spacing: -0.3px; }
    .header p { color: rgba(255,255,255,0.8); font-size: 14px; margin: 8px 0 0; }
    .body { padding: 36px 40px; }
    .body p { color: #334155; font-size: 15px; line-height: 1.7; margin: 0 0 16px; }
    .notice-box { background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 10px; padding: 16px 20px; margin: 24px 0; }
    .notice-box p { color: #b91c1c; font-size: 14px; margin: 0; font-weight: 500; }
    .cta-btn { display: block; width: fit-content; margin: 24px auto 0; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 13px 28px; border-radius: 10px; font-size: 15px; font-weight: 600; text-align: center; }
    .footer { border-top: 1px solid #e2e8f0; padding: 20px 40px; text-align: center; }
    .footer p { color: #94a3b8; font-size: 12px; margin: 0; line-height: 1.5; }
    .footer a { color: #2563eb; text-decoration: none; }
</style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                 stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>
        <h1>Password Changed</h1>
        <p><?php echo $site_name; ?></p>
    </div>

    <div class="body">
        <p>Hi <?php echo esc_html( $authme_user_name ); ?>,</p>
        <p>Your account password on <strong><?php echo $site_name; ?></strong> was successfully changed.</p>
        <p>If you made this change, you can safely ignore this email. Your account is secure.</p>

        <div class="notice-box">
            <p>⚠️ If you did <strong>not</strong> make this change, please contact us immediately and reset your password right away.</p>
        </div>

        <a href="<?php echo $site_url; ?>/authme" class="cta-btn">Visit <?php echo $site_name; ?></a>
    </div>

    <div class="footer">
        <p>This is an automated security notification from <a href="<?php echo $site_url; ?>"><?php echo $site_name; ?></a>.<br>
        Please do not reply to this email.</p>
    </div>

</div>
</body>
</html>
