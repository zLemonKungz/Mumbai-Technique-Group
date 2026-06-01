import { createClient } from '@supabase/supabase-js';

// Initialize Supabase client
const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_SERVICE_ROLE_KEY
);

export default async function handler(req, res) {
  // Set CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

  // Handle preflight requests
  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  try {
    if (req.method === 'GET') {
      // Fetch all team members, ordered by name
      const { data, error } = await supabase
        .from('team_members')
        .select('id, name, role, bio, avatar_url, email, social_links, skills, portfolio_items, blog_posts, created_at')
        .order('name');

      if (error) throw error;

      // Format data to ensure JSON fields are objects/arrays (Supabase returns them as such already)
      const teamMembers = data.map(member => ({
        ...member,
        social_links: member.social_links || {},
        skills: member.skills || [],
        portfolio_items: member.portfolio_items || [],
        blog_posts: member.blog_posts || []
      }));

      return res.status(200).json({
        success: true,
        message: 'Team members retrieved successfully',
        data: { team_members: teamMembers }
      });
    } else {
      // Method not allowed
      return res.status(405).json({
        success: false,
        message: 'Method not allowed'
      });
    }
  } catch (error) {
    console.error('Error fetching team members:', error);
    return res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
}