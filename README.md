# MarkX - Quote and Invoice Management System

A robust Laravel-based application for managing quotes and invoices, designed for marketers and managers to streamline their business operations.

## Features

- **Quote Management**
  - Create, edit, and delete quotes
  - Add multiple items to quotes with quantities and prices
  - Quote approval workflow
  - Convert approved quotes to invoices
  - PDF generation for quotes

- **Invoice Management**
  - Automatic invoice generation from approved quotes
  - Invoice status tracking (draft, final, paid, overdue, cancelled)
  - PDF generation for invoices
  - Due date management
  - Payment tracking

- **User Roles**
  - Manager: Manages marketers and view all quotes/invoices
  - Marketer: Can create quotes and manage their own quotes/invoices

- **Product Items Catalog**
  - Reusable item database
  - Price history tracking
  - Usage statistics

## Requirements

- PHP >= 8.1
- Laravel 10.x
- Composer
- Node.js & NPM
- MySQL/PostgreSQL

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd MarkX
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install JavaScript dependencies:
```bash
npm install
```

4. Create environment file:
```bash
cp .env.example .env
```

5. Configure your database in `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Generate application key:
```bash
php artisan key:generate
```

7. Run database migrations:
```bash
php artisan migrate
```

8. Build assets:
```bash
npm run dev
```

9. Create storage link:
```bash
php artisan storage:link
```

## Usage

### Quote Management

1. **Creating a Quote**
   - Navigate to Quotes section
   - Click "Create New Quote"
   - Fill in quote details and add items
   - Submit for approval

2. **Quote Workflow**
   - Pending: Initial state when created
   - Approved: Manager has approved the quote
   - Rejected: Manager has rejected the quote
   - Converted: Quote has been converted to an invoice

### Invoice Management

1. **Converting Quotes to Invoices**
   - Open an approved quote
   - Click "Convert to Invoice"
   - Invoice will be generated with approved items

2. **Invoice States**
   - Draft: Initial state
   - Final: Invoice sent to client
   - Paid: Payment received
   - Overdue: Past due date
   - Cancelled: Invoice cancelled

## User Flows

### Marketer Role

1. **Quote Creation and Management**
   - Log in as a marketer
   - Navigate to Quotes dashboard
   - Create new quotes with detailed items and pricing
   - View and edit pending quotes
   - Track quote approval status
   - Download quote PDFs

2. **Invoice Management**
   - Convert approved quotes to invoices
   - Track invoice status (draft, final, paid, overdue)
   - Send invoices to clients
   - Mark invoices as paid
   - Download invoice PDFs

3. **Product Catalog**
   - Access product item history
   - View commonly used items and pricing
   - Track success rates of items
   - Analyze price trends

4. **Dashboard**
   - View personal performance metrics
   - Track conversion rates
   - Monitor pending approvals
   - View recent activity

### Manager Role

1. **Quote Review and Approval**
   - Log in as a manager
   - Review pending quotes
   - Approve/reject individual items in quotes
   - Provide feedback on rejections
   - Track marketer performance

2. **Invoice Oversight**
   - Monitor all invoices across marketers
   - Track payment status
   - View overdue invoices
   - Generate financial reports

3. **Performance Monitoring**
   - View team-wide metrics
   - Track quote-to-invoice conversion rates
   - Monitor individual marketer performance
   - Analyze product success rates

4. **System Administration**
   - View system-wide reports
   - Monitor user activity
   - Track approval timelines
   - Review sales metrics

## Development

### Project Structure

- `app/Models`: Contains Quote, Invoice, User, and QuoteItem models
- `app/Http/Controllers`: Contains business logic
- `app/Policies`: Authorization policies
- `app/Services`: PDF generation service
- `resources/views`: Blade templates
- `database/migrations`: Database structure
- `routes`: Web and API routes

### Key Files

- `QuoteController.php`: Quote management logic
- `InvoiceController.php`: Invoice processing
- `PdfService.php`: PDF generation for quotes and invoices
- `QuotePolicy.php` & `InvoicePolicy.php`: Authorization rules

## Detailed Role Permissions

### Manager Role Permissions
- **Quotes**
  - View all quotes in the system
  - Cannot create or edit quotes
  - View quote history and analytics

- **Invoices**
  - View all invoices in the system
  - Monitor payment status
  - View invoice history
  - Cannot create or edit invoices directly
  - Access to financial reports

- **Users**
  - View marketer performance
  - Access system-wide analytics
  - View activity logs
  - Generate performance reports

### Marketer Role Permissions
- **Quotes**
  - Create new quotes
  - Edit own pending quotes
  - View own quotes
  - Delete own pending quotes
  - Convert approved quotes to invoices

- **Invoices**
  - Create invoices from approved quotes
  - Edit own draft invoices
  - Mark own invoices as paid
  - Send invoices to clients
  - Download invoice PDFs

- **Products**
  - Add items to quotes
  - View product catalog
  - Access price history
  - View item success rates

## Step-by-Step Guides

### For Marketers

1. **Creating a New Quote**
   ```
   1. Navigate to Quotes → Create New Quote
   2. Fill in basic information:
      - Title
      - Description
      - Valid until date
   3. Add items:
      - Select from product catalog or add new
      - Set quantity and price
      - Add additional items as needed
   4. Review total amount
   5. Submit for approval
   ```

2. **Converting Quote to Invoice**
   ```
   1. Open approved quote
   2. Click "Convert to Invoice"
   3. Review invoice details
   4. Set due date (default 30 days)
   5. Generate invoice
   ```

3. **Managing Invoice Payment**
   ```
   1. Open invoice
   2. Click "Mark as Paid"
   3. Enter payment details
   4. Confirm payment
   ```

### For Managers

1. **Reviewing Quotes**
   ```
   1. Access Quotes dashboard
   2. Filter for pending quotes
   3. Open quote for review
   4. Review individual items
   ```

2. **Monitoring Performance**
   ```
   1. Access Dashboard
   2. View key metrics:
      - Quote approval rate
      - Invoice conversion rate
      - Payment collection rate
   3. View reports as needed
   ```

## Troubleshooting

### Common Issues and Solutions

1. **Quote Creation Issues**
   - **Problem**: Unable to submit quote
     - Solution: Check all required fields are filled
     - Solution: Verify total amount is greater than zero
     - Solution: Ensure valid until date is in the future

   - **Problem**: Items not appearing in catalog
     - Solution: Clear browser cache
     - Solution: Verify item search terms
     - Solution: Check if items are archived

2. **Invoice Generation Issues**
   - **Problem**: Cannot convert quote to invoice
     - Solution: Verify quote is approved
     - Solution: Check if all items are approved
     - Solution: Ensure no duplicate invoice exists

   - **Problem**: PDF generation fails
     - Solution: Check server memory limits
     - Solution: Verify file permissions
     - Solution: Clear PDF cache

3. **Payment Processing Issues**
   - **Problem**: Cannot mark invoice as paid
     - Solution: Verify user permissions
     - Solution: Check invoice status is 'final'
     - Solution: Ensure payment details are complete

4. **System Access Issues**
   - **Problem**: Permission denied errors
     - Solution: Clear browser cache and cookies
     - Solution: Re-login to the system
     - Solution: Contact administrator for role verification

## Best Practices

### For Marketers

1. **Quote Management**
   - Review all items thoroughly before submission
   - Use standardized item descriptions from catalog
   - Include detailed notes for special pricing
   - Keep quotes valid for standard 30-day period
   - Follow up on pending quotes within 48 hours

2. **Invoice Management**
   - Convert approved quotes to invoices promptly
   - Send invoices immediately after generation
   - Follow up on overdue payments weekly
   - Maintain clear communication with clients
   - Document all payment-related communications

3. **Product Catalog Usage**
   - Use existing items when possible
   - Maintain consistent pricing for regular clients
   - Document special pricing arrangements
   - Update item descriptions clearly
   - Track successful item combinations

### For Managers

1. **Quote Review Process**
   - Review quotes within 24 business hours
   - Provide clear feedback for rejections
   - Check pricing against standards
   - Verify proper client information
   - Document approval decisions

2. **Performance Monitoring**
   - Review team metrics weekly
   - Identify training opportunities
   - Monitor pricing consistency
   - Track quote success rates
   - Analyze rejection patterns

3. **System Administration**
   - Regular review of user activities
   - Monitor system performance
   - Track unusual patterns
   - Maintain data integrity
   - Regular backup verification

## Security Guidelines

1. **Account Security**
   - Regular password updates required
   - Automatic logout after 30 minutes
   - No sharing of accounts allowed

2. **Data Protection**
   - Client information is confidential
   - No export of sensitive data
   - Regular security audits
   - Encrypted PDF generation

3. **Access Control**
   - Role-based access strictly enforced
   - Activity logging enabled
   - Regular permission audits
