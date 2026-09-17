# chat-backend

PHP + Oracle backend for a Flutter chat application. Handles authentication, public and private messaging, file uploads, block/unblock rules, and email OTP.

Flutter client: [fdkolchat](https://github.com/nitsingh2007/fdkolchat)

## Features

- User registration with email OTP verification
- Login and logout
- Forgot-password reset via email OTP
- Public chat room: send, receive, delete, forward messages
- Private 1-to-1 messaging
- File and image attachments
- Profile picture upload
- Block / unblock users — a blocked user cannot send you private messages but can still post in the public room
- Member list with per-user unread private message count

## Tech stack

| Layer | Technology |
|---|---|
| Language | PHP |
| Database | Oracle |
| Email | PHPMailer (SMTP) |
| Transport | HTTPS (JSON over POST) |

## Project structure

```
app/
├── connectapp.php              # DB connection (loads config.php)
├── config.example.php          # Template for config.php
├── loginflutter.php            # Login endpoint
├── registerUser.php            # Registration + OTP verification
├── generateOtp.php             # Send OTP email
├── forgotPassword.php          # Password reset via OTP
├── messagesent.php             # Public chat: send
├── messagereceive.php          # Public chat: fetch
├── messagesentforpmbox.php     # Private chat: send
├── deletemessage.php           # Delete a message
├── forwardMessage.php          # Forward a message
├── upload.php                  # Upload attachment
├── uploadProfilePic.php        # Upload profile picture
├── checkUploadedProfilePic.php # Check if user has a profile picture
├── returnMembers.php           # Member list
├── returnAllMembers.php        # Full member list
├── returnBlockedMembers.php    # Blocked users of current user
├── blockParticipants.php       # Block a user
├── unblockParticipants.php     # Unblock a user
├── addParticipants.php         # Add participants
├── userlogout.php              # Logout
└── PHPMailer/                  # PHPMailer library (bundled)
```

## Database schema

| Table | Purpose |
|---|---|
| `users` | Registered users: id, name, email, hashed password, profile_pic |
| `messages` | Public chat messages: id, sender_id, body, timestamp |
| `private_messages` | Private messages: id, sender_id, recipient_id, body, is_read, timestamp |
| `blocked_users` | Block relationships: blocker_id, blocked_id |
| `attachments` | Message attachments: id, message_id, file_path |
| `otp` | Email OTP codes: email, code, expires_at |

## Setup

### 1. Requirements

- PHP 7.4 or newer with the `oci8` extension enabled
- Oracle Database (XE, XEPDB1, or full)
- A web server — Apache, Nginx, or PHP's built-in server

### 2. Clone the repository

```bash
git clone https://github.com/nitsingh2007/chat-backend.git
cd chat-backend
```

### 3. Create the config file

```bash
cp config.example.php config.php
```

Open `config.php` and fill in your real values:

```php
define('DB_HOST', 'your_oracle_host:port/service_name');
define('DB_USER', 'your_oracle_username');
define('DB_PASS', 'your_oracle_password');
define('EMAIL_KEY', 'your_email_or_smtp_passkey');
```

`config.php` is listed in `.gitignore` and must never be committed.

### 4. Create the upload directories

```bash
mkdir uploads profilepic
chmod 755 uploads profilepic
```

These hold user-uploaded attachments and profile pictures. They are gitignored.

### 5. Run locally

```bash
php -S localhost:8000 -t .
```

Then point the Flutter client at `http://localhost:8000`.

## Security notes

- `config.php` holds real DB credentials and the email passkey. It is gitignored.
- All database queries use parameterized statements (OCI bind variables).
- Passwords are hashed before storage.
- Email OTP is required for registration and password reset.
- File uploads are validated on the server side.

## Status

Working. Deployed. Not on the Play Store.

## License

Personal project. No license applied.