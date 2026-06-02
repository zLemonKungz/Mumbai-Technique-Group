import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_SERVICE_ROLE_KEY
);

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  try {
    if (req.method === 'GET') {
      let query = supabase
        .from('team_blog')
        .select(`
          id,
          team_member_id,
          title,
          content,
          excerpt,
          image_url,
          category,
          featured,
          created_at,
          team_members!team_blog_team_member_id_fkey(name)
        `)
        .order('created_at', { ascending: false });

      // Apply filters from query parameters
      const { team_member_id, category, featured } = req.query;

      if (team_member_id) {
        if (!isNaN(Number(team_member_id))) {
          query = query.eq('team_member_id', Number(team_member_id));
        }
      }

      if (category) {
        query = query.eq('category', category);
      }

      if (featured !== undefined && featured !== '') {
        const featuredBool = featured === '1' || featured === 'true';
        query = query.eq('featured', featuredBool);
      }

      const { data, error } = await query;

      if (error) throw error;

      // Format the data to flatten the team member name
      const blogPosts = data.map(item => {
        const teamMemberName = item.team_members ? item.team_members.name : null;
        return {
          ...item,
          member_name: teamMemberName,
          team_members: undefined
        };
      });

      return res.status(200).json({
        success: true,
        message: 'Blog posts retrieved',
        data: { blog_posts: blogPosts }
      });
    } else {
      return res.status(405).json({
        success: false,
        message: 'Method not allowed'
      });
    }
  } catch (error) {
    console.error('Error fetching blog posts:', error);
    return res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
}