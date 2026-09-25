<section id="home" class="content-section">
    <h2 class="fw-bold mb-4"><i class="bi bi-house-door me-2"></i>Home</h2>

    <style>
    /* ── Home Highlights Grid ────────────────────────────────── */
    .home-highlights {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 30px;
    }
    .highlight-card {
        background: var(--surface-2);
        border: 2px solid var(--brand-dark);
        border-radius: 16px;
        padding: 24px;
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .highlight-card:hover {
        transform: translateY(-4px);
        border-color: var(--brand-mid);
        box-shadow: 0 8px 24px rgba(94, 42, 42, 0.3);
    }
    .highlight-icon {
        width: 48px;
        height: 48px;
        background: var(--brand-accent);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
    }
    .highlight-icon i {
        font-size: 1.5rem;
        color: var(--text-primary);
    }
    .highlight-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 12px;
        color: var(--text-primary);
    }
    .highlight-text {
        font-size: 0.95rem;
        color: var(--text-muted);
        line-height: 1.6;
    }
    .highlight-divider {
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--brand-accent), transparent);
        margin: 20px 0;
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
    /* ── GitHub Preview ──────────────────────────────────────── */
    .github-preview {
        background: var(--surface-2);
        border: 2px solid var(--brand-dark);
        border-radius: 16px;
        padding: 24px;
    }
    .github-preview h3 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 16px;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .github-preview h3 i {
        font-size: 1.2rem;
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
    /* ── Experience Preview ──────────────────────────────────── */
    .experience-preview {
        background: var(--surface-2);
        border: 2px solid var(--brand-dark);
        border-radius: 16px;
        padding: 24px;
    }
    .experience-preview h3 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 16px;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .experience-preview h3 i {
        font-size: 1.2rem;
    }
    .exp-item {
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--surface-1);
    }
    .exp-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    .exp-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 4px;
        color: var(--text-primary);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .exp-company {
        font-weight: 500;
    }
    .exp-period {
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    .exp-description {
        font-size: 0.9rem;
        color: var(--text-muted);
        line-height: 1.6;
    }
    </style>

    <!-- Welcome Message -->
    <div class="mb-5">
        <h1 class="display-5 fw-bold">Welcome to My Portfolio</h1>
        <p class="lead fs-5">
            Hi! I'm Daniel, a passionate Software Engineer with expertise in full-stack development.
            Explore my work, skills, and journey below.
        </p>
    </div>

    <!-- Highlights Grid -->
    <div class="home-highlights">
        <!-- Skills Highlight -->
        <div class="highlight-card">
            <div class="highlight-icon">
                <i class="bi bi-diagram-3"></i>
            </div>
            <div class="highlight-title">Technical Skills</div>
            <div class="highlight-text">
                Proficient in modern full-stack technologies including PHP, Laravel, JavaScript,
                TypeScript, React, Vue.js, and more. Strong foundation in databases,
                version control, and development tools.
            </div>
        </div>

        <!-- GitHub Highlight -->
        <div class="highlight-card">
            <div class="highlight-icon">
                <i class="bi bi-github"></i>
            </div>
            <div class="highlight-title">GitHub Activity</div>
            <div class="highlight-text">
                Active contributor with multiple repositories showcasing projects ranging from
                web applications to system utilities. Clean code, detailed documentation,
                and regular updates.
            </div>
        </div>

        <!-- Projects Highlight -->
        <div class="highlight-card">
            <div class="highlight-icon">
                <i class="bi bi-folder2-open"></i>
            </div>
            <div class="highlight-title">Featured Projects</div>
            <div class="highlight-text">
                Selected projects demonstrating practical problem-solving skills,
                ranging from academic repositories to professional web applications.
                Each project showcases different aspects of full-stack development.
            </div>
        </div>

        <!-- Experience Highlight -->
        <div class="highlight-card">
            <div class="highlight-icon">
                <i class="bi bi-briefcase"></i>
            </div>
            <div class="highlight-title">Experience Preview</div>
            <div class="highlight-text">
                Software Engineer Intern at Argon Software with Next.js/TypeScript development,
                QA workflow ownership, and Laravel database migrations. Plus work immersion
                internship at Muzon Elementary School developing IT support and problem-solving skills.
            </div>
        </div>
    </div>

    </section>