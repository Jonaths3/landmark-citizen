Hi {{ucwords(strtolower($email_data['name']))}}
<br><br>
You’re now a Landmark Citizen, Congratulations!
<br><br>
Your unique verification pin is <span style="font-size: 14px"><strong>{{$email_data['pin']}}</strong></span>. You are advised to change this pin on your app for security reasons.
<br><br>
Please click the below link to activate your account!
<br><br>
<a href="https://www.landmarkafrica.com/ldc/public/user/verify?id={{$email_data['verification_code']}}">Click Here!</a>

<br><br>
Thank you!
<br>
<br>
<strong>Landmark Citizen Admin</strong>
<br><br><br>
