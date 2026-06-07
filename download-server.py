#!/usr/bin/env python3
"""
EasyTeam Download Server - Minecraft Paper Versions Downloader
Run: python3 download-server.py
"""

import os
import json
from flask import Flask, send_file, render_template_string, abort, jsonify

app = Flask(__name__)

VERSIONS_DIR = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'storage', 'versions')

# HTML Template - clean modern design
HTML_TEMPLATE = """
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyTeam Download Server</title>
    <style>
        :root {
            --bg: #0f0f1a;
            --card: #1a1a2e;
            --accent: #6366f1;
            --accent-hover: #818cf8;
            --text: #e2e8f0;
            --text-muted: #94a3b8;
            --success: #22c55e;
            --border: #2a2a4a;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            padding: 2rem 1rem;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .header h1 {
            font-size: 2rem;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        .header p {
            color: var(--text-muted);
            font-size: 1rem;
        }
        .header .badge {
            display: inline-block;
            background: var(--accent);
            color: white;
            padding: 0.25rem 1rem;
            border-radius: 999px;
            font-size: 0.85rem;
            margin-top: 0.75rem;
        }
        .stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 1.5rem 0;
        }
        .stat-item {
            text-align: center;
        }
        .stat-item .num {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--accent);
        }
        .stat-item .label {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .download-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease;
        }
        .download-card:hover {
            border-color: var(--accent);
            transform: translateY(-1px);
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.15);
        }
        .version-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .version-badge {
            background: var(--accent);
            color: white;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            min-width: 5rem;
            text-align: center;
        }
        .version-meta {
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        .version-meta span {
            margin-right: 1rem;
        }
        .download-btn {
            background: var(--accent);
            color: white;
            border: none;
            padding: 0.65rem 1.5rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .download-btn:hover {
            background: var(--accent-hover);
            transform: scale(1.02);
        }
        .download-btn .icon {
            font-size: 1.1rem;
        }
        .wget-box {
            background: #0a0a1a;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-top: 0.5rem;
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
            color: var(--success);
            display: none;
            word-break: break-all;
        }
        .wget-box.show { display: block; }
        .wget-box .copy-btn {
            float: right;
            background: var(--accent);
            color: white;
            border: none;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
        }
        .wget-box .copy-btn:hover { background: var(--accent-hover); }
        .commands-section {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .commands-section h3 {
            margin-bottom: 1rem;
            color: var(--accent);
        }
        .commands-section code {
            display: block;
            background: #0a0a1a;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            border-left: 3px solid var(--accent);
        }
        .commands-section .label {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
        }
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: var(--success);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.9rem;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        .toast.show { opacity: 1; }
        .footer {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.8rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⬇ EasyTeam Download Server</h1>
            <p>Download Minecraft Paper server JARs — bypass sanctions & filters</p>
            <div class="badge">🚀 {{ total_size }} total • {{ count }} versions</div>
        </div>

        <div class="stats">
            <div class="stat-item">
                <div class="num">{{ count }}</div>
                <div class="label">Versions</div>
            </div>
            <div class="stat-item">
                <div class="num">{{ total_size }}</div>
                <div class="label">Total Size</div>
            </div>
            <div class="stat-item">
                <div class="num">{{ hostname }}</div>
                <div class="label">Server</div>
            </div>
        </div>

        <div class="commands-section">
            <h3>📋 Quick Download Commands</h3>
            <p class="label">Download all versions at once:</p>
            <code id="download-all-cmd">{% for v in versions %}wget {{ base_url }}/download/{{ v.filename }} &{% if not loop.last %} {% endif %}{% endfor %}</code>
            <p class="label">Download single version (example - 1.20.1):</p>
            <code>wget {{ base_url }}/download/paper-1.20.1.jar</code>
            <p class="label">Download with curl:</p>
            <code>curl -O {{ base_url }}/download/paper-1.20.1.jar</code>
        </div>

        <h2 style="margin-bottom: 1rem; font-size: 1.25rem;">📦 Available Versions</h2>

        {% for v in versions %}
        <div class="download-card">
            <div class="version-info">
                <div class="version-badge">{{ v.mc_version }}</div>
                <div>
                    <div style="font-weight: 500;">Paper {{ v.mc_version }}</div>
                    <div class="version-meta">
                        <span>📦 {{ v.size }}</span>
                        <span>📅 {{ v.type }}</span>
                    </div>
                </div>
            </div>
            <div style="text-align: right;">
                <a href="/download/{{ v.filename }}" class="download-btn" download>
                    <span class="icon">⬇</span> Download
                </a>
                <button class="download-btn" style="background: var(--border); margin-left: 0.5rem; font-size: 0.8rem;" onclick="toggleWget('{{ v.filename }}')">🔗 wget</button>
                <div class="wget-box" id="wget-{{ v.filename }}">
                    wget {{ base_url }}/download/{{ v.filename }}
                    <button class="copy-btn" onclick="copyCmd('wget {{ base_url }}/download/{{ v.filename }}')">Copy</button>
                </div>
            </div>
        </div>
        {% endfor %}

        <div class="footer">
            EasyTeam Download Server • Made for Iranian Minecraft Server Admins 🇮🇷
        </div>
    </div>

    <div class="toast" id="toast">Copied to clipboard!</div>

    <script>
        function toggleWget(filename) {
            const box = document.getElementById('wget-' + filename);
            box.classList.toggle('show');
        }
        async function copyCmd(cmd) {
            try {
                await navigator.clipboard.writeText(cmd);
                const toast = document.getElementById('toast');
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 2000);
            } catch(e) {
                // fallback
                const ta = document.createElement('textarea');
                ta.value = cmd;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
        }
    </script>
</body>
</html>
"""

