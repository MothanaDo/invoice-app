##  🧾 Invoice Generation App
This project is to create an invoicing API. The API needs to have endpoints to
1.	Create a new invoice for a customer and persist the invoice data.
2.	Show the details for one invoice. Each Customer pays by number and quality of Users. You need to determine the number and the quality of Users for each invoice and make sure Users are not counted twice across invoice periods.



## 🚀 Features
1.	POST to /invoices with the parameters START (string, date), END (string, date) and CUSTOMER_ID. The endpoint should calculate the invoice events, amount of events and total price. It will return the invoice_id.
2.	GET to /invoices/:id will return the details of the invoice. This includes:
-	A list of invoiced events
-	The frequency of how often an event occurred in a period
-	The price for each event
-	The total price that the customer needs to pay
-	An additional list of users (ids or email addresses), what was invoiced for each user and why so that the customer can double-check if the invoice is correct.



## 🛠️ Tech Stack
- PHP Laravel.
- MySQL database.


## 💾 Database Diagram 
```
https://github.com/MothanaDo/invoice-app/blob/master/Database_Diagram.pdf
```


https://github.com/MothanaDo/invoice-app/blob/master/Database_Diagram.pdf

##  💻 Setup and Installation
1- Clone the repository:

```
git clone https://github.com/MothanaDo/invoice-app.git
```

2- Install dependencies:

```
composer install
```

3- Start the development server:

```
php artisan serv

```

The app will be available at  **`http://localhost:5173`** .


