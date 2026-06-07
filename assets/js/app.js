/**
 * EasyTeam Panel v2 - SPA Engine
 * Single Page Application with history.pushState navigation
 * No full page reloads - Pterodactyl/PufferPanel style
 */

(function() {
    'use strict';

    const App = {
        currentPage: 'dashboard',
        isLoading: false,
        contentEl: null,
        sidebarEl: null,
        titleEl: null,
        consoleInterval: null,
        activeIntervals: [],
        
        init() {
            this.contentEl = document.getElementById('appContent');
            this.sidebarEl = document.getElementById('sidebar');
            this.titleEl = document.getElementById('pageTitle');
            
            this.bindNavigation();
            this.bindSidebar();
            this.startClock();
            this.initTooltips();
            this.initTheme();
            this.initServerRefresh();
            
            // Handle browser back/forward
            window.addEventListener('popstate', (e) => {
                if (e.state && e.state.page) {
                    this.loadPage(e.state.page, false);
                }
            });
            
            // Initial page from data attribute
            const initialPage = document.body.dataset.page || 'dashboard';
            this.currentPage = initialPage;
            this.highlightNav(initialPage);
        },
        
        bindNavigation() {
            // Intercept ALL internal navigation links (no need for data-nav)
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a');
                if (!link) return;
                
                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('http') || href.startsWith('javascript:')) return;
                
                // Skip logout, language switcher, download links, external
                if (link.classList.contains('lang-btn') || 
                    link.getAttribute('download') ||
                    href.includes('action=logout') ||
                    href.includes('page=api')) return;
                
                const page = this.getPageFromHref(href);
                
                if (page) {
                    e.preventDefault();
                    this.loadPage(page, true, href);
                }
            });
            
            // Intercept form submissions for SPA (skip login/register for speed and proper layout)
            document.addEventListener('submit', (e) => {
                const form = e.target;
                if (!form || !form.action || form.enctype === 'multipart/form-data') return;
                
                const href = form.action;
                const page = this.getPageFromHref(href);
                
                // Skip login/register forms - full page load is faster and ensures correct layout
                if (page === 'login' || page === 'register') return;
                
                // Only intercept forms that target panel pages
                if (page && !href.includes('action=logout')) {
                    e.preventDefault();
                    this.submitForm(form);
                }
            });
        },
        
        getPageFromHref(href) {
            if (!href || href.startsWith('http') || href.startsWith('#')) return null;
            
            const url = new URL(href, window.location.origin);
            const page = url.searchParams.get('page');
            const pageMap = {
                'dashboard': 'dashboard',
                'servers': 'servers',
                'server-detail': 'server-detail',
                'console': 'console',
                'files': 'files',
                'versions': 'versions',
                'users': 'users',
                'settings': 'settings',
                'login': 'login',
                'register': 'register',
            };
            
            return pageMap[page] || null;
        },
        
        loadPage(page, pushState = true, href = null) {
            if (this.isLoading || page === this.currentPage) return;
            
            this.isLoading = true;
            this.showLoading();
            
            const url = href || `index.php?page=${page}&ajax=1`;
            const ajaxUrl = url.includes('?') ? url + '&ajax=1' : url + '?ajax=1';
            
            // Build URL for pushState (without ajax param)
            const stateUrl = href || `index.php?page=${page}`;
            
            fetch(ajaxUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html, */*'
                }
            })
            .then(r => {
                if (r.status === 302 || r.status === 301) {
                    // Redirect - handle it
                    const location = r.headers.get('Location');
                    if (location) {
                        const redirectPage = this.getPageFromHref(location);
                        if (redirectPage) {
                            return this.loadPage(redirectPage, pushState, location);
                        }
                        window.location.href = location;
                    }
                    throw new Error('Redirect');
                }
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.text();
            })
            .then(html => {
                this.renderContent(html, page, stateUrl, pushState);
            })
            .catch(err => {
                if (err.message !== 'Redirect') {
                    console.error('SPA Error:', err);
                    this.contentEl.innerHTML = `<div class="error-page">
                        <svg class="icon icon-64"><use href="assets/icons/sprite.svg#icon-error"/></svg>
                        <h3>خطا در بارگذاری صفحه</h3>
                        <p>${err.message}</p>
                        <button onclick="window.location.reload()" class="btn btn-primary mt-4">
                            <svg class="icon"><use href="assets/icons/sprite.svg#icon-refresh"/></svg> بارگذاری مجدد
                        </button>
                    </div>`;
                }
            })
            .finally(() => {
                this.isLoading = false;
                this.hideLoading();
                this.initTooltips();
            });
        },
        
        renderContent(html, page, stateUrl, pushState) {
            // Stop any running page-specific intervals before switching
            this.stopPageScripts();
            
            // Fade out
            this.contentEl.style.opacity = '0';
            this.contentEl.style.transform = 'translateY(8px)';
            
            setTimeout(() => {
                this.contentEl.innerHTML = html;
                this.currentPage = page;
                
                // Update title
                const titleMatch = html.match(/<title[^>]*>([^<]+)<\/title>/i);
                if (titleMatch) {
                    document.title = titleMatch[1];
                }
                
                // Update page title in topbar
                const h1 = this.contentEl.querySelector('h2');
                if (h1 && this.titleEl) {
                    this.titleEl.textContent = h1.textContent;
                }
                
                // Update URL
                if (pushState) {
                    window.history.pushState({ page }, '', stateUrl);
                }
                
                // Highlight active nav
                this.highlightNav(page);
                
                // Re-init page-specific scripts
                this.initPageScripts(page);
                
                // Fade in
                this.contentEl.style.opacity = '';
                this.contentEl.style.transform = '';
                
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                // Close sidebar on mobile
                if (window.innerWidth <= 768) {
                    this.sidebarEl?.classList.remove('open');
                }
                
                // Re-bind loading spinners for forms
                this.bindFormLoading();
            }, 150);
        },
        
        highlightNav(page) {
            document.querySelectorAll('.nav-item').forEach(el => {
                el.classList.remove('active');
                const href = el.getAttribute('href') || '';
                if (href.includes(`page=${page}`) || href.includes(`page=${this.getNavPage(page)}`)) {
                    el.classList.add('active');
                }
            });
        },
        
        getNavPage(page) {
            const map = {
                'server-detail': 'servers',
                'console': 'servers',
                'files': 'servers',
            };
            return map[page] || page;
        },
        
        showLoading() {
            let loader = document.getElementById('appLoader');
            if (!loader) {
                loader = document.createElement('div');
                loader.id = 'appLoader';
                loader.className = 'app-loader';
                loader.innerHTML = '<svg class="icon spin"><use href="assets/icons/sprite.svg#icon-loading"/></svg>';
                document.getElementById('mainContent')?.appendChild(loader);
            }
            loader.classList.add('visible');
        },
        
        hideLoading() {
            const loader = document.getElementById('appLoader');
            if (loader) loader.classList.remove('visible');
        },
        
        submitForm(form) {
            const formData = new FormData(form);
            const action = form.action || window.location.href;
            
            this.showLoading();
            
            fetch(action, {
                method: 'POST',
                body: new URLSearchParams(formData),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => {
                const location = r.headers.get('Location');
                if (location) {
                    const page = this.getPageFromHref(location);
                    if (page) {
                        return this.loadPage(page, true, location);
                    }
                    window.location.href = location;
                    return;
                }
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.text();
            })
            .then(html => {
                if (html) {
                    // Refresh current page with response
                    this.renderContent(html, this.currentPage, window.location.href, false);
                }
            })
            .catch(err => {
                console.error('Form Error:', err);
                window.location.reload();
            })
            .finally(() => this.hideLoading());
        },
        
        startClock() {
            const update = () => {
                const el = document.getElementById('serverTime');
                if (el) {
                    const now = new Date();
                    el.textContent = now.toLocaleTimeString('fa-IR', {
                        hour: '2-digit', minute: '2-digit', second: '2-digit'
                    });
                }
            };
            update();
            setInterval(update, 1000);
        },
        
        initTooltips() {
            // Auto-hide alerts
            document.querySelectorAll('.alert').forEach(el => {
                if (!el.dataset.timer) {
                    el.dataset.timer = '1';
                    setTimeout(() => {
                        el.style.opacity = '0';
                        setTimeout(() => el.remove(), 300);
                    }, 4000);
                }
            });
        },
        
        stopPageScripts() {
            // Clear all active intervals
            this.activeIntervals.forEach(id => clearInterval(id));
            this.activeIntervals = [];
            this.consoleInterval = null;
        },
        
        initPageScripts(page) {
            if (page === 'console') this.startConsole();
            if (page === 'files') this.initFileManager();
        },
        
        startConsole() {
            const container = this.contentEl?.querySelector('.console-page');
            if (!container || this.consoleInterval) return;
            
            const serverId = container.dataset.serverId;
            if (!serverId) return;
            
            // ---- Console Form Submission ----
            const form = document.getElementById('consoleForm');
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    const formData = new FormData(form);
                    formData.append('action', 'console_command');
                    formData.append('server_id', serverId);
                    
                    fetch('index.php?page=api&ajax=1', {
                        method: 'POST',
                        body: new URLSearchParams(formData)
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            form.querySelector('input[name="command"]').value = '';
                        }
                    })
                    .catch(err => console.error('Console cmd error:', err));
                });
            }
            
            // ---- Console Output Polling ----
            const output = document.getElementById('consoleOutput');
            const statusBadge = document.getElementById('consoleStatus');
            const cmdInput = document.getElementById('commandInput');
            const autoScroll = document.getElementById('autoScroll');
            
            const poll = () => {
                fetch('index.php?page=api&action=console_output&server_id=' + serverId + '&ajax=1')
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.output && output) {
                            const joined = data.output.join('\n');
                            if (joined !== output.getAttribute('data-last')) {
                                output.setAttribute('data-last', joined);
                                output.innerHTML = data.output.map(line => 
                                    '<div class="console-line">' + this.escapeHtml(line) + '</div>'
                                ).join('');
                                if (autoScroll && autoScroll.checked) {
                                    output.scrollTop = output.scrollHeight;
                                }
                            }
                        }
                        // Update status
                        if (data.server_status && statusBadge) {
                            statusBadge.className = 'status-badge status-' + data.server_status;
                            // Preserve language - read the initial label text once
                            const lang = document.body.dataset.lang || 'fa';
                            statusBadge.textContent = data.server_status === 'online' 
                                ? (lang === 'fa' ? 'آنلاین' : 'Online')
                                : (lang === 'fa' ? 'آفلاین' : 'Offline');
                            if (cmdInput) {
                                cmdInput.disabled = data.server_status !== 'online';
                                const btn = cmdInput.nextElementSibling;
                                if (btn) btn.disabled = data.server_status !== 'online';
                            }
                        }
                    })
                    .catch(err => console.error('Console poll error:', err));
            };
            
            // Start polling
            const id = setInterval(poll, 2000);
            this.activeIntervals.push(id);
            this.consoleInterval = id;
            
            // ---- Clear Console Button ----
            const clearBtn = document.getElementById('clearConsoleBtn');
            if (clearBtn && output) {
                clearBtn.addEventListener('click', () => {
                    output.innerHTML = '<div class="console-empty">' + 
                        (container.querySelector('.console-empty')?.textContent || 'No output') + 
                        '</div>';
                    output.setAttribute('data-last', '');
                });
            }
        },
        
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        initFileManager() {
            const scripts = this.contentEl?.querySelectorAll('script');
            scripts?.forEach(script => {
                if (script.textContent.includes('showCreateModal') || script.textContent.includes('deleteItem')) {
                    const newScript = document.createElement('script');
                    newScript.textContent = script.textContent;
                    script.parentNode.replaceChild(newScript, script);
                }
            });
        },
        
        bindFormLoading() {
            // Auto-disable submit buttons on all forms to prevent double-submit
            this.contentEl?.querySelectorAll('form').forEach(form => {
                if (form.dataset.boundLoading) return;
                form.dataset.boundLoading = '1';
                form.addEventListener('submit', function(e) {
                    var btn = this.querySelector('button[type="submit"].btn-primary');
                    if (btn) {
                        setTimeout(function() {
                            btn.disabled = true;
                            var spinner = btn.querySelector('[class*="spin"]');
                            var icon = btn.querySelector('.icon:not([class*="spin"])');
                            if (spinner) spinner.style.display = 'inline-block';
                            if (icon && !btn.querySelector('[class*="spin"]')) {
                                var spin = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                                spin.setAttribute('class', 'icon spin');
                                spin.innerHTML = '<use href="assets/icons/sprite.svg#icon-loading"/>';
                                btn.insertBefore(spin, icon);
                            }
                        }, 10);
                    }
                });
            });
        },
        
        bindSidebar() {
            const toggle = document.getElementById('sidebarToggle');
            if (toggle) {
                toggle.addEventListener('click', () => {
                    this.sidebarEl?.classList.toggle('open');
                });
                
                document.addEventListener('click', (e) => {
                    if (window.innerWidth <= 768 && 
                        this.sidebarEl?.classList.contains('open') &&
                        !this.sidebarEl.contains(e.target) && 
                        e.target !== toggle) {
                        this.sidebarEl.classList.remove('open');
                    }
                });
            }
        },
        
        initTheme() {
            const toggle = document.getElementById('themeToggle');
            const icon = document.getElementById('themeIcon');
            
            // Load saved theme
            const saved = localStorage.getItem('easyteam_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', saved);
            if (icon) {
                icon.querySelector('use').setAttribute('href', 
                    'assets/icons/sprite.svg#icon-' + (saved === 'dark' ? 'paper' : 'memory'));
            }
            
            if (toggle) {
                toggle.addEventListener('click', () => {
                    const current = document.documentElement.getAttribute('data-theme');
                    const next = current === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', next);
                    localStorage.setItem('easyteam_theme', next);
                    if (icon) {
                        icon.querySelector('use').setAttribute('href', 
                            'assets/icons/sprite.svg#icon-' + (next === 'dark' ? 'paper' : 'memory'));
                    }
                });
            }
        },
        
        initServerRefresh() {
            // Auto-refresh: status badges will be updated on page navigation
            // Console output polling is handled separately in startConsole()
        }
    };

    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => App.init());
    } else {
        App.init();
    }

    // Global helper functions
    window.toggleSidebar = () => document.getElementById('sidebar')?.classList.toggle('open');
    window.navigateTo = (page, href) => App.loadPage(page, true, href);
    
})();

// ===== Global: Language Switcher (preserves current URL) =====
function switchLanguage(lang) {
    // Extract current page params to preserve context
    var currentHref = window.location.href;
    var match = currentHref.match(/[?&]page=([a-z-]+)/);
    var page = match ? match[1] : 'dashboard';
    // Preserve extra params like id=5
    var extraParams = '';
    var paramsMatch = currentHref.match(/[?&](id|user_id|path)=([^&]+)/g);
    if (paramsMatch) {
        extraParams = '&' + paramsMatch.map(function(p) { return p.replace(/^[?&]/, ''); }).join('&');
    }
    window.location.href = 'index.php?page=api&action=set_language&lang=' + lang + '&redirect=' + encodeURIComponent(page + extraParams);
}

// ===== Global: CuteCaptcha - Select captcha option =====
function selectCaptcha(btn) {
    var options = btn.parentElement.querySelectorAll('.captcha-option');
    options.forEach(function(opt) { opt.classList.remove('selected'); });
    btn.classList.add('selected');
    
    var answer = document.getElementById('captchaAnswer');
    if (answer) {
        answer.value = btn.getAttribute('data-value');
    }
    
    // Show feedback
    var feedback = document.getElementById('captchaFeedback');
    var text = document.getElementById('captchaFeedbackText');
    if (feedback && text) {
        feedback.style.display = 'flex';
        var lang = document.body.dataset.lang || 'fa';
        feedback.className = 'captcha-feedback captcha-success';
        text.textContent = lang === 'fa' ? '✓ تأیید شد' : '✓ Verified';
        
        var captcha = document.getElementById('cuteCaptcha');
        if (captcha) captcha.classList.add('captcha-solved');
    }
}

