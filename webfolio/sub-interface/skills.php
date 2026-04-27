<section id="skills" class="content-section">
    <h2 class="fw-bold mb-4"><i class="bi bi-tools me-2"></i>Skills</h2>

    <style>
    /* ── Skills Grid ─────────────────────────────────────── */
    .skills-grid {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .skill-category {
        background: var(--surface-2);
        border: 2px solid var(--brand-dark);
        border-radius: 12px;
        padding: 16px 18px 18px;
        transition: border-color 0.3s ease;
    }
    .skill-category:hover {
        border-color: var(--brand-mid);
    }

    .skill-category-label {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--brand-accent);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .skill-category-label i {
        font-size: 0.8rem;
        opacity: 0.85;
    }

    /* Logo badge */
    .badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .tech-badge {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        background: var(--surface-1);
        border: 2px solid transparent;
        border-radius: 10px;
        padding: 10px 14px 8px;
        cursor: default;
        transition: border-color 0.2s ease, transform 0.2s ease, background 0.2s ease;
        min-width: 68px;
    }
    .tech-badge:hover {
        border-color: var(--brand-accent);
        background: rgba(255, 107, 107, 0.07);
        transform: translateY(-3px);
    }
    .tech-badge i {
        font-size: 1.9rem;
        line-height: 1;
    }
    .tech-badge span {
        font-size: 0.62rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        color: var(--text-muted);
        text-align: center;
        white-space: nowrap;
    }
    .tech-badge:hover span {
        color: var(--text-primary);
    }

    /* AI tool badges — image logos */
    .tech-badge .ai-logo {
        width: 30px;
        height: 30px;
        object-fit: contain;
        border-radius: 6px;
    }
    </style>

    <div class="skills-grid">

        <!-- Front-End -->
        <div class="skill-category">
            <div class="skill-category-label">
                <i class="bi bi-layout-text-window-reverse"></i> Front-End
            </div>
            <div class="badge-row">
                <div class="tech-badge">
                    <i class="devicon-html5-plain colored"></i>
                    <span>HTML5</span>
                </div>
                <div class="tech-badge">
                    <i class="devicon-css3-plain colored"></i>
                    <span>CSS3</span>
                </div>
                <div class="tech-badge">
                    <i class="devicon-javascript-plain colored"></i>
                    <span>JavaScript</span>
                </div>
                <div class="tech-badge">
                    <i class="devicon-bootstrap-plain colored"></i>
                    <span>Bootstrap</span>
                </div>
                <div class="tech-badge">
                    <i class="devicon-react-original colored"></i>
                    <span>React</span>
                </div>
                <div class="tech-badge">
                    <i class="devicon-tailwindcss-plain colored"></i>
                    <span>Tailwind</span>
                </div>
            </div>
        </div>

        <!-- Back-End -->
        <div class="skill-category">
            <div class="skill-category-label">
                <i class="bi bi-server"></i> Back-End
            </div>
            <div class="badge-row">
                <div class="tech-badge">
                    <i class="devicon-php-plain colored"></i>
                    <span>PHP</span>
                </div>
                <div class="tech-badge">
                    <i class="devicon-laravel-plain colored"></i>
                    <span>Laravel</span>
                </div>
                <div class="tech-badge">
                    <i class="devicon-java-plain colored"></i>
                    <span>Java</span>
                </div>
                <div class="tech-badge">
                    <i class="devicon-python-plain colored"></i>
                    <span>Python</span>
                </div>
            </div>
        </div>

        <!-- Database -->
        <div class="skill-category">
            <div class="skill-category-label">
                <i class="bi bi-database"></i> Database
            </div>
            <div class="badge-row">
                <div class="tech-badge">
                    <i class="devicon-mysql-plain colored"></i>
                    <span>MySQL</span>
                </div>
            </div>
        </div>

        <!-- Version Control -->
        <div class="skill-category">
            <div class="skill-category-label">
                <i class="bi bi-git"></i> Version Control
            </div>
            <div class="badge-row">
                <div class="tech-badge">
                    <i class="devicon-git-plain colored"></i>
                    <span>Git</span>
                </div>
                <div class="tech-badge">
                    <i class="devicon-github-original" style="color:#ffffff;"></i>
                    <span>GitHub</span>
                </div>
            </div>
        </div>

        <!-- Dev Tools -->
        <div class="skill-category">
            <div class="skill-category-label">
                <i class="bi bi-terminal"></i> Dev Tools
            </div>
            <div class="badge-row">
                <div class="tech-badge">
                    <i class="devicon-vscode-plain colored"></i>
                    <span>VS Code</span>
                </div>
                <div class="tech-badge">
                    <i class="devicon-xampp-plain colored"></i>
                    <span>XAMPP</span>
                </div>
            </div>
        </div>

        <!-- AI Tools -->
        <div class="skill-category">
            <div class="skill-category-label">
                <i class="bi bi-stars"></i> AI Tools
            </div>
            <div class="badge-row">

                <!-- Claude -->
                <div class="tech-badge">
                    <img class="ai-logo"
                         src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Claude_AI_logo.svg/512px-Claude_AI_logo.svg.png"
                         alt="Claude"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='block'"
                    ><i class="bi bi-robot" style="display:none;font-size:1.9rem;color:#d97757;"></i>
                    <span>Claude</span>
                </div>

                <!-- Cursor -->
                <div class="tech-badge">
                    <img class="ai-logo"
                         src="https://www.cursor.com/favicon.ico"
                         alt="Cursor"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='block'"
                    ><i class="bi bi-cursor-fill" style="display:none;font-size:1.9rem;color:#ffffff;"></i>
                    <span>Cursor</span>
                </div>

                <!-- GitHub Copilot -->
                <div class="tech-badge">
                    <i class="devicon-githubcopilot-plain" style="color:#ffffff;font-size:1.9rem;"></i>
                    <span>Copilot</span>
                </div>

                <!-- ChatGPT -->
                <div class="tech-badge">
                    <img class="ai-logo"
                         src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/04/ChatGPT_logo.svg/512px-ChatGPT_logo.svg.png"
                         alt="ChatGPT"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='block'"
                    ><i class="bi bi-chat-dots-fill" style="display:none;font-size:1.9rem;color:#10a37f;"></i>
                    <span>ChatGPT</span>
                </div>

            </div>
        </div>

    </div>
</section>