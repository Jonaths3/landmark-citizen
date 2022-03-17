Hello {{ucwords(strtolower($email_data['name']))}}
<br><br>
Congratulations! your store <strong>{{$email_data['store_name']}}</strong> has been registered as a vendor on Landmark wallet.
<br><br>
You have also been registered as the first cashier for this store. Pleases see details below.
<br><br>
Store Name: {{$email_data['store_name']}}
<br>
Admin username: {{$email_data['email']}}
<br>
Admin password: {{$email_data['password']}}
<br>
Name: {{$email_data['name']}}
<br>
Cashier Pin: {{$email_data['pin']}}
<br>
<br>
You are advised to change your pin and password for security purposes.
<br><br>
Have a wonderful business at Landmark!
<br>
<br>
Thank You.
<br>
<br>
<br>