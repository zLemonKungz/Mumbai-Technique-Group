import { createClient } from '@supabase/supabase-js';

const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_SERVICE_ROLE_KEY
);

export default async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, PUT, DELETE, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  const { id } = req.query;

  // Validate ID
  if (!id || isNaN(Number(id))) {
    return res.status(400).json({
      success: false,
      message: 'Valid team member ID is required'
    });
  }

  const numericId = Number(id);

  try {
    if (req.method === 'GET') {
      // Fetch single team member
      const { data, error } = await supabase
        .from('team_members')
        .select('id, name, role, bio, avatar_url, email, social_links, skills, portfolio_items, blog_posts, created_at, updated_at')
        .eq('id', numericId)
        .single();

      if (error) {
        if (error.code === 'PGRST116') {
          return res.status(404).json({
            success: false,
            message: 'Team member not found'
          });
        }
        throw error;
      }

      return res.status(200).json({
        success: true,
        message: 'Team member retrieved successfully',
        data: {
          team_member: {
            ...data,
            social_links: data.social_links || {},
            skills: data.skills || [],
            portfolio_items: data.portfolio_items || [],
            blog_posts: data.blog_posts || []
          }
        }
      });
    } else if (req.method === 'PUT' || req.method === 'PATCH') {
      // Update team member (require admin auth - simplified)
      // In production, you should verify JWT or session
      // For simplicity, we'll allow update if request includes a secret token or we can skip auth for demo
      // TODO: Add proper authentication

      const {
        name,
        role,
        bio,
        avatar_url,
        email,
        social_links,
        skills,
        portfolio_items,
        blog_posts
      } = req.body;

      // Build update object
      const updateData = {};
      if (name !== undefined) updateData.name = name;
      if (role !== undefined) updateData.role = role;
      if (bio !== undefined) updateData.bio = bio;
      if (avatar_url !== undefined) updateData.avatar_url = avatar_url;
      if (email !== undefined) updateData.email = email;
      if (social_links !== undefined) updateData.social_links = social_links;
      if (skills !== undefined) updateData.skills = skills;
      if (portfolio_items !== undefined) updateData.portfolio_items = portfolio_items;
      if (blog_posts !== undefined) updateData.blog_posts = blog_posts;
      updateData.updated_at = new Date().toISOString();

      if (Object.keys(updateData).length === 0) {
        return res.status(400).json({
          success: false,
          message: 'No valid fields to update'
        });
      }

      const { data, error } = await supabase
        .from('team_members')
        .update(updateData)
        .eq('id', numericId);

      if (error) throw error;

      if (data.length === 0) {
        return res.status(404).json({
          success: false,
          message: 'Team member not found'
        });
      }

      return res.status(200).json({
        success: true,
        message: 'Team member updated successfully'
      });
    } else if (req.method === 'DELETE') {
      // Delete team member (require admin auth)
      const { data, error } = await supabase
        .from('team_members')
        .delete()
        .eq('id', numericId);

      if (error) throw error;

      if (data.length === 0) {
        return res.status(404).json({
          success: false,
          message: 'Team member not found'
        });
      }

      return res.status(200).json({
        success: true,
        message: 'Team member deleted successfully'
      });
    } else {
      return res.status(405).json({
        success: false,
        message: 'Method not allowed'
      });
    }
  } catch (error) {
    console.error(`Error handling team member ${id}:`, error);
    return res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
}