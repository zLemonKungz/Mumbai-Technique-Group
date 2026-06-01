# Team-Capable Portfolio Platform with PHP CMS - Design Specification

## Overview
Transform the existing single-freelancer portfolio site into a team-capable portfolio platform with PHP-based content management system, green color theme, and enhanced visual effects.

## Architecture

### Backend System
- **Technology**: PHP with MySQLi (maintaining existing stack)
- **Database**: MySQL with normalized schema for team data
- **API Layer**: REST-like endpoints serving JSON data to frontend SPA
- **Authentication**: Secure admin login system with session management
- **Data Protection**: Prepared statements, input validation, CSRF tokens

### Database Schema
```sql
-- Team members table
CREATE TABLE team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    bio TEXT,
    avatar_url VARCHAR(255),
    email VARCHAR(100),
    social_links JSON,
    skills JSON,
    portfolio_items JSON,
    blog_posts JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Portfolio items table
CREATE TABLE team_portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_member_id INT,
    title VARCHAR(200),
    description TEXT,
    image_url VARCHAR(255),
    category ENUM('web', 'app', 'branding', 'ecommerce'),
    project_url VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (team_member_id) REFERENCES team_members(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blog posts table
CREATE TABLE team_blog (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_member_id INT,
    title VARCHAR(200),
    content TEXT,
    excerpt TEXT,
    image_url VARCHAR(255),
    published_at TIMESTAMP,
    tags JSON,
    FOREIGN KEY (team_member_id) REFERENCES team_members(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site settings table
CREATE TABLE team_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_title VARCHAR(100) DEFAULT 'PixelTest Team',
    tagline VARCHAR(200) DEFAULT 'Creative Agency',
    logo_url VARCHAR(255),
    color_theme VARCHAR(20) DEFAULT 'green',
    contact_info JSON,
    social_links JSON,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### API Endpoints

#### Public Endpoints
- `GET /api/team` - List all team members (basic info)
- `GET /api/team/:id` - Get full team member details
- `GET /api/portfolio` - Get portfolio items (filterable by teamMemberId, category)
- `GET /api/blog` - Get blog posts (filterable by teamMemberId, tag)
- `GET /api/settings` - Get site configuration
- `POST /api/contact` - Process contact form (existing, unchanged)

#### Admin Endpoints (Require Authentication)
- `POST /api/auth/login` - Admin login
- `POST /api/auth/logout` - Admin logout
- `POST /api/admin/team` - Create team member
- `PUT /api/admin/team/:id` - Update team member
- `DELETE /api/admin/team/:id` - Delete team member
- Similar CRUD endpoints for portfolio, blog, settings
- `POST /api/admin/upload` - Handle media uploads

## Frontend Implementation

### Core Components

#### 1. Team Overview Grid
- Replaces current team section
- Responsive grid layout (1-3 columns based on screen size)
- Each card shows: avatar, name, role, brief bio
- Hover effects: 3D tilt, depth shift, avatar pulse
- Clicking card loads individual member profile

#### 2. Individual Member Profile View
- Loaded via URL hash: `#/team/member-id`
- Features tabbed interface: Skills, Portfolio, Blog Posts
- Back button to return to team overview
- Shareable deep links to specific member profiles

#### 3. Dynamic Content Loading
- All main sections load via AJAX: About, Portfolio, Blog
- Loading skeletons/shimmers during data fetch
- Error states with retry mechanism and user-friendly messages
- Cache busting to prevent stale data

#### 4. Edit Mode Interface
- Activated after admin login
- Visual indicator: colored dot + "Edit Mode Active" label
- Hover-over edit controls on editable content:
  - Pencil icon for text fields
  - Camera icon for images
  - Reorder handles for lists
- Inline editors:
  - Text: click-to-edit with save/cancel
  - Images: click to upload/replace with preview
  - Lists: drag-and-drop reordering
- Auto-save or explicit save buttons based on complexity

### Navigation & URL Structure
- Hash-based routing for SPA-like behavior:
  - `#/about` - About section
  - `#/team` - Team overview grid
  - `#/team/:id` - Individual member profile
  - `#/portfolio` - Portfolio section
  - `#/blog` - Blog section
  - `#/contact` - Contact section
- Browser back/forward button support
- Shareable URLs for direct linking to content

## Visual Design & Theme

### Color System (Green Theme)
Replace all yellow/gold variables:
- `--accent: hsl(120, 70%, 40%)` (vibrant primary green)
- `--accent-dark: hsl(120, 60%, 30%)` (darker secondary green)
- Adjust gradients, borders, shadows, icon colors accordingly
- Ensure WCAG AA contrast compliance for text/UI elements

### Enhanced Visual Effects

#### Team Member Cards
- **Hover State**:
  - Transform: rotateX(10deg) rotateY(-10deg) translateY(-5px)
  - Box-shadow: increased depth and spread
  - Avatar: scale(1.05) with subtle pulse animation
  - Background: slight color shift with transition

#### Section Transitions
- **Entrance Animation**:
  - Staggered fade-in with scale (0.95 → 1)
  - Animation-delay: 100ms per element
  - Direction-based: content slides in from appropriate direction
