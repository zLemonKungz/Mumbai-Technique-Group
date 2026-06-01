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
        .from('team_portfolio')
        .select(`
          id,
          team_member_id,
          title,
          description,
          image_url,
          category,
          project_url,
          featured,
          created_at,
          team_members!team_portfolio_team_member_id_fkey(name)
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

      // Format the data to flatten the team member name and ensure proper types
      const portfolioItems = data.map(item => {
        const teamMemberName = item.team_members ? item.team_members.name : null;
        return {
          ...item,
          member_name: teamMemberName,
          // Remove the joined team_members object to keep the structure clean
          team_members: undefined
        };
      });

      return res.status(200).json({
        success: true,
        message: 'Portfolio items retrieved',
        data: { portfolio_items: portfolioItems }
      });
    } else {
      return res.status(405).json({
        success: false,
        message: 'Method not allowed'
      });
    }
  } catch (error) {
    console.error('Error fetching portfolio items:', error);
    return res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
}