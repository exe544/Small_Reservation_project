# Small reservations project for tour companies.
_____
This project was made to introduce my skills working with Laravel.

Simple app that allows users to book excursions and for Tour companies - manage their activities, assign guides, etc.

### Functionality:

The following functionality was implemented in this project:
- [x] Admin user can create new companies;
- [x] Companies owners can send invite mail to other employees with link through which role will be assigned to user after registration (guide or company admin role); 
- [x] Companies owners can manage activities and assign guides to them;
- [x] Guests can view list of actual tours;
- [x] Auth users can book a tour, cancel it, view the activity's guide contacts;
- [x] After booking a tour user gets a confirmation email;
- [x] Assigned to activity guide can download PDF file with list of tourists and tour details.

All features are covered by tests.
_____
### Created using:
* PHP 8.1
* Laravel 10.0
* MySql 8.0.30
* Redis 2.2
____

### Installation:

To run app localy:
1. Use ```git clone``` to clone this repository locally.
2. Copy ```.env.example``` file and paste to your ```.env``` file. 
3. Run ```composer install``` command.
4. Generate a key using command ```php artisan key:generate``` if needed.
5. Set up your ```.env``` file with your MySQL connection and Mailing. (I used MailTrap for example).
6. Run ```php artisan migrate --seed``` command.
9. Run ```php artisan serve```.
10. In new CLI window enter ```npm install``` & ```npm run dev```. 

If you want to fill app with some data:
1. Run ```php artisan db:seed --class=ActivitySeeder```
2. Run command ```php artisan storage:link```.

To run tests:
1. Copy ```.env.example``` file and paste to your ```.env.testing``` file.
2. Run ```php artisan migrate --env=testing --seed``` command.
3. Use command ```php artisan test```.
