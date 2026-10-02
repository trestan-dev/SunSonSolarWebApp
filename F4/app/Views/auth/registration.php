<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/sss-auth.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script type="module" src="<?= base_url('assets/js/sss-auth.js') ?>"></script>
</head>
<body>
    <div class="auth-container">
        <div class="auth-form registration-form">
            <img class="site-logo" src="<?= base_url('assets/logo-sss.png') ?>" alt="SunSonSolar logo">
            <h1>Welcome to SunSonSolar</h1>
            <p>Please fill out the form below to create an account.</p>
            <form id="registration-form" data-endpoint="<?= site_url('register') ?>">
                <div class="form-fields">
                    <input type="text" placeholder="First Name" name="first_name" required>
                    <input type="text" placeholder="Last Name" name="last_name" required>
                    <input type="text" placeholder="Middle Name" name="middle_name" required>
                    <label for="birthdate" style="font-weight: bold; margin: 0 0 6px 0;">Birthdate</label>
                    <input type="date" name="birthdate" id="birthdate" required>
                    <div class="gender-group">
                        <label id="gender-label">Gender</label>
                        <div class="gender-options">
                            <label class="gender-option" for="male">
                                <input type="radio" id="male" name="gender" value="male" required>
                                <span>Male</span>
                            </label>
                            <label class="gender-option" for="female">
                                <input type="radio" id="female" name="gender" value="female" required>
                                <span>Female</span>
                            </label>
                        </div>
                    </div>
                    <input type="email" placeholder="Email" name="email" required>
                    <input type="tel" placeholder="Phone Number" name="phone_number" autocomplete="tel" required>
                    <input type="text" placeholder="Address" name="address" required>
                    <input type="text" placeholder="Username" name="username" required>
                    <div class="password-field">
                        <input type="password" placeholder="Password" name="password" required>
                        <button class="password-toggle" type="button" aria-label="Show password" aria-pressed="false"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
                    </div>
                </div>
                <button type="submit">Register</button>
                <p class="form-message" role="status" aria-live="polite"></p>
                <p>Already have an account? <a href="<?= site_url('login') ?>">Login</a></p>
            </form>
        </div>
    </div>
</body>

</html>
