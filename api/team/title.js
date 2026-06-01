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
      const { data, error } = await supabase
        .from('team_settings')
        .select('value')
        .eq('key', 'team_title')
        .single();

      if (error && error.code !== 'PGRST116') throw error;

      const title = data ? data.value : 'Our Team';

      return res.status(200).json({
        success: true,
        message: 'Team title retrieved successfully',
        data: { title }
      });
    } else {
      return res.status(405).json({
        success: false,
        message: 'Method not allowed'
      });
    }
  } catch (error) {
    console.error('Error fetching team title:', error);
    return res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
}