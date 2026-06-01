import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_SERVICE_ROLE_KEY
);

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, PUT, DELETE, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  const { id } = req.query;

  // Validate ID
  if (!id || isNaN(Number(id))) {
    return res.status(400).json({
      success: false,
      message: 'Valid portfolio item ID is required'
    });
  }

  const numericId = Number(id);

  try {
    if (req.method === 'GET') {
      // Fetch single portfolio item
      const { data, error } = await supabase
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
        .eq('id', numericId)
        .single();

      if (error) {
        if (error.code === 'PGRST116') {
          return res.status(404).json({
            success: false,
            message: 'Portfolio item not found'
          });
        }
        throw error;
      }

      // Format response to include member_name
      const portfolioItem = {
        ...data,
        member_name: data.team_members ? data.team_members.name : null,
        team_members: undefined // Remove the joined object
      };

      return res.status(200).json({
        success: true,
        message: 'Portfolio item retrieved successfully',
        data: { portfolio_item: portfolioItem }
      });
    } else if (req.method === 'PUT') {
      // Update portfolio item
      const {
        team_member_id,
        title,
        description,
        image_url,
        category,
        project_url,
        featured
      } = req.body;

      // Build update object
      const updateData = {};
      if (team_member_id !== undefined) updateData.team_member_id = team_member_id;
      if (title !== undefined) updateData.title = title;
      if (description !== undefined) updateData.description = description;
      if (image_url !== undefined) updateData.image_url = image_url;
      if (category !== undefined) updateData.category = category;
      if (project_url !== undefined) updateData.project_url = project_url;
      if (featured !== undefined) updateData.featured = featured;
      updateData.updated_at = new Date().toISOString();

      if (Object.keys(updateData).length === 0) {
        return res.status(400).json({
          success: false,
          message: 'No valid fields to update'
        });
      }

      const { data, error } = await supabase
        .from('team_portfolio')
        .update(updateData)
        .eq('id', numericId);

      if (error) throw error;

      if (data.length === 0) {
        return res.status(404).json({
          success: false,
          message: 'Portfolio item not found'
        });
      }

      return res.status(200).json({
        success: true,
        message: 'Portfolio item updated successfully'
      });
    } else if (req.method === 'DELETE') {
      // Delete portfolio item
      const { data, error } = await supabase
        .from('team_portfolio')
        .delete()
        .eq('id', numericId);

      if (error) throw error;

      if (data.length === 0) {
        return res.status(404).json({
          success: false,
          message: 'Portfolio item not found'
        });
      }

      return res.status(200).json({
        success: true,
        message: 'Portfolio item deleted successfully'
      });
    } else {
      return res.status(405).json({
        success: false,
        message: 'Method not allowed'
      });
    }
  } catch (error) {
    console.error(`Error handling portfolio item ${id}:`, error);
    return res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
}