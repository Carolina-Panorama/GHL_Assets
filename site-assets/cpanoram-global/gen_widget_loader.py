#!/usr/bin/env python3

def generate_widget_loader(widget_path, widget_id, custom_value_key=None):
    """
    widget_path:    e.g. "site-home-widgets/headlines-grid-v2.html"
    widget_id:      e.g. "GHL_TOP_ARTICLES" or "CP_ARTICLE_LIST_FEED_WIDGET"
    custom_value_key (optional):
                    e.g. "top_articles" (without 'custom_values.' prefix)
    """
    version_helper = """
    function withAssetsVersion(callback, timeoutMs) {
      var start = Date.now();

      function readVersion() {
        // Prefer explicit global if already set
        if (window.CP_ASSETS_VERSION) {
          return window.CP_ASSETS_VERSION;
        }

        // Fallback: derive from any existing global JS/CSS URL that includes util-ghl-assets@
        var selector = 'script[src*="util-ghl-assets@"], link[href*="util-ghl-assets@"]';
        var el = document.querySelector(selector);
        if (!el) return null;

        var url = el.src || el.href || '';
        var match = url.match(/util-ghl-assets@([^/]+)/);
        if (match && match[1]) {
          return match[1];
        }

        return null;
      }

      (function check() {
        var v = readVersion();
        if (v) {
          callback(v);
        } else if (Date.now() - start < (timeoutMs || 5000)) {
          setTimeout(check, 25);
        } else {
          console.warn('Could not determine assets version; falling back to "main" for widget loader.');
          callback('main');
        }
      })();
    }
""".rstrip()

    if custom_value_key:
        # Widget that uses a GHL custom_value
        custom_expr = f"{{{{custom_values.{custom_value_key}}}}}"
        return f"""<!-- GHL custom-value script -->
<script id="{widget_id}" type="text/plain">
  {custom_expr}
</script>

<script>
{version_helper}

  (function () {{
    const id = '{widget_id}';
    const anchor = document.getElementById(id);
    if (!anchor) return;

    withAssetsVersion(function(version) {{
      var url = 'https://cdn.jsdelivr.net/gh/Carolina-Panorama/util-ghl-assets@' +
                version +
                '/site-assets/{widget_path}';

      fetch(url)
        .then(function(r) {{ return r.text(); }})
        .then(function(html) {{
          const container = document.createElement('div');
          anchor.parentNode.insertBefore(container, anchor.nextSibling);
          container.innerHTML = html;

          container.querySelectorAll('script').forEach(function(oldScript) {{
            const s = document.createElement('script');
            if (oldScript.src) s.src = oldScript.src;
            else s.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(s, oldScript);
          }});
        }})
        .catch(function(err) {{
          console.error('Error loading widget ({widget_path}):', err);
        }});
    }});
  }})();
</script>"""

    # Widget that does NOT use a custom_value
    return f"""<script id="{widget_id}">
  (function () {{
    const anchor = document.getElementById('{widget_id}') || document.currentScript;
    if (!anchor) return;

{version_helper}

    withAssetsVersion(function(version) {{
      var url = 'https://cdn.jsdelivr.net/gh/Carolina-Panorama/util-ghl-assets@' +
                version +
                '/site-assets/{widget_path}';

      fetch(url)
        .then(function(r) {{ return r.text(); }})
        .then(function(html) {{
          const container = document.createElement('div');
          anchor.parentNode.insertBefore(container, anchor.nextSibling);
          container.innerHTML = html;

          container.querySelectorAll('script').forEach(function(oldScript) {{
            const s = document.createElement('script');
            if (oldScript.src) s.src = oldScript.src;
            else s.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(s, oldScript);
          }});
        }})
        .catch(function(err) {{
          console.error('Error loading widget ({widget_path}):', err);
        }});
    }});
  }})();
</script>"""


if __name__ == "__main__":
  import os
  import re
  import sys

  if len(sys.argv) < 2:
    print("Usage: gen_widget_loader.py <widget_path_relative_to_site-assets> [custom_value_key]")
    print("Example (no custom value): gen_widget_loader.py site-home-widgets/article-list-feed.html")
    print("Example (with custom value): gen_widget_loader.py site-home-widgets/trending-carousel-v2.html editors_picks")
    sys.exit(1)

  widget_path = sys.argv[1]
  custom_value_key = None
  if len(sys.argv) >= 3:
    custom_value_key = sys.argv[2]

  # Derive widget_id from filename (minus extension), formatted as CP_{FILENAME}
  # Example: "article-list-feed.html" -> "CP_ARTICLE_LIST_FEED"
  basename = os.path.basename(widget_path)
  stem, _ = os.path.splitext(basename)
  # Replace any non-alphanumeric characters with underscore, then uppercase
  formatted = re.sub(r"[^A-Za-z0-9]+", "_", stem).strip("_").upper()
  widget_id = f"CP_{formatted}"

  snippet = generate_widget_loader(widget_path=widget_path, widget_id=widget_id, custom_value_key=custom_value_key)
  print(snippet)