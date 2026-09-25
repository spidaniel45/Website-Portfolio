<section id="experience" class="content-section">
    <h2 class="fw-bold mb-4"><i class="bi bi-briefcase me-2"></i>Work Experience</h2>

    <style>
    /* ── Experience Timeline ───────────────────────────────── */
    .experience-timeline {
        position: relative;
        padding-left: 30px;
        border-left: 2px solid var(--brand-dark);
        margin-top: 20px;
    }
    .experience-timeline::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        bottom: 0;
        width: 12px;
        background: var(--brand-dark);
        border-radius: 6px;
    }
    .experience-item {
        position: relative;
        margin-bottom: 25px;
        padding-bottom: 15px;
    }
    .experience-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .experience-item::after {
        content: '';
        position: absolute;
        left: -10px;
        top: 8px;
        width: 12px;
        height: 12px;
        background: var(--brand-accent);
        border-radius: 50%;
        border: 2px solid var(--surface-1);
    }
    .experience-header {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 8px;
    }
    .experience-title {
        font-size: 1.1rem;
        font-weight: 600;
        flex: 1;
        min-width: 180px;
    }
    .experience-date {
        font-size: 0.9rem;
        color: var(--text-muted);
        white-space: nowrap;
    }
    .experience-company {
        font-size: 0.95rem;
        font-weight: 500;
        color: var(--text-primary);
        margin: 4px 0;
    }
    .experience-description {
        margin-left: 20px;
        padding-left: 10px;
        border-left: 2px solid var(--surface-2);
    }
    .experience-description ul {
        margin: 8px 0;
        padding-left: 20px;
    }
    .experience-description li {
        margin-bottom: 6px;
        position: relative;
    }
    .experience-description li::before {
        content: "▹";
        position: absolute;
        left: -12px;
        color: var(--brand-accent);
    }
    </style>

    <div class="experience-timeline">

        <!-- Argon Software -->
        <div class="experience-item">
            <div class="experience-header">
                <div class="experience-title">Software Engineer Intern</div>
                <div class="experience-date">November 2025 - August 2026</div>
            </div>
            <div class="experience-company">Argon Software</div>
            <div class="experience-description">
                <ul>
                    <li><strong>Feature Delivery:</strong> Contributed to a Next.js/TypeScript web application, delivering feature fixes and performance improvements across the codebase.</li>
                    <li><strong>Quality Assurance:</strong> Owned end-to-end QA and defect resolution across staging environments to ensure stable, production-ready releases; authored unit tests in Pest PHP for feature-level validation during development.</li>
                    <li><strong>Database Management:</strong> Executed migrations and schema updates for Laravel backends, maintaining strict data integrity across iterative development cycles.</li>
                    <li><strong>Team Collaboration:</strong> Streamlined version-control workflows by resolving complex merge conflicts and actively participating in peer code reviews.</li>
                </ul>
            </div>
        </div>

        <!-- Muzon Elementary School -->
        <div class="experience-item">
            <div class="experience-header">
                <div class="experience-title">Work Immersion Internship</div>
                <div class="experience-date">March 2023</div>
            </div>
            <div class="experience-company">Muzon Elementary School</div>
            <div class="experience-description">
                <ul>
                    <li>Provided IT support and assisted with the maintenance of internal educational technology systems.</li>
                    <li>Developed foundational communication and problem-solving skills within a professional workplace environment.</li>
                </ul>
            </div>
        </div>

    </div>
</section>