# Purchase Requisition System

![Filament Admin Demo](https://filamentphp.com/images/social.png)

A complete purchase requisition management system built with Laravel Filament featuring:
- Department budget tracking
- Multi-PDF quotation attachments
- Approval workflow
- Real-time budget calculations

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+
- Composer
- SQLite/MySQL
- Node.js 16+

```bash
git clone https://github.com/Zack956/Purchase-Requisition-Form.git
cd Purchase-Requisition-Form
composer install
cp .env.example .env
php artisan key:generate

# For SQLite:
touch database/database.sqlite

php artisan migrate --seed
php artisan serve


✨ Key Features
Feature	Description
Budget Tracking	Real-time department budget monitoring
PDF Quotations	Upload and preview multiple vendor quotes
Approval Workflow	Draft → Pending → Approved/Rejected
Audit Trail	Complete history of all changes

📂 Project Structure
Purchase-Requisition-Form/
├── app/
│   ├── Filament/Resources/  # All Filament resources
│   ├── Models/              # Eloquent models
│   └── Services/            # Business logic
├── config/                  # Configuration files
├── database/                # Migrations and seeders
└── resources/               # Views and assets

🌟 Recommended Development Setup
Install Laragon (Windows) or Valet (Mac)

Clone repository

Set up SQLite database:
touch database/database.sqlite
php artisan migrate --seed

Access admin at http://localhost/admin

🤝 Contributing
Pull requests are welcome! For major changes, please open an issue first.

📜 License
MIT

### Step 3: Commit and Push the README
```bash
git add README.md
git commit -m "Add comprehensive README documentation"
git push origin master