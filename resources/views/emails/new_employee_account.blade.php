<!DOCTYPE html>
<html>
<head>
    <title>New Employee Account</title>
</head>
<body>
    <h1>Welcome to the HRIS, {{ $user->name }}!</h1>

    <p>Your account has been created, but it is currently pending approval by the admin.</p>
    <p>Here are your temporary login credentials once you are approved:</p>
    <ul>
        <li>Email: {{ $user->email }}</li>
        <li>Password: {{ $plainPassword }}</li>
    </ul>

    <p>
        Please wait until an administrator approves your account. Once approved, you will be able to 
        log in using the above credentials.
    </p>

    <p>Thank you!</p>
</body>
</html>
