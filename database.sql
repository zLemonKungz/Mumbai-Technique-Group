-- Database schema for PixelTest Team Portfolio System
-- This schema supports multiple team members with individual profiles, portfolios, and blogs

-- Create database if it doesn't exist (comment out if using existing database)
-- CREATE DATABASE IF NOT EXISTS teampostudio;
-- USE teampostudio;

-- Team Members Table
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    bio TEXT,
    avatar_url VARCHAR(255),
    email VARCHAR(100),
    social_links JSON, -- Stores social media links as JSON object
    skills JSON, -- Stores skills as JSON array of objects
    portfolio_items JSON, -- Stores portfolio item IDs as JSON array
    blog_posts JSON, -- Stores blog post IDs as JSON array
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Portfolio Items Table
CREATE TABLE IF NOT EXISTS team_portfolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_member_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    category VARCHAR(50), -- e.g., web design, mobile apps, branding, ecommerce
    project_url VARCHAR(255),
    featured TINYINT(1) DEFAULT 0, -- 0 = not featured, 1 = featured
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_member_id) REFERENCES team_members(id) ON DELETE CASCADE
);

-- Blog Posts Table
CREATE TABLE IF NOT EXISTS team_blog (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_member_id INT,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    excerpt TEXT,
    image_url VARCHAR(255),
    category VARCHAR(50),
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (team_member_id) REFERENCES team_members(id) ON DELETE CASCADE
);

-- Team Settings Table (for site-wide settings and dynamic content)
CREATE TABLE IF NOT EXISTS team_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    `value` TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Contact Form Submissions Table
CREATE TABLE IF NOT EXISTS contact_form_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT IGNORE INTO team_settings (`key`, `value`) VALUES
('about_title', 'About Us'),
('about_content', '<p>We are a creative team passionate about delivering exceptional digital experiences. Our multidisciplinary approach combines thoughtful design, robust development, and strategic thinking to create solutions that engage users and drive business results.</p><p>With years of experience across various industries, we pride ourselves on our ability to understand complex challenges and transform them into elegant, functional solutions that exceed expectations.</p>'),
('team_title', 'Our Team'),
('site_title', 'PixelTest Team | Creative Agency'),
('site_description', 'Creative agency specializing in web design, mobile apps, branding, and e-commerce solutions');

-- Indexes for better performance
CREATE INDEX idx_team_members_name ON team_members(name);
CREATE INDEX idx_team_portfolio_team_member ON team_portfolio(team_member_id);
CREATE INDEX idx_team_portfolio_category ON team_portfolio(category);
CREATE INDEX idx_team_portfolio_featured ON team_portfolio(featured);
CREATE INDEX idx_team_blog_team_member ON team_blog(team_member_id);
CREATE INDEX idx_team_blog_category ON team_blog(category);
CREATE INDEX idx_team_blog_featured ON team_blog(featured);
CREATE INDEX idx_contact_submissions_created ON contact_form_submissions(created_at);