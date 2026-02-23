# Senior Data Management System

A comprehensive PHP-based system for managing senior citizen data with Excel upload, QR code generation, and ID card creation.

## Features

- **User Authentication**: Secure login system
- **Data Management**: Add, view, and manage senior citizen records
- **Excel Upload**: Bulk import data from Excel files
- **Manual Entry**: Add individual records through a modal form
- **Auto Age Calculation**: Age automatically calculated from birthdate
- **QR Code Generation**: QR codes generated for each person
- **ID Card Generation**: Professional ID cards with print functionality
- **Image Upload**: Support for profile pictures
- **Data Validation**: Prevents duplicate entries and validates input

## Requirements

- PHP 7.4 or higher
- MySQL/MariaDB
- XAMPP/WAMP/MAMP (for local development)
- Composer (for PHPExcel dependency)

## Installation

1. **Clone/Download the project** to your web server directory (e.g., `htdocs/Senior`)

2. **Create the database**:
   - Open phpMyAdmin
   - Import the `database.sql` file or run the SQL commands manually

3. **Install dependencies**:
   ```bash
   composer install
   ```

4. **Configure database connection** (if needed):
   - Edit `config.php` to match your database credentials

5. **Set permissions**:
   - Ensure the `uploads/` directory is writable by the web server

6. **Access the system**:
   - Open your browser and navigate to `http://localhost/Senior/`
   - Default login: `admin` / `admin123`

## Excel File Format

When uploading Excel files, ensure the following column structure:

| Column | Header | Required | Format |
|--------|--------|----------|--------|
| A | ID Number | Yes | Text |
| B | Name | Yes | Text |
| C | Sex | Yes | "Male" or "Female" |
| D | Barangay | Yes | Text |
| E | City | Yes | Text |
| F | Province | Yes | Text |
| G | Birthdate | Yes | YYYY-MM-DD or MM/DD/YYYY or DD/MM/YYYY |

## File Structure

```
Senior/
├── config.php              # Database configuration and functions
├── index.php               # Main dashboard
├── login.php               # Login page
├── logout.php              # Logout handler
├── upload_excel.php        # Excel upload processor
├── generate_id.php         # ID card generator
├── database.sql            # Database schema
├── composer.json           # PHP dependencies
├── uploads/                # Image upload directory
└── vendor/                 # Composer dependencies (after install)
```

## Usage

1. **Login** with your credentials
2. **Add Person**: Use the modal form for manual entry
3. **Upload Excel**: Bulk import from Excel files
4. **View Records**: Browse all registered persons in the dashboard
5. **Generate ID**: Click the "ID" button to create and print ID cards

## Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Session-based authentication
- File upload validation
- XSS protection with `htmlspecialchars()`

## Browser Compatibility

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Support

For issues or questions, please check the error logs and ensure all requirements are met.

## License

This project is open source and available under the MIT License.
