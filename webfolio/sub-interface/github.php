<section id="git" class="content-section">
    <h2 class="fw-bold mb-4"><i class="bi bi-github me-2"></i>GitHub Overview</h2>

    <style>
    /* ── GitHub Section Styling ─────────────────────────────────── */
    .github-overview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 30px;
    }
    .github-card {
        background: var(--surface-2);
        border: 2px solid var(--brand-dark);
        border-radius: 16px;
        padding: 24px;
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .github-card:hover {
        transform: translateY(-4px);
        border-color: var(--brand-mid);
        box-shadow: 0 8px 24px rgba(94, 42, 42, 0.3);
    }
    .github-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
    }
    .github-header i {
        font-size: 1.2rem;
    }
    .github-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
        gap: 12px;
        margin-top: 16px;
    }
    .stat-item {
        text-align: center;
    }
    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--brand-accent);
        display: block;
    }
    .stat-label {
        font-size: 0.85rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .repo-list {
        margin-top: 16px;
    }
    .repo-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid var(--surface-1);
    }
    .repo-item:last-child {
        border-bottom: none;
    }
    .repo-name {
        font-weight: 600;
        color: var(--text-primary);
    }
    .repo-lang {
        background: var(--brand-accent);
        color: var(--text-primary);
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.8rem;
    }
    .github-history {
        background: var(--surface-2);
        border: 2px solid var(--brand-dark);
        border-radius: 16px;
        padding: 24px;
    }
    .github-history h3 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 16px;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .github-history h3 i {
        font-size: 1.2rem;
    }
    </style>

    <!-- GitHub Overview -->
    <div class="github-overview">
        <!-- Stats Card -->
        <div class="github-card">
            <div class="github-header">
                <i class="bi bi-graph-up"></i>
                <span class="github-title">GitHub Overview</span>
            </div>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-number">12+</span>
                    <span class="stat-label">Repositories</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">5+</span>
                    <span class="stat-label">Languages</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">100+</span>
                    <span class="stat-label">Commits</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">3+</span>
                    <span class="stat-label">Years Active</span>
                </div>
            </div>
        </div>

        <!-- Repositories Card -->
        <div class="github-card">
            <div class="github-header">
                <i class="bi bi-folder2-open"></i>
                <span class="github-title">Featured Repositories</span>
            </div>
            <div class="repo-list">
                <div class="repo-item">
                    <span class="repo-name">RTU_RePo</span>
                    <span class="repo-lang">PHP</span>
                </div>
                <div class="repo-item">
                    <span class="repo-name">Vault-77</span>
                    <span class="repo-lang">Next.js</span>
                </div>
                <div class="repo-item">
                    <span class="repo-name">SPC Project</span>
                    <span class="repo-lang">Laravel</span>
                </div>
            </div>
        </div>
    </div>

    <!-- GitHub History -->
    <div class="github-history">
        <h3><i class="bi bi-github"></i> GitHub History</h3>
        <a href="https://github.com/spidaniel45?tab=repositories" target="_blank" rel="noopener"
           class="d-block text-decoration-none">
            <img src="https://github-readme-stats.vercel.app/api?username=spidaniel45&show_icons=true&theme=dark"
                 alt="GitHub Stats" class="img-fluid rounded-3 mb-3">
            <img src="https://ghchart.rshah.org/2ea44f/spidaniel45"
                 alt="GitHub Contributions" class="githubcontributions img-fluid rounded-3 mb-3">
            <img src="https://github-readme-activity-graph.vercel.app/graph?username=spidaniel45&theme=github-dark"
                 alt="GitHub Activity Graph" class="githubactivitygraph img-fluid rounded-3">
        </a>
        <p style="color: var(--text-primary); opacity: 0.8; margin-top: 1rem;">
            Active contributor with multiple repositories showcasing projects ranging from
            web applications to system utilities. Clean code, detailed documentation,
            and regular updates demonstrate commitment to quality and best practices.
        </p>
    </div>
</section>