export default {
  async fetch(request, env) {
    // Helper function to normalize date strings to M-D-YYYY format (no leading zeros)
    const normalizeDateForSearch = (dateString) => {
      // Try to parse various formats: M/D/YYYY, MM/DD/YYYY, M-D-YYYY, etc.
      const patterns = [
        /(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/,  // M/D/YYYY or MM/DD/YYYY
        /(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})/   // YYYY/M/D or YYYY-MM-DD
      ];
      
      for (const pattern of patterns) {
        const match = dateString.match(pattern);
        if (match) {
          let month, day, year;
          if (match[1].length === 4) {
            // YYYY-M-D format
            year = match[1];
            month = parseInt(match[2], 10);
            day = parseInt(match[3], 10);
          } else {
            // M-D-YYYY format
            month = parseInt(match[1], 10);
            day = parseInt(match[2], 10);
            year = match[3];
          }
          return `${month}-${day}-${year}`;
        }
      }
      
      return dateString; // Return as-is if no pattern matches
    };
    
    // Add CORS headers
    const corsHeaders = {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, OPTIONS',
      'Access-Control-Allow-Headers': 'Content-Type',
      'Content-Type': 'application/json'
    };

    // Handle CORS preflight
    if (request.method === 'OPTIONS') {
      return new Response(null, { headers: corsHeaders });
    }

    // Parse URL for search endpoint
    const url = new URL(request.url);
    
    // Security: Only allow root path and /search requests
    if (url.pathname !== '/' && url.pathname !== '' && url.pathname !== '/search') {
      return new Response('Not Found', { 
        status: 404,
        headers: { 'Content-Type': 'text/plain' }
      });
    }

    // Security: Only allow requests from carolinapanorama.com or .org (including subdomains)
    const referer = request.headers.get('Referer') || '';
    const origin = request.headers.get('Origin') || '';
    
    const allowedDomains = ['carolinapanorama.com', 'carolinapanorama.org'];
    
    const checkDomain = (urlString) => {
      if (!urlString) return false;
      try {
        const hostname = new URL(urlString).hostname.toLowerCase();
        return allowedDomains.some(domain => 
          hostname === domain || hostname.endsWith('.' + domain)
        );
      } catch {
        return false;
      }
    };
    
    if (!checkDomain(referer) && !checkDomain(origin)) {
      return new Response('Forbidden', { 
        status: 403,
        headers: { 'Content-Type': 'text/plain' }
      });
    }

    try {
      // Search endpoint - find file by date
      if (url.pathname === '/search') {
        const dateParam = url.searchParams.get('date');
        if (!dateParam) {
          return new Response(JSON.stringify({ error: 'Missing date parameter' }), {
            status: 400,
            headers: corsHeaders
          });
        }
        
        // Normalize date to match file format (M-D-YYYY without leading zeros)
        const normalizedDate = normalizeDateForSearch(dateParam);
        
        // List all objects in the bucket
        const listed = await env.MY_BUCKET.list();
        
        // Find matching file
        for (const object of listed.objects) {
          if (object.key.match(/\d+-\d+-\d+.*\.pdf$/i)) {
            const dateMatch = object.key.match(/(\d+-\d+-\d+)/);
            if (dateMatch && dateMatch[1] === normalizedDate) {
              const publicUrl = `https://files.carolinapanorama.org/${object.key}`;
              return new Response(JSON.stringify({ 
                url: publicUrl, 
                date: dateMatch[1] 
              }), {
                headers: corsHeaders
              });
            }
          }
        }
        
        return new Response(JSON.stringify({ error: 'File not found' }), {
          status: 404,
          headers: corsHeaders
        });
      }
      
      // List all objects in the bucket
      const listed = await env.MY_BUCKET.list();
      
      // Filter and format files
      const fileList = {};
      
      for (const object of listed.objects) {
        // Only include PDF files with date pattern
        if (object.key.match(/\d+-\d+-\d+.*\.pdf$/i)) {
          const dateMatch = object.key.match(/(\d+-\d+-\d+)/);
          if (dateMatch) {
            // Construct public URL
            const publicUrl = `https://files.carolinapanorama.org/${object.key}`;
            fileList[publicUrl] = dateMatch[1];
          }
        }
      }
      
      return new Response(JSON.stringify(fileList), {
        headers: corsHeaders
      });
      
    } catch (error) {
      return new Response(JSON.stringify({ error: error.message }), {
        status: 500,
        headers: corsHeaders
      });
    }
  }
};