def format_size(size_bytes):
    for unit in ['B', 'KB', 'MB', 'GB']:
        if size_bytes < 1024:
            return f"{size_bytes:.1f}{unit}"
        size_bytes /= 1024
    return f"{size_bytes:.1f}GB"

def get_versions():
    versions = []
    if not os.path.exists(VERSIONS_DIR):
        return versions
    
    total_bytes = 0
    for f in sorted(os.listdir(VERSIONS_DIR)):
        if f.endswith('.jar') and f.startswith('paper-'):
            fp = os.path.join(VERSIONS_DIR, f)
            size = os.path.getsize(fp)
            total_bytes += size
            # Extract MC version from filename: paper-1.20.1.jar -> 1.20.1
            mc_version = f.replace('paper-', '').replace('.jar', '')
            versions.append({
                'filename': f,
                'mc_version': mc_version,
                'size': format_size(size),
                'size_bytes': size,
                'type': 'Paper'
            })
    
    return versions, total_bytes


@app.route('/')
def index():
    versions, total_bytes = get_versions()
    versions.reverse()  # newest first
    
    # Get hostname
    hostname = os.environ.get('HOSTNAME', 'localhost')
    port = request.host.split(':')[-1] if ':' in request.host else '5000'
    
    return render_template_string(
        HTML_TEMPLATE,
        versions=versions,
        count=len(versions),
        total_size=format_size(total_bytes),
        base_url=request.host_url.rstrip('/'),
        hostname=hostname
    )


@app.route('/download/<filename>')
def download(filename):
    versions, _ = get_versions()
    filenames = [v['filename'] for v in versions]
    
    if filename not in filenames:
        abort(404, description=f"File {filename} not found")
    
    filepath = os.path.join(VERSIONS_DIR, filename)
    return send_file(
        filepath,
        as_attachment=True,
        download_name=filename
    )


@app.route('/api/versions')
def api_versions():
    versions, total_bytes = get_versions()
    return jsonify({
        'count': len(versions),
        'total_size_bytes': total_bytes,
        'versions': versions
    })


@app.errorhandler(404)
def not_found(e):
    return jsonify({'error': str(e.description)}), 404


if __name__ == '__main__':
    from flask import request
    
    port = int(os.environ.get('PORT', 5000))
    print(f"""
╔══════════════════════════════════════════════════════╗
║         🎮 EasyTeam Download Server                  ║
╠══════════════════════════════════════════════════════╣
║  Local:  http://localhost:{port}                       ║
║                                                      ║
║  📦 {len(get_versions()[0])} Paper versions ready               ║
╚══════════════════════════════════════════════════════╝
""")
    app.run(host='0.0.0.0', port=port, debug=False)
