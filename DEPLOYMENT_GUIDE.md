# Deployment Guide: Mumbai-Technique Portfolio System

This guide explains how to deploy your team-capable portfolio system using:
- **GitHub Pages** for hosting the static frontend (HTML, CSS, JavaScript)
- **Vercel** for hosting serverless functions (backend API)
- **Supabase** for the PostgreSQL database

## Prerequisites

1. **GitHub Account** - Your code is already in a GitHub repository
2. **Vercel Account** - [vercel.com](https://vercel.com) (sign up with GitHub for easy login)
3. **Supabase Account** - [supabase.com](https://supabase.com) (sign up with GitHub)
4. **Node.js** (optional, for local testing) - [nodejs.org](https://nodejs.org)

## Step 1: Set Up Supabase Database

1. Log in to [Supabase](https://supabase.com) and create a new project
2. Note down your:
   - **Project URL** (found in Settings → API)
   - **Service Role Key** (found in Settings → API)
3. In Supabase, go to the SQL Editor and run the following schema:

```sql
-- Copy and paste this into Supabase SQL Editor
CREATE TABLE IF NOT EXISTS team_members (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    bio TEXT,
    avatar_url VARCHAR(255),
    email VARCHAR(100),
    social_links JSONB,
    skills JSONB,
    portfolio_items JSONB,
    blog_posts JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS team_portfolio (
    id SERIAL PRIMARY KEY,
    team_member_id INTEGER,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    category VARCHAR(50),
    project_url VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_member_id) REFERENCES team_members(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS team_blog (
    id SERIAL PRIMARY KEY,
    team_member_id INTEGER,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    excerpt TEXT,
    image_url VARCHAR(255),
    category VARCHAR(50),
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_member_id) REFERENCES team_members(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS team_settings (
    id SERIAL PRIMARY KEY,
    "key" VARCHAR(100) NOT NULL UNIQUE,
    "value" TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contact_form_submissions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default settings
INSERT INTO team_settings ("key", "value") VALUES
('about_title', 'About Us'),
('about_content', '<p>เราเป็นทีมสร้างสรรค์ที่หลงใหลในการมอบประสบการณ์ดิจิทัลที่ยอดเยี่ยม วิธีการทำงานแบบสหวิทยาการของเราผสมผสานการออกแบบที่มีความคิด การพัฒนาที่แข็งแกร่ง และการคิดเชิงกลยุทธ์เพื่อสร้างโซลูชันที่ดึงดูดผู้ใช้และขับเคลื่อนผลลัพธ์ทางธุรกิจ</p><p>ด้วยประสบการณ์หลายปีในหลากหลายอุตสาหกรรม เราภูมิใจในความสามารถในการเข้าใจความท้าทายที่ซับซ้อนและเปลี่ยนให้เป็นโซลูชันที่สง่างามและใช้งานได้จริงที่เกินความคาดหวัง</p>'),
('team_title', 'Our Team'),
('site_title', 'PixelTest Team | Creative Agency'),
('site_description', 'เอเจนซี่สร้างสรรค์ที่เชี่ยวชาญด้านเว็บดีไซน์ แอปมือถือ การสร้างแบรนด์ และโซลูชันอีคอมเมิร์ซ');

-- Create indexes for better performance
CREATE INDEX idx_team_members_name ON team_members(name);
CREATE INDEX idx_team_portfolio_team_member ON team_portfolio(team_member_id);
CREATE INDEX idx_team_portfolio_category ON team_portfolio(category);
CREATE INDEX idx_team_portfolio_featured ON team_portfolio(featured);
CREATE INDEX idx_team_blog_team_member ON team_blog(team_member_id);
CREATE INDEX idx_team_blog_category ON team_blog(category);
CREATE INDEX idx_team_blog_featured ON team_blog(featured);
CREATE INDEX idx_contact_submissions_created ON contact_form_submissions(created_at);
```

## Step 2: Set Up Vercel Project

1. Log in to [Vercel](https://vercel.com) and click "New Project"
2. Import your GitHub repository (`Mumbai-Technique.github.io`)
3. Vercel will detect it's a static site - we'll add serverless functions next
4. After importing, go to your project settings in Vercel:
   - Settings → Environment Variables
   - Add the following variables:
     - `SUPABASE_URL`: [Your Supabase Project URL]
     - `SUPABASE_SERVICE_ROLE_KEY`: [Your Supabase Service Role Key]
     - `ADMIN_USERNAME`: `admin` (or any username you prefer)
     - `ADMIN_PASSWORD`: [Choose a secure password]
5. Vercel will automatically detect and deploy your serverless functions in the `/api` directory

## Step 3: Update Frontend Configuration

1. Open `script.js` in your repository
2. Find this line near the top:
   ```javascript
   const API_BASE = 'https://your-vercel-project-name.vercel.app/api';
   ```
3. Replace `your-vercel-project-name` with your actual Vercel project subdomain
   (you can find this in your Vercel project dashboard under "Domains")
4. Save and commit the changes:
   ```bash
   git add script.js
   git commit -m "Update API base URL to point to Vercel deployment"
   git push
   ```

## Step 4: Verify Deployment

1. Wait for Vercel to finish deploying (check the Deployments tab in Vercel dashboard)
2. Visit your GitHub Pages site (e.g., `https://username.github.io/Mumbai-Technique.github.io`)
3. Test the functionality:
   - Navigate between tabs (About, Team, Portfolio, Blog, Contact)
   - Check if team members appear (you'll need to add some via admin panel first)
   - Test the contact form
4. To access the admin panel:
   - You'll need to create admin routes (optional - for now you can manage data directly in Supabase)
   - For a quick admin interface, you can use Supabase's own dashboard

## Step 5: (Optional) Set Up Admin Routes

If you want a full admin interface on Vercel as well:

1. Create an `/admin` directory in your repository
2. Move your admin PHP files there and convert them to serverless functions (similar to `/api/`)
3. Add a `vercel.json` file to your repository root with:
   ```json
   {
     "rewrites": [
       { "source": "/admin/:path*", "destination": "/admin/:path*" }
     ]
   }
   ```
4. Update environment variables in Vercel if needed
5. Push changes and Vercel will deploy the admin interface

## Step 6: Manage Data

You can manage your data in three ways:
1. **Directly in Supabase** - Use the Supabase dashboard to insert/edit/delete records
2. **Via API** - Use tools like Postman or curl to call your Vercel endpoints
3. **Admin Interface** - If you set up admin routes (Step 5)

## Troubleshooting

### Common Issues

1. **CORS Errors** - Make sure all your serverless functions have:
   ```javascript
   res.setHeader('Access-Control-Allow-Origin', '*');
   res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
   res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
   ```
   And handle OPTIONS requests.

2. **Database Connection Errors** - Double-check:
   - `SUPABASE_URL` and `SUPABASE_SERVICE_ROLE_KEY` are correct in Vercel environment variables
   - Your Supabase project is active (not paused)

3. **API Not Found (404)** - Verify:
   - Your `API_BASE` in `script.js` matches your Vercel domain
   - The serverless functions are correctly placed in `/api/` directory
   - Vercel has finished deploying after your last push

4. **Empty Data** - Check if you have any data in your Supabase tables:
   - In Supabase dashboard, go to Table Editor and check each table
   - You may need to insert some test data

## Updating Your Site

Whenever you make changes:
1. For frontend changes (HTML, CSS, JavaScript): Push to GitHub → GitHub Pages updates automatically
2. For backend changes (serverless functions): Push to GitHub → Vercel updates automatically
3. For database changes: Make them directly in Supabase (or via migration scripts)

## Performance & Scaling

- **GitHub Pages**: Excellent for static assets, global CDN, free
- **Vercel Serverless Functions**: Auto-scales, you pay only for usage, generous free tier
- **Supabase PostgreSQL**: Managed database with auto-scaling, backups, and point-in-time recovery

## Security Notes

1. In a production environment, you should:
   - Use proper JWT authentication instead of simple username/password check
   - Restrict CORS origins to your domain only
   - Use Supabase Row Level Security (RLS) for finer-grained access control
   - Hash passwords (though we're using env vars for admin login, which is acceptable for a small team site)
   - Validate and sanitize all inputs (we're already doing this)

2. Current security measures in place:
   - Input sanitization to prevent XSS
   - Prepared statements via Supabase (prevents SQL injection)
   - Environment variables for secrets (not committed to repo)
   - Basic validation on all endpoints

## Congratulations!

Your team-capable portfolio system is now deployed with:
- ✅ Static frontend hosted on GitHub Pages (fast, free, reliable)
- ✅ Dynamic backend powered by Vercel serverless functions (scalable, secure)
- ✅ PostgreSQL database via Supabase (relational, performant)
- ✅ Green color scheme as requested
- ✅ Full CRUD operations for team members, portfolio, and blog
- ✅ Admin login capability (via environment variables)
- ✅ Contact form with database storage
- ✅ Edit mode toggle for content management
- ✅ Responsive design with animations and transitions

For any questions or issues, check the deployment logs in Vercel and Supabase dashboards, and browsers console for frontend errors.