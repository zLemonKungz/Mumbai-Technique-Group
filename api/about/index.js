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
      const { data: aboutTitleData, error: aboutTitleError } = await supabase
        .from('team_settings')
        .select('value')
        .eq('key', 'about_title')
        .single();

      const { data: aboutContentData, error: aboutContentError } = await supabase
        .from('team_settings')
        .select('value')
        .eq('key', 'about_content')
        .single();

      if (aboutTitleError && aboutTitleError.code !== 'PGRST116') throw aboutTitleError;
      if (aboutContentError && aboutContentError.code !== 'PGRST116') throw aboutContentError;

      const title = aboutTitleData ? aboutTitleData.value : 'About Us';
      const content = aboutContentData ? aboutContentData.value : '<p>เราเป็นทีมสร้างสรรค์ที่หลงใหลในการมอบประสบการณ์ดิจิทัลที่ยอดเยี่ยม วิธีการทำงานแบบสหวิทยาการของเราผสมผสานการออกแบบที่มีความคิด การพัฒนาที่แข็งแกร่ง และการคิดเชิงกลยุทธ์เพื่อสร้างโซลูชันที่ดึงดูดผู้ใช้และขับเคลื่อนผลลัพธ์ทางธุรกิจ</p><p>ด้วยประสบการณ์หลายปีในหลากหลายอุตสาหกรรม เราภูมิใจในความสามารถในการเข้าใจความท้าทายที่ซับซ้อนและเปลี่ยนให้เป็นโซลูชันที่สง่างามและใช้งานได้จริงที่เกินความคาดหวัง</p>';

      return res.status(200).json({
        success: true,
        message: 'About content retrieved successfully',
        data: { title, content }
      });
    } else {
      return res.status(405).json({
        success: false,
        message: 'Method not allowed'
      });
    }
  } catch (error) {
    console.error('Error fetching about content:', error);
    return res.status(500).json({
      success: false,
      message: 'Internal server error'
    });
  }
}