- **Tab Switching**:
  - Smooth cross-fade between tab content
  - Active tab indicator bar slides horizontally

#### Interactive Elements
- **Buttons**:
  - Hover: background gradient shift + icon bounce
  - Active: slight scale reduction (0.98)
  - Disabled: opacity 0.6 + cursor not-allowed
- **Form Inputs**:
  - Focus: 2px solid accent color glow
  - Valid: subtle green border on success
  - Error: subtle red border on validation failure
- **Nav Tabs**:
  - Active state: background surface-light, text primary
  - Transition: all properties 0.3s ease
  - Indicator: 3px left border in accent color

#### Scroll Effects
- **Progress Indicator**: thin bar in header showing scroll percentage
- **Parallax**: subtle background movement on hero sections
- **Skill Bars**: animate width on scroll into view
- **Fade-in Elements**: content blocks fade in as they enter viewport

## Authentication & Security

### Admin Authentication
- **Login Form**: secure form with CSRF protection
- **Password Handling**: PHP `password_hash()` and `password_verify()`
- **Session Management**: PHP sessions with secure cookies
- **Timeout**: automatic logout after 30 minutes of inactivity
- **Remember Me**: optional persistent login with secure token
- **Brute Force Protection**: rate limiting after 5 failed attempts

### Data Security
- **SQL Injection Prevention**: prepared statements on all queries
- **Input Validation**: server-side validation for all API endpoints
- **Output Encoding**: HTML escaping for admin-edited content
- **File Upload Security**:
  - MIME type validation (images only: jpg, png, gif, webp)
  - Size limits (5MB per file)
  - Randomized filenames to prevent collisions
  - Storage outside web root or with .htaccess protection
- **CSRF Protection**: tokens on all state-changing requests
- **XSS Prevention**: Content Security Policy headers, input sanitization

## Responsive Design & Accessibility

### Breakpoints
- **Mobile**: <768px - Sidebar converts to off-canvas drawer
- **Tablet**: 768-1024px - Sidebar visible, compact layouts
- **Desktop**: >1024px - Fixed sidebar width (260px), expanded main content

### Accessibility Features
- **ARIA Labels**: all interactive elements have descriptive labels
- **Keyboard Navigation**: full keyboard support for admin interface
- **Focus Management**: focus traps in modals, logical tab order
- **Color Contrast**: WCAG AA compliance verified for green palette
- **Reduced Motion**: respects `prefers-reduced-motion` media query
- **Semantic HTML**: proper heading hierarchy, landmark elements
- **Screen Reader Support**: live regions for dynamic content updates

## Implementation Phases

### Phase 1: Foundation & Database
1. Create MySQL database and tables per schema
2. Build core API endpoints for team data retrieval
3. Implement basic authentication system
4. Create team overview grid component
5. Develop individual member profile view

### Phase 2: Content Management
1. Build admin login/logout system
2. Implement CRUD endpoints for all content types
3. Develop edit mode interface with inline editors
4. Add media upload functionality
5. Create settings management interface

### Phase 3: Frontend Enhancements
1. Implement dynamic content loading for all sections
2. Add loading states and error handling
3. Develop URL hash-based routing system
4. Implement visual effects and animations
5. Refine responsive layouts for all breakpoints

### Phase 4: Theme & Styling
1. Replace all yellow/gold CSS variables with green equivalents
2. Update gradients, borders, shadows throughout stylesheet
3. Ensure accessibility compliance with new color palette
4. Add animation classes and transition effects
5. Test and refine hover/focus states

### Phase 5: Testing & Optimization
1. Cross-browser testing (Chrome, Firefox, Safari, Edge)
2. Mobile device testing (various screen sizes)
3. Performance optimization (lazy loading, asset optimization)
4. Security audit and penetration testing
5. User acceptance testing and feedback incorporation

## Dependencies & Requirements
- PHP 7.4+ with MySQLi extension
- MySQL 5.7+ or MariaDB equivalent
- Modern browser support (ES6+, CSS Grid/Flexbox)
- Web server with PHP processing capability (Apache/Nginx)
- SMTP server for contact form notifications (if email enabled)

## Future Enhancements
- Role-based admin permissions (editor, admin, super-admin)
- Content scheduling and versioning
- Analytics integration for tracking views/engagement
- Multilingual support for international teams
- API rate limiting and caching layers
- Integration with third-party services (social media, CRM)

## Files to Create/Modify

### New Files:
- `/api/` directory with PHP endpoint files
- `/admin/` directory for admin interface assets
- `/uploads/` directory for media storage (secured)
- `/includes/` directory for shared PHP functions/db connection
- `database.sql` - schema and sample data

### Modified Files:
- `index.html` - add dynamic containers, team overview grid
- `style.css` - complete theme overhaul to green palette + animations
- `script.js` - significant expansion for data loading, routing, edit mode
- `contact.php` - keep for form handling, ensure consistent DB usage

This design provides a robust, extensible foundation for a team-capable portfolio platform while maintaining the existing technology stack and enhancing the user experience with modern visual effects and interactions.