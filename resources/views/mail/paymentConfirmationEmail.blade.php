<strong>Payment Confirmation</strong>
<br><br>
Please see below the details of a recent payment transaction on your account:
<br><br>
Amount: ₦{{$email_data['amount']}}.
<br><br>
Sender: {{ucwords($email_data['sender_name'])}}.
<br><br>
Reciever: {{ucwords($email_data['receiver_name'])}}.
<br><br>
Transaction ID: {{$email_data['tranx_id']}}.
<br><br>
Thank you!
<br>
<br>
<br>