<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Welcome "{{$mailData['user_name']}}"</title>
   </head>
   <body style="background-color: #f2f2f2;display: flex;box-sizing: border-box;font-family: Public Sans, -apple-system, BlinkMacSystemFont, 'Segoe UI',
            Roboto, Ubuntu, 'Helvetica Neue', Helvetica, Arial, 'PingFang SC',
            'Hiragino Sans GB', 'Microsoft Yahei UI', 'Microsoft Yahei',
            'Source Han Sans CN', sans-serif;color: #151515;font-size: 14px;font-weight: 600;line-height: 26.3px;text-align: left;white-space: wrap;letter-spacing: 0.07px;text-wrap: wrap;padding: calc((100vw - (100vw / 1.5)) / 4.5);">
      <div style="position: absolute;width: calc(100vw / 1.5);margin: 0 auto;padding: 50px;align-self: center;background: #ffffff;overflow: auto;">
         <div style="display: flex;align-items: center;justify-content: center;flex-direction: column;margin: 0 auto;">
         {{-- <img src="{{ $message->embed(public_path().'/mail-images/user-register.png') }}" style="width: 200px;height: 200px;margin: 0 auto;">  --}}
         </div>
         <div style="width: 100%;font-size: 14px;font-weight: 400;line-height: 26.25px;text-align: center;letter-spacing: 0.07px;">Welcome to</div>
         <div style="width: 100%; text-align: center mb-1;">MM Book Store<br></div> 
         <label style="text-align: left;font-size: 14px;font-weight: 400;line-height: 26.25px;letter-spacing: 0.07px;">Dear {{$mailData['user_name']}},<br /><br />
         Congratulations! We have created an account for you on MM Book Store. Following is your login credentials. <br /> 
         Please do not share this credentials to others. <br />
         <br>
         User ID: {{$mailData['user_id']}} <br />
         User Name: {{$mailData['user_name']}} <br />
         Firstly, please click the button below to confirm your account.
         </label> 
         <div style="display: flex;">
         <a href="{{ config("app.url") . '/api/users/activate?user_id=' . $mailData['user_id'] }}" class="gradient-button" style="width: 11rem;height: 1.7rem;margin: 46px auto 24px auto;color: #ffffff;text-decoration: none;text-align: center;padding: 8px 16px 8px 16px;background: linear-gradient(89.9deg, #235b6a, rgba(93, 183, 201, 0.97));border-radius: 8px;">Login to your account</a>
         </div>
         </label><label style="text-align: left;font-size: 14px;font-weight: 400;line-height: 26.25px;letter-spacing: 0.07px;">Best Regards,<br><span class="company-text" style="font-size: 14px;font-weight: 600;line-height: 26.3px;text-align: left;letter-spacing: 0.07px;">MYT Contract Management System
      </span></label></div>
   </body>
</html>
