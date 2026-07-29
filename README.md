# Digital Payment System & Security
## WEB TECHNOLOGIES PROJECT
A role-based **digital payment and wallet management web application** developed as a Web Technologies project. The system provides separate interfaces and permissions for **Customers, Agents, and Administrators** and supports wallet transactions, bill payments, loan requests, account management, and administrative controls.

> **Important:** This is an academic/demo project. The current implementation stores account passwords as plain text and is **not suitable for production use** without the security improvements listed below.

---

## Table of Contents

- [Project Overview](#project-overview)
- [Repository Description](#repository-description)
- [Main Features](#main-features)
- [User Roles](#user-roles)
- [Technology Stack](#technology-stack)
- [System Architecture](#system-architecture)
- [Project Structure](#project-structure)
- [Database Design](#database-design)
- [Transaction Rules](#transaction-rules)
- [API Endpoints](#api-endpoints)
- [Installation and Setup](#installation-and-setup)
- [Demo Accounts](#demo-accounts)
- [How to Use](#how-to-use)
- [Security Features](#security-features)
- [Current Limitations](#current-limitations)
- [Future Improvements](#future-improvements)
- [Suggested GitHub Topics](#suggested-github-topics)
- [License](#license)

---

## Project Overview

The **Digital Payment System & Security** project simulates a digital wallet platform where users can register and perform common financial operations through a web interface.

The application uses a lightweight, MVC-inspired PHP structure:

- `index.php` works as the main router.
- Controllers manage page requests and user actions.
- Models provide database configuration, authentication helpers, and PDO connectivity.
- Views contain the user interfaces.
- API scripts process customer transactions and return JSON responses.
- MySQL stores users, balances, transactions, bill providers, loan requests, and password-reset records.

---

## Repository Description

Use the following text in the GitHub repository **About → Description** field:

> Role-based digital payment and wallet management system with customer, agent, and admin modules, money transfer, cash in/out, bill payment, loan processing, and account security features using PHP and MySQL.

---

## Main Features

### Authentication and Account Management

- Role-based login for Customer, Agent, and Admin.
- Login using either **User ID** or **email address**.
- Customer and Agent account registration.
- New Customer accounts are approved automatically.
- New Agent accounts remain pending until Admin approval.
- Account-status validation during login.
- Logout and session management.
- Forgot-password flow with a locally generated reset link.
- Reset tokens are hashed and expire after 30 minutes.
- Change-password functionality.
- Customer profile update.
- Customer profile-image upload.

### Digital Wallet Operations

- View current wallet balance.
- Send money from one Customer to another Customer.
- Cash out through a registered Agent.
- Calculate cash-out fees automatically.
- Pay bills through configured bill providers.
- Submit a loan request.
- Store completed operations in the transaction table.
- Use database transactions and row locking during balance updates.

### Administration

- View the Admin dashboard.
- Change a user's role.
- Change account status to Pending, Approved, or Rejected.
- Prevent an Admin from removing their own Admin role.
- View all loan requests.
- Approve or reject pending loans.
- Credit an approved loan directly to the Customer wallet.
- Record approved loans as transactions.
- Add, edit, and remove Terms and Conditions.
- Retrieve loan-request data through an Admin JSON endpoint.

---

## User Roles

### Customer

A Customer can:

- Create an account.
- Log in after registration.
- View their wallet balance.
- Send money to another Customer by phone number.
- Cash out through an Agent.
- Pay a bill.
- Apply for a loan.
- Update name, email, phone number, and profile image.
- Change or reset their password.
- View Terms and Conditions.

### Agent

An Agent can:

- Register for an Agent account.
- Log in after Admin approval.
- View the Agent dashboard.
- Perform a Cash In operation for a Customer.
- Review the Cash Out Request interface.
- Access the User Verification interface.

> The Agent Cash In module is database-backed. The current Cash Out Request page uses demo session data, and the User Verification page is currently a UI prototype.

### Administrator

An Administrator can:

- Log in to the Admin dashboard.
- View registered users.
- Update user roles.
- Approve, reject, or mark accounts as pending.
- Review loan requests.
- Approve or reject loans.
- Credit approved loan amounts to Customer balances.
- Manage the system Terms and Conditions.

---

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | PHP |
| Database | MySQL / MariaDB |
| Database Access | PDO with prepared statements |
| Frontend | HTML5, CSS3, JavaScript |
| Asynchronous Operations | Fetch API / AJAX and JSON |
| Authentication | PHP sessions |
| Local Server | XAMPP, WAMP, or a compatible PHP server |
| Architecture | MVC-inspired structure with a front controller |

No PHP framework, Composer package, or Node.js dependency is required.

---

## System Architecture

```text
Browser
   |
   v
index.php router
   |
   +-------------------+--------------------+
   |                   |                    |
Controllers         API Scripts          Views
   |                   |                    |
   +-------------------+--------------------+
                       |
                       v
                 Models / PDO
                       |
                       v
                MySQL Database
```

### Routing

The main router receives a URL parameter such as:

```text
index.php?url=Auth/login
index.php?url=Customer/dashboard
index.php?url=Agent/dashboard
index.php?url=Admin/dashboard
```

It then calls the appropriate controller function and loads the required view.

---

## Project Structure

```text
Digital-Payment-System-and-Security/
│
├── index.php
├── database.sql
├── README.md
│
├── controllers/
│   ├── AuthController.php
│   ├── CustomerController.php
│   ├── AgentController.php
│   ├── AdminController.php
│   ├── SecurityController.php
│   └── api/
│       ├── get_balance.php
│       ├── check_phone.php
│       ├── send_money.php
│       ├── cashout_fee.php
│       ├── cash_out.php
│       ├── pay_bill.php
│       ├── loan_request.php
│       └── admin/
│           └── get_loan_requests.php
│
├── models/
│   ├── bootstrap.php
│   ├── config.php
│   ├── db.php
│   ├── db_legacy_proj2.php
│   ├── demoData.php
│   ├── terms.json
│   └── helpers/
│       └── auth.php
│
├── public/
│   └── uploads/
│       └── profile/
│
└── views/
    ├── _guard.php
    ├── admin/
    ├── agent/
    ├── auth/
    ├── customer/
    ├── security/
    └── assets/
        ├── css/
        ├── icons/
        └── js/
```

---

## Database Design

The included `database.sql` script creates a database named:

```sql
dps
```

It creates the following main tables.

### `users`

Stores all accounts that can log in.

Important fields:

- `id`
- `user_id`
- `name`
- `phone`
- `email`
- `dob`
- `gender`
- `role`
- `status`
- `password`
- `profile_image`
- `balance`
- `created_at`
- `updated_at`

Supported roles:

```text
admin
agent
customer
```

Supported account statuses:

```text
pending
approved
rejected
```

### `bill_providers`

Stores organizations that can receive bill payments.

Default providers:

- Electricity Co
- Water Authority
- Internet Provider

### `transactions`

Stores wallet and payment operations.

Supported transaction types used by the project include:

- `send_money`
- `cash_in`
- `cash_out`
- `pay_bill`
- `loan`

The table stores the amount, fee, sender, receiver, bill-provider information, reference, and creation time.

### `loan_requests`

Stores:

- Customer ID
- Requested amount
- Duration in months
- Pending, approved, or rejected status
- Creation and update times

### `password_resets`

Stores:

- User ID
- Hashed reset token
- Expiration time
- Used time
- Creation time

---

## Transaction Rules

### Send Money

- Sender and receiver must both be Customer accounts.
- A Customer cannot send money to their own account.
- The receiver is identified by a valid Bangladesh phone number.
- The sender must confirm the operation using their password.
- The sender must have sufficient balance.
- The amount is deducted from the sender and credited to the receiver.
- The transaction is recorded in the database.
- Current fee: `0 BDT`.

### Cash Out

- Cash Out is performed from a Customer wallet to an Agent account.
- The Agent is identified by a valid Bangladesh phone number.
- The Customer must confirm the operation using their password.
- Cash-out fee calculation:

```text
10 BDT for every 1,000 BDT or part of 1,000 BDT
```

Examples:

| Cash-out Amount | Fee | Total Deduction |
|---:|---:|---:|
| 500 BDT | 10 BDT | 510 BDT |
| 1,000 BDT | 10 BDT | 1,010 BDT |
| 1,001 BDT | 20 BDT | 1,021 BDT |
| 2,000 BDT | 20 BDT | 2,020 BDT |

- The Customer balance is reduced by the amount plus fee.
- The Agent receives the requested amount.
- The transaction is recorded in the database.

### Cash In

- An Agent enters a Customer User ID and amount.
- The Agent must have enough balance for the amount plus fee.
- Current Cash In fee: `10 BDT`.
- The amount is credited to the Customer.
- The amount plus fee is deducted from the Agent.
- The transaction is recorded in the database.

### Pay Bill

- The Customer selects a provider from the database.
- A bill/account number and amount are entered.
- The Customer confirms the payment using their password.
- The amount is deducted from the Customer wallet.
- Provider information and the bill reference are stored in the transaction record.
- Current fee: `0 BDT`.

### Loan Request

- Minimum loan amount: `1 BDT`.
- Maximum loan amount: `2,000 BDT`.
- Duration must be a positive number of months.
- The Customer confirms the request using their password.
- New requests are stored with `pending` status.
- An Admin can approve or reject the request.
- When approved, the requested amount is credited to the Customer balance.
- The approved loan is recorded as a transaction.

---

## API Endpoints

These scripts require an active session and the correct role.

### Customer APIs

| Endpoint | Purpose |
|---|---|
| `controllers/api/get_balance.php` | Return the current Customer balance |
| `controllers/api/check_phone.php` | Check whether a phone number belongs to a user |
| `controllers/api/send_money.php` | Transfer money to another Customer |
| `controllers/api/cashout_fee.php` | Calculate Cash Out fee and total |
| `controllers/api/cash_out.php` | Complete a Customer Cash Out |
| `controllers/api/pay_bill.php` | Pay a configured bill provider |
| `controllers/api/loan_request.php` | Submit a loan request |

### Admin API

| Endpoint | Purpose |
|---|---|
| `controllers/api/admin/get_loan_requests.php` | Return recent loan requests as JSON |

The API accepts standard form input, and several endpoints also support JSON request bodies.

---

## Installation and Setup

### Requirements

Install one of the following:

- XAMPP
- WAMP
- MAMP
- PHP with MySQL/MariaDB and PDO MySQL enabled

Recommended components:

- PHP 7.4 or later
- MySQL or MariaDB
- Apache
- phpMyAdmin

### Step 1: Download or Clone

Using Git:

```bash
git clone YOUR_REPOSITORY_URL
```

Or download the project ZIP and extract it.

### Step 2: Move the Project

For XAMPP, place the project folder inside:

```text
C:\xampp\htdocs\
```

Example:

```text
C:\xampp\htdocs\Digital-Payment-System-and-Security\
```

### Step 3: Start the Server

Open the XAMPP Control Panel and start:

- Apache
- MySQL

### Step 4: Import the Database

1. Open phpMyAdmin:

```text
http://localhost/phpmyadmin/
```

2. Select **Import**.
3. Choose `database.sql`.
4. Click **Go**.

> The SQL script drops any existing `dps` database and creates a new one. Back up an existing database before importing.

### Step 5: Configure Database Access

Default settings in `models/config.php`:

```php
DB_HOST = localhost
DB_NAME = dps
DB_USER = root
DB_PASS = empty
```

Update these values if your MySQL configuration is different.

Environment variables are also supported:

```text
DB_HOST
DB_NAME
DB_USER
DB_PASS
```

### Step 6: Open the Application

When the folder is named `Digital-Payment-System-and-Security`, open:

```text
http://localhost/Digital-Payment-System-and-Security/
```

When using the original downloaded folder name, open:

```text
http://localhost/Digital-Payment-System-and-Security-main/
```

The default route opens the login page.

---

## Demo Accounts

The database script creates the following accounts.

| Role | User ID | Email | Password | Initial Balance |
|---|---|---|---|---:|
| Admin | `admin` | `admin@dps.com` | `123456` | 0 BDT |
| Agent | `agent1` | `agent@dps.com` | `123456` | 1,000 BDT |
| Customer | `cust1` | `customer@dps.com` | `123456` | 4,200 BDT |

During login:

1. Select the correct role.
2. Enter either the User ID or email.
3. Enter the password.

Example Customer login:

```text
Role: Customer
User ID: cust1
Password: 123456
```

---

## How to Use

### Customer Workflow

1. Select **Customer** on the login page.
2. Log in using the demo account or create a new Customer account.
3. Open the dashboard.
4. Select Send Money, Cash Out, Pay Bill, Loan, or Profile.
5. Enter valid information.
6. Confirm financial actions using the account password.
7. Return to the dashboard to view the updated balance.

### Agent Workflow

1. Select **Agent** on the login page.
2. Log in using an approved Agent account.
3. Use **Cash In** to credit a Customer account.
4. Enter the Customer User ID and amount.
5. The system deducts the amount and fixed fee from the Agent wallet.

### Admin Workflow

1. Select **Admin** on the login page.
2. Log in with the Admin demo account.
3. Open **Manage Roles** to update roles and statuses.
4. Open **Loan Status** to approve or reject requests.
5. Open **Terms & Conditions** to update system policies.

---

## Security Features

The current project includes the following security and data-integrity measures:

- Role-based route protection.
- Session-based login state.
- Account-status checks.
- PDO prepared statements.
- Server-side input validation.
- Bangladesh phone-number validation.
- Database transactions for balance-changing operations.
- `SELECT ... FOR UPDATE` row locking for important wallet operations.
- Balance checks before deductions.
- Password confirmation before Customer financial actions.
- Hashed password-reset tokens.
- Expiring and single-use reset records.
- Restricted profile-image type and size.
- Unique database constraints for User ID, phone, and email.
- Generic JSON server-error responses.

---

## Current Limitations

The following limitations are present in the submitted project:

1. **Passwords are stored as plain text.**
2. Some comments mention bcrypt, but the current code compares plain-text passwords.
3. CSRF tokens are not implemented for forms or transaction requests.
4. The Forgot Password page displays the reset link locally instead of sending an email or SMS.
5. The Agent Cash Out Request module uses demo data stored in the session.
6. The Agent User Verification page is a UI prototype without database search or approval logic.
7. Error display is enabled in `models/config.php`, which can reveal technical details during development.
8. No transaction-history page is provided for Customers.
9. No two-factor authentication or OTP confirmation is implemented.
10. No rate limiting, account-lockout policy, or login-attempt monitoring is implemented.
11. No automated test suite is included.
12. The application is designed for local XAMPP/WAMP use and has not been configured for a production deployment.

---

## Future Improvements

Recommended improvements:

- Replace plain-text passwords with `password_hash()` and `password_verify()`.
- Add CSRF protection to every state-changing request.
- Implement OTP or two-factor authentication.
- Send password-reset links through verified email or SMS.
- Add transaction-history and receipt pages.
- Add pagination and filtering for Admin tables.
- Connect Agent Cash Out requests to the transaction database.
- Complete the Agent User Verification/KYC module.
- Add user-address, NID, profession, and verification tables.
- Add login-attempt limits and account lockout.
- Add an audit-log table.
- Add notification support.
- Add stronger authorization checks inside every view.
- Disable detailed PHP errors in production.
- Add input normalization and stricter amount precision handling.
- Add automated unit, integration, and security tests.
- Add responsive screenshots and a project demonstration video.
- Configure HTTPS and secure session-cookie settings for deployment.

---

## Suggested GitHub Topics

Add these topics from the repository **About** section:

```text
php
mysql
digital-payment
payment-system
digital-wallet
fintech
web-application
pdo
role-based-access-control
xampp
html
css
javascript
ajax
academic-project
```

---

## License

No license file is currently included in this project. Add an appropriate license before allowing public reuse or redistribution.

---

## Academic Notice

This repository was developed for educational purposes as a **Web Technologies Project**. It demonstrates role-based application design, PHP sessions, PDO database operations, wallet balance updates, transaction processing, and administrative management.

It should not be used to process real financial information or real money without a complete security review and substantial production hardening.
