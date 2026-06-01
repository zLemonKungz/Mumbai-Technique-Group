# The Developer 03 - Freelancer Portfolio

A dark-themed single-page portfolio site built with HTML5, CSS3, Vanilla JavaScript, PHP, and MySQL.

## Features
- Single-page layout with tabbed sections (About, Resume, Portfolio, Blog, Contact)
- Expandable sidebar with contact details and social links
- Responsive design
- Testimonials modal
- Filterable project gallery
- Animated skill bars
- PHP/MySQL contact form with prepared statements

## Tech Stack
- HTML5
- CSS3
- JavaScript (Vanilla)
- PHP (with MySQLi)
- MySQL
- Ionicons 5.5.2
- Google Fonts: Poppins

## Setup
1. **Database**: Create a MySQL database and table for storing contact form submissions.
   - Table name: `tdata` (or set `CONTACT_TABLE` environment variable)
   - Columns: `id` (INT, AUTO_INCREMENT, PRIMARY KEY), `name` (VARCHAR), `email` (VARCHAR), `subject` (VARCHAR), `message` (TEXT), `created_at` (TIMESTAMP DEFAULT CURRENT_TIMESTAMP)

2. **Environment Variables**: Set the following environment variables for the PHP backend:
   - `DB_HOST`: MySQL host (default: localhost)
   - `DB_PORT`: MySQL port (default: 3306)
   - `DB_NAME`: Database name
   - `DB_USER`: MySQL username
   - `DB_PASSWORD`: MySQL password
   - `CONTACT_TABLE`: Table name for contact submissions (default: `tdata`)

3. **Deployment**: Place all files in your web server's public directory. Ensure PHP is installed and configured to run `.php` files.

## File Structure
- `index.html`: Main SPA markup
- `style.css`: All styles (dark theme, layout, animations)
- `script.js`: DOM manipulation, tab switching, sidebar toggle, modal handling, form validation, AJAX submit
- `contact.php`: Backend endpoint to process form submissions via POST, connect to MySQL using MySQLi prepared statements, and return JSON response.

## Customization
- Replace placeholder images (`avatar.png`, `testimonial*.jpg`, `project*.jpg`, `blog*.jpg`, `logo*.png`) with your own assets.
- Update content in `index.html` (bio, services, resume items, projects, blog posts, etc.).
- Adjust colors in `style.css` by modifying the CSS variables at the top.

## Browser Support
Works in all modern browsers that support ES modules, CSS Grid, and Flexbox.

## Notes
- The site is designed as a static single-page application; no build tools are required.
- The contact form uses AJAX to submit data to `contact.php` without page reload.
- For production, restrict the `Access-Control-Allow-Origin` header in `contact.php` to your domain only.