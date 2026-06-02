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
      message: 'Valid blog post ID is required'
    });
  }

  const numericId = Number(id);

  try {
    if (req.method === 'GET') {
      // Fetch single blog post
      const { data, error } = await supabase
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
        .eq('id', numericId)
        .single();

      if (error) {
        if (error.code === 'PGRST116') {
          return res.status(404).json({
            success: false,
            message: 'Blog post not found'
          });
        }
        throw error;
      }

      // Format response to include member_name
      const blogPost = {
        ...data,
        member_name: data.team_members ? data.team_members.name : null,
        team_members: undefined // Remove the joined object
      };

      return res.status(200).json({
        success: true,
        message: 'Blog post retrieved successfully',
        data: { blog_post: blogPost }
      });
    } else if (req.method === 'PUT') {
      // Update blog post
      const {
        team_member_id,
        title,
        content,
        excerpt,
        image_url,
        category,
        featured
      } = req.body;

      // Build update object
      const updateData = {};
      if (team_member_id !== undefined) updateData.team_member_id = team_member_id;
      if (title !== undefined) updateData.title = title;
      if (content !== undefined) updateData.content = content;
      if (excerpt !== undefined) updateData.excerpt = excerpt;
      if (image_url !== undefined) updateData.image_url = image_url;
      if (category !== undefined) updateData.category = category;
      if (featured !== undefined) updateData.featured = featured;
      updateData.updated_at = new Date().toISOString();

      if (Object.keys(updateData).length === 0) {
        return res.status(400).json({
          success: false,
          message: 'No valid fields to update'
        });
      }

      const { data, error } = await supabase
        .from('team_blog')
        .update(updateData)
        .eq('id', numericId);

      if (error) throw error;

      if (data.length === 0) {
        return res.status(404).json({
          success: false,
          message: 'Blog post not found'
        });
      }

      return res.status(200).json({
        success: true,
        message: 'Blog post updated successfully'
      });
    } else if (req.method === 'DELETE') {
      // Delete blog post
      const { data, error } = await supabase
        .from('team_blog')
        .delete()
        .eq('id', numericId);

      if (error) throw error;

      if (data.length === 0) {
        return res.status(404).json({
          success: false,
          message: 'Blog post not found'
        });
      }

      return res.status(200).json({
        success: true,
        message: 'Blog post deleted successfully'
      });
    } else {
      return res.status(405).json({
        success: false,
        message: 'Method not allowed'
      });
    }
  } catch (error) {
    console.error(`Error handling blog post ${id}:`, error);
    return res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
}