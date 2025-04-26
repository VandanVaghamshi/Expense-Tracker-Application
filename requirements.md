# Expense Tracker Application

A simple web application that allows users to track their daily expenses with user authentication, expense management, and visualization features.

## Features

- User registration and login (Email and password authentication)
- Add, edit, and delete expenses
- Each expense includes description, amount, category, and date
- Summary of expenses with total and category breakdown
- Day-wise expense tracking
- Dashboard with charts to visualize expenses over time (using Chart.js)
- Export expenses to CSV file

## Technical Stack

- PHP for server-side scripting
- MySQL for database management
- Bootstrap for responsive design
- Chart.js for data visualization

## Setup Instructions

1. Clone the repository
2. Import the database schema from `database/expense_tracker.sql`
3. Configure database connection in `config/database.php`
4. Start your local server (e.g., XAMPP, WAMP)
5. Access the application through your browser at `http://localhost/Mailer/linkedin/`

## Project Structure

```
linkedin/
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
├── config/
│   └── database.php
├── controllers/
│   ├── AuthController.php
│   ├── ExpenseController.php
│   └── DashboardController.php
├── database/
│   └── expense_tracker.sql
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── models/
│   ├── User.php
│   └── Expense.php
├── views/
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   ├── expenses/
│   │   ├── add.php
│   │   ├── edit.php
│   │   └── list.php
│   └── dashboard/
│       └── index.php
├── index.php
└── README.md
```

## Usage

1. Register a new account
2. Log in with your credentials
3. Add new expenses with description, amount, category, and date
4. View your expense summary and breakdown by category
5. Visualize your spending patterns through the dashboard
6. Export your expense data to CSV when needed

## Error Handling

The application implements proper error handling and returns appropriate HTTP status codes for various operations.

## Future Improvements

- Implement budget setting and tracking
- Add recurring expense functionality
- Provide mobile app integration
- Enable multi-currency support