<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Verify your email – MM Book Store</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">

   <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f1f5f9;padding:40px 16px;">
      <tr>
         <td align="center">

            <!-- Card -->
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:560px;background-color:#ffffff;border-radius:24px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

               <!-- Header -->
               <tr>
                  <td align="center" style="background-color:#0f172a;padding:36px 40px;">
                     <p style="margin:0;font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#94a3b8;">Welcome to</p>
                     <h1 style="margin:8px 0 0 0;font-size:26px;font-weight:900;letter-spacing:2px;text-transform:uppercase;color:#ffffff;">MM Book Store</h1>
                  </td>
               </tr>

               <!-- Body -->
               <tr>
                  <td style="padding:40px 40px 32px 40px;">

                     <p style="margin:0 0 8px 0;font-size:16px;font-weight:700;color:#0f172a;">
                        Hi, {{ $mailData['user_name'] }}!
                     </p>

                     <p style="margin:0 0 20px 0;font-size:14px;font-weight:400;line-height:1.75;color:#475569;">
                        Congratulations! Your MM Book Store account has been created. Click the button below to verify your email address and activate your account.
                     </p>

                     <!-- Divider -->
                     <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
                        <tr>
                           <td style="border-top:1px solid #e2e8f0;"></td>
                        </tr>
                     </table>

                     <!-- CTA Button -->
                     <table cellpadding="0" cellspacing="0" border="0" style="margin:0 auto 28px auto;">
                        <tr>
                           <td align="center" style="background-color:#2563eb;border-radius:12px;">
                              <a href="{{ $mailData['verification_url'] }}"
                                 style="display:inline-block;padding:14px 40px;font-size:14px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#ffffff;text-decoration:none;">
                                 Verify Email Address
                              </a>
                           </td>
                        </tr>
                     </table>

                     <!-- Expiry notice -->
                     <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
                        <tr>
                           <td style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 18px;">
                              <p style="margin:0;font-size:13px;font-weight:400;line-height:1.6;color:#64748b;">
                                 ⏳ &nbsp;This verification link will expire in <strong style="color:#0f172a;">24 hours</strong>. If it expires, you can request a new one from the login page.
                              </p>
                           </td>
                        </tr>
                     </table>

                     <!-- Divider -->
                     <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
                        <tr>
                           <td style="border-top:1px solid #e2e8f0;"></td>
                        </tr>
                     </table>

                     <p style="margin:0;font-size:13px;font-weight:400;line-height:1.6;color:#94a3b8;">
                        If you didn't create this account, you can safely ignore this email.
                     </p>

                  </td>
               </tr>

               <!-- Footer -->
               <tr>
                  <td style="background-color:#f8fafc;border-top:1px solid #e2e8f0;padding:24px 40px;">
                     <p style="margin:0;font-size:13px;font-weight:600;color:#0f172a;">MM Book Store</p>
                     <p style="margin:4px 0 0 0;font-size:12px;font-weight:400;color:#94a3b8;">
                        Best regards, the MM Book Store team.
                     </p>
                  </td>
               </tr>

            </table>
            <!-- /Card -->

         </td>
      </tr>
   </table>

</body>
</html>
