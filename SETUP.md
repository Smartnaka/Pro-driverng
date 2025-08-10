# ProDrivers Setup Guide

## Environment Variables Configuration

Create a `.env` file in the root directory with the following variables:

```bash
# Paystack API Configuration
PAYSTACK_SECRET_KEY=sk_test_your_secret_key_here
PAYSTACK_PUBLIC_KEY=pk_test_your_public_key_here

# Database Configuration
DB_HOST=localhost
DB_NAME=prodrivers
DB_USER=root
DB_PASS=

# Email Configuration (SMTP)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password_here
SMTP_ENCRYPTION=tls

# Security Configuration
# Generate a random string for CSRF protection
CSRF_SECRET=your_random_csrf_secret_here
```

## Security Improvements Implemented

### 1. ✅ Environment Variables
- Moved Paystack keys from hardcoded values to environment variables
- Added support for database and email configuration via environment variables
- `.env` file is already in `.gitignore` for security

### 2. ✅ Database Transactions
- Added transaction support to booking creation process
- Ensures atomicity between payment verification and booking creation
- Automatic rollback on database errors

### 3. ✅ CSRF Protection
- Added CSRF token generation and validation to booking forms
- Prevents cross-site request forgery attacks
- Tokens are validated server-side before processing

### 4. ✅ Input Validation
- Enhanced server-side validation for all booking fields
- Added sanitization using `filter_input()` functions
- Client-side validation with HTML5 attributes
- Proper error handling and user feedback

### 5. ✅ Email Error Handling
- Improved error logging for failed email attempts
- Email failures don't prevent booking completion
- Detailed error messages for debugging
- Better exception handling

### 6. ✅ Session Cleanup
- Booking session data is properly cleaned after successful booking
- Prevents session pollution and security issues

## Setup Steps

1. **Copy the environment variables above to a `.env` file**
2. **Update the values with your actual credentials**
3. **Ensure your database is configured correctly**
4. **Set up SMTP for email functionality**
5. **Test the booking flow to ensure all security measures work**

## Security Notes

- Never commit the `.env` file to version control
- Use strong, unique passwords for database and email
- Regularly rotate API keys
- Monitor error logs for security issues
- Keep all dependencies updated 