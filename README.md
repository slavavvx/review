# Review form

This project was created by:
	- PHP v.7.3.6
	- MySql v.5.7.9
	- JavaScript

## TODO
* Make dir `review`

## Requirements
* You need PHP 7.3, MySQL 5 or latter
* Composer: https://getcomposer.org/

## How-to
* Put project into dir `review`
* Rename file `secrets.exapmle.json` to `secrets.json` and add the following info to it:
	- data to connect to database;
	- keys for gooogle recaptcha v3 (you can take them on this site https://www.google.com/recaptcha/about/).
* Rename file `.env.example` to `.env` and add necessary info to it (as in the example)
* Open terminal (command line) and run the command `composer update`
* Open terminal (command line) and run the command `mysql -u username -p < your_path/review/database/review_full.sql`
* In address bar of your browser write `review/`

## Tips
* To install project on local pc with OS Windows
	- you can use web server Apache 2.4
* Create virtual host `review`
	- add this row `127.0.0.1 review` into your *hosts* file (Windows: *C:\Windows\System32\drivers\etc\hosts*)
	- copy and paste next code into *httpd-vhosts.conf* file:
	    
	    ```
		<VirtualHost review:80>
            ServerName review
            DocumentRoot your_path/review
        
            ErrorLog "your_path/review/logs/error.log"
            CustomLog "your_path/review/logs/access.log" common
		</VirtualHost>
		```

* Restart Apache
