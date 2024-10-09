<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## COVID Vaccine Registration

**COVID Vaccine Registration** is a web application where users can register for a single-dose COVID vaccination, select a vaccine center, and receive notifications about their scheduled vaccination date. The system distributes vaccine appointments on a first-come, first-served basis and provides a status search feature based on the user's National ID (NID).

### Features:
- User registration for vaccination with center selection.
- Vaccine centers have a daily limit on how many users they can serve.
- Vaccination appointments are scheduled based on availability and distributed on a first-come, first-served basis.
- Email notifications are sent to users at 9 PM the day before their scheduled appointment.
- Search page to check vaccination status by NID, with statuses like "Not Registered," "Not Scheduled," "Scheduled," and "Vaccinated."

## Project Setup

Follow these steps to set up and run the project locally.

### Step 1: Clone the Repository

```bash
git clone https://github.com/iktakhairul/COVID-vaccination.git
cd COVID-vaccination
```
### Step 2: Install Dependencies
```
composer install
```
### Step 3: Set Up Environment Variables
```
Copy the .env.example file to .env:
cp .env.example .env

Generate the application key:
php artisan key:generate
```
### Step 4: Configure the Database
```
Update the .env file with your database details:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```
### Step 5: Run Migrations and Seeders
```
Create database and run the following commands to set up the database tables and pre-populate the vaccine_centers table with sample data:
php artisan migrate
php artisan db:seed --class=VaccineCentersSeeder

or

php artisan migrate:fresh --seed
```
### Step 6: Serve the Application
```
To start the application, use Laravel’s built-in server:
php artisan serve
```
### Step 7: Scheduling Tasks (If you need to send email)
```
To ensure users receive email notifications the night before their vaccination, set up Laravel's task scheduling. In your server’s cron configuration, add:
* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1

or run
php artisan schedule:work
```

### Step 8: Set Up Mail Configuration
```
Make sure that your mail configuration is set up in the .env file. Here’s an example configuration using SMTP:
MAIL_DRIVER=smtp
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```
### Step 9: Use Queue for Sending Emails
```
Ensure your application is set up to use queues. If you haven’t already, configure your queue settings in the .env file:MAIL_MAILER=smtp
QUEUE_CONNECTION=database
```
### Available Features
- Registration Page - Users can register for the vaccine.
- Search Page - Users can search for their vaccination status using their NID. 
- Email Notification - Users will receive an email the night before their scheduled vaccination date.

### License
This `README.md` file now provides detailed setup instructions, including environment configuration, migration and seeding commands, project overview, and license information specific to your project "COVID Vaccine Registration."

### Performance Optimization 🏎️
To enhance the efficiency of user registration and improve search performance:

- **Database Indexing**: Ensure that columns frequently searched or filtered, such as `user_nid`, are indexed for faster lookups.
- **Eager Loading**: Use eager loading when retrieving related models to reduce the number of queries executed, especially when displaying vaccine centers and their registrations.
- **Caching**: Implement caching strategies for frequently accessed data, such as vaccine center information.
- **Batch Processing**: If scheduling users, consider batch processing registrations to reduce database load.

These optimizations will ensure a faster and more responsive application for users during peak registration periods.

### Future Enhancements 📈

To implement SMS notifications alongside email notifications for vaccination scheduling:

- **SMS Gateway Integration**: Integrate with an SMS gateway service (e.g., Twilio, Nexmo) to send SMS messages.
- **Notification Logic**: Update the notification logic in the `RegisterController` to include sending an SMS after the email notification is sent.
- **Error Handling**: Implement error handling to manage failed SMS deliveries and potentially log these for review.

These changes would ensure users receive timely notifications via SMS, enhancing their experience with the vaccination registration system.

### Adding SMS Functionality to Email Notifications
```
Choose an SMS service provider (e.g., Twilio, Nexmo).
Add the service provider's SDK to your project (e.g., via Composer):

composer require twilio/sdk

Configure your SMS service in the .env file:
SMS_SERVICE_PROVIDER=twilio
TWILIO_ACCOUNT_SID=<your-twilio-account-sid>
TWILIO_AUTH_TOKEN=<your-twilio-auth-token>
TWILIO_PHONE_NUMBER=<your-twilio-phone-number>
```
Modify command to include the logic for sending SMS notifications and integrate along with smp service provider documentation.
