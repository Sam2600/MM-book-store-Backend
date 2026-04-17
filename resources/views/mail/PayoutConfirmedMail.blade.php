<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Payment Received – Akkhayer</title>
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
                     <p style="margin:0;font-size:11px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:#94a3b8;">Akkhayer</p>
                     <h1 style="margin:8px 0 0 0;font-size:26px;font-weight:900;letter-spacing:1px;color:#ffffff;">Payment Received</h1>
                  </td>
               </tr>

               <!-- Body -->
               <tr>
                  <td style="padding:40px 40px 32px 40px;">

                     <p style="margin:0 0 20px 0;font-size:16px;font-weight:700;color:#0f172a;">
                        Hi, {{ $payout->author->name }}!
                     </p>

                     <p style="margin:0 0 24px 0;font-size:14px;font-weight:400;line-height:1.75;color:#475569;">
                        Your payment for <strong style="color:#0f172a;">{{ $payout->period }}</strong> has been successfully transferred to your account. Here are the details:
                     </p>

                     <!-- Payment Details Box -->
                     <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
                        <tr>
                           <td style="padding:20px 24px;">

                              <!-- Amount -->
                              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:16px;">
                                 <tr>
                                    <td style="font-size:13px;color:#64748b;">Amount</td>
                                    <td align="right" style="font-size:20px;font-weight:800;color:#16a34a;">
                                       {{ number_format($payout->total_amount, 0) }} MMK
                                    </td>
                                 </tr>
                              </table>

                              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #e2e8f0;padding-top:16px;">

                                 <!-- Period -->
                                 <tr>
                                    <td style="padding:4px 0;font-size:13px;color:#64748b;width:40%;">Period</td>
                                    <td style="padding:4px 0;font-size:13px;font-weight:600;color:#0f172a;">{{ $payout->period }}</td>
                                 </tr>

                                 <!-- Payment Method -->
                                 <tr>
                                    <td style="padding:4px 0;font-size:13px;color:#64748b;">Sent via</td>
                                    <td style="padding:4px 0;font-size:13px;font-weight:600;color:#0f172a;">
                                       {{ strtoupper($payout->payment_method) }}
                                    </td>
                                 </tr>

                                 <!-- Account -->
                                 <tr>
                                    <td style="padding:4px 0;font-size:13px;color:#64748b;">To account</td>
                                    <td style="padding:4px 0;font-size:13px;font-weight:600;color:#0f172a;">
                                       {{ $payout->payment_account }}
                                    </td>
                                 </tr>

                                 @if($payout->reference_number)
                                 <!-- Reference -->
                                 <tr>
                                    <td style="padding:4px 0;font-size:13px;color:#64748b;">Reference</td>
                                    <td style="padding:4px 0;font-size:13px;font-weight:600;color:#0f172a;font-family:monospace;">
                                       {{ $payout->reference_number }}
                                    </td>
                                 </tr>
                                 @endif

                                 <!-- Paid At -->
                                 <tr>
                                    <td style="padding:4px 0;font-size:13px;color:#64748b;">Confirmed at</td>
                                    <td style="padding:4px 0;font-size:13px;font-weight:600;color:#0f172a;">
                                       {{ $payout->paid_at?->format('d M Y, H:i') }}
                                    </td>
                                 </tr>

                              </table>
                           </td>
                        </tr>
                     </table>

                     @if($payout->note)
                     <!-- Admin Note -->
                     <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
                        <tr>
                           <td style="background-color:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:14px 18px;">
                              <p style="margin:0 0 4px 0;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#92400e;">Note from admin</p>
                              <p style="margin:0;font-size:13px;line-height:1.6;color:#78350f;">{{ $payout->note }}</p>
                           </td>
                        </tr>
                     </table>
                     @endif

                     <!-- Divider -->
                     <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
                        <tr>
                           <td style="border-top:1px solid #e2e8f0;"></td>
                        </tr>
                     </table>

                     <p style="margin:0;font-size:13px;font-weight:400;line-height:1.6;color:#94a3b8;">
                        If you have any questions about this payment, please contact the Akkhayer team. Keep creating great content!
                     </p>

                  </td>
               </tr>

               <!-- Footer -->
               <tr>
                  <td style="background-color:#f8fafc;border-top:1px solid #e2e8f0;padding:24px 40px;">
                     <p style="margin:0;font-size:13px;font-weight:600;color:#0f172a;">Akkhayer</p>
                     <p style="margin:4px 0 0 0;font-size:12px;font-weight:400;color:#94a3b8;">
                        Thank you for being an author on our platform.
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
