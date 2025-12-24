# Shopify Custom App Setup

This guide will walk you through setting up and running a Shopify custom app for local development and deployment.

## Prerequisites

Before you begin, ensure you have the following installed:
- PHP (8.^)
- Composer
- Node.js (22.^) and npm
- MySQL
- Ngrok account with authtoken

## Setup Instructions

### 1. Clone the Repository

```bash
git clone <repository-url>
cd <repository-name>
```

### 2. Install Dependencies

Install PHP dependencies via Composer:

```bash
composer install
```

Install Node.js dependencies:

```bash
npm install
```

### 3. Configure Environment Variables

Create a `.env` file by copying the example file:

```bash
cp .env.example .env
```

Generate an application key:

```bash
php artisan key:generate
```

Update the `.env` file with your database credentials and other necessary configuration:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### 4. Create Database and Run Migrations

Create the database specified in your `.env` file, then run migrations:

```bash
php artisan migrate
```

### 5. Install and Configure Ngrok

Install Ngrok (Linux/Ubuntu):

```bash
sudo snap install ngrok
```

For other operating systems, visit [ngrok.com/download](https://ngrok.com/download)

Add your Ngrok authtoken:

```bash
ngrok config add-authtoken YOUR_AUTHTOKEN
```

### 6. Start the Application

Start the Laravel development server:

```bash
php artisan serve
```

In a new terminal window, expose your local server with Ngrok:

```bash
ngrok http 8000
```

Copy the HTTPS forwarding URL provided by Ngrok (e.g., `https://abc123.ngrok.io`).

### 7. Create Shopify App

1. Navigate to the [Shopify Partner Dashboard](https://partners.shopify.com/)
2. Create a new app with the following configuration:
    - **App type**: Custom app
    - **Scopes required**:
        - `read_products`
        - `read_orders`
    - **App URL**: Your Ngrok HTTPS URL (e.g., `https://abc123.ngrok.io`)
    - **Allowed redirection URL(s)**: `https://abc123.ngrok.io/shopify/callback`

### 8. Add Shopify Credentials to `.env`

Copy the API credentials from your Shopify app dashboard and add them to your `.env` file:

```env
SHOPIFY_API_KEY=your_api_key_here
SHOPIFY_API_SECRET=your_api_secret_here
```

Restart your Laravel server to load the new environment variables.

### 9. Distribute the Custom App

1. In the Shopify Partner Dashboard, set your app as a **Custom App**
2. Distribute the app to your target merchant's store
3. Provide the merchant with the installation link

### 10. Merchant Installation

Once the merchant clicks the installation link and approves the required permissions, the app will be installed and accessible as an embedded app within their Shopify admin.

## Important Notes

- **Security**: Never commit your `.env` file to version control. Ensure it's listed in `.gitignore`
- **Ngrok URLs**: Ngrok URLs change each time you restart ngrok (on the free plan). Update your Shopify app URLs accordingly
- **Database Permissions**: Ensure your database user has the necessary permissions to create and modify tables
- **Testing**: Thoroughly test all integrations in a development store before deploying to production
- **Production**: For production deployment, replace Ngrok with a proper domain and SSL certificate

## Troubleshooting

- **Database Connection Errors**: Verify your database credentials in `.env` and ensure the database exists
- **Ngrok Not Working**: Ensure you're using the HTTPS URL and that your authtoken is correctly configured
- **Shopify Authentication Issues**: Double-check that your redirect URL matches exactly in both Shopify and your `.env` file

## Additional Resources

- [Shopify App Development Documentation](https://shopify.dev/docs/apps)
- [Laravel Documentation](https://laravel.com/docs)
- [Ngrok Documentation](https://ngrok.com/docs)

## Support

For issues or questions, please [open an issue](link-to-your-issues-page) or contact the development team.
