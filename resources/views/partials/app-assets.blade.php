<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css" rel="stylesheet">
<style>
    :root {
        --bf-bg: #eef2e8;
        --bf-bg-alt: #dde6d7;
        --bf-surface: rgba(255, 252, 246, 0.88);
        --bf-surface-strong: #fffdf8;
        --bf-ink: #223127;
        --bf-ink-soft: #55685b;
        --bf-line: rgba(57, 86, 63, 0.15);
        --bf-line-strong: rgba(1, 103, 52, 0.2);
        --bf-accent: #016734;
        --bf-accent-strong: #014825;
        --bf-accent-soft: #7db928;
        --bf-danger: #c95741;
        --bf-shadow: 0 20px 50px rgba(25, 40, 29, 0.10);
        --bs-body-font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
        --bs-body-color: var(--bf-ink);
        --bs-body-bg: var(--bf-bg);
        --bs-border-color: var(--bf-line);
        --bs-primary: var(--bf-accent);
        --bs-primary-rgb: 1, 103, 52;
    }

    body.app-shell {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        background:
            radial-gradient(circle at 12% 12%, rgba(125, 185, 40, 0.14) 0%, transparent 34%),
            radial-gradient(circle at 85% 18%, rgba(1, 103, 52, 0.10) 0%, transparent 28%),
            linear-gradient(155deg, var(--bf-bg), var(--bf-bg-alt));
        color: var(--bf-ink);
    }

    .app-navbar {
        background: rgba(255, 252, 246, 0.82);
        backdrop-filter: blur(14px);
        border-bottom: 1px solid var(--bf-line);
        box-shadow: 0 10px 24px rgba(25, 40, 29, 0.06);
    }

    .app-navbar-shell {
        width: min(1540px, calc(100% - 20px));
        margin: 0 auto;
    }

    .app-navbar .navbar-brand {
        font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
        font-weight: 700;
        color: var(--bf-accent-strong);
    }

    .app-navbar .brand-logo {
        height: 38px;
        width: auto;
    }

    .app-navbar .nav-link {
        color: var(--bf-ink);
        font-weight: 600;
        border-radius: 999px;
        padding: 0.5rem 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .app-navbar .nav-link:hover,
    .app-navbar .nav-link:focus-visible,
    .app-navbar .nav-link.active {
        color: var(--bf-accent-strong);
        background: rgba(1, 103, 52, 0.08);
    }

    .app-navbar .navbar-toggler {
        border-color: var(--bf-line-strong);
        box-shadow: none;
    }

    .app-navbar .navbar-text {
        color: var(--bf-ink-soft);
        font-size: 0.95rem;
    }

    .app-toolbar {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
    }

    .app-page {
        width: min(1540px, calc(100% - 20px));
        margin: 0 auto;
        padding: 1.1rem 0 2rem;
    }

    .app-footer {
        margin-top: auto;
        border-top: 1px solid var(--bf-line);
        background: rgba(255, 252, 246, 0.82);
        backdrop-filter: blur(12px);
    }

    .app-footer-shell {
        width: min(1540px, calc(100% - 20px));
        margin: 0 auto;
        padding: 1rem 0 1.2rem;
        display: grid;
        gap: 0.65rem;
    }

    .app-footer-brand {
        display: flex;
        gap: 0.6rem;
        align-items: center;
        flex-wrap: wrap;
        color: var(--bf-ink-soft);
        font-size: 0.95rem;
    }

    .app-footer-links {
        display: flex;
        gap: 0.85rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .app-footer-link {
        color: var(--bf-accent-strong);
        text-decoration: none;
        font-weight: 600;
    }

    .app-footer-link.active,
    .app-footer-link:hover,
    .app-footer-link:focus-visible {
        color: var(--bf-accent);
    }

    .app-footer-copy {
        color: var(--bf-ink-soft);
        font-size: 0.88rem;
    }

    .app-surface,
    .app-card {
        background: var(--bf-surface);
        border: 1px solid var(--bf-line);
        box-shadow: var(--bf-shadow);
        border-radius: 1.25rem;
    }

    .app-card {
        padding: 1.25rem;
    }

    .app-eyebrow {
        font-size: 0.78rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--bf-accent-soft);
        font-weight: 700;
    }

    .app-title {
        font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
        letter-spacing: -0.03em;
    }

    .auth-stage {
        min-height: calc(100vh - 82px);
        display: grid;
        place-items: center;
        padding: 1.5rem 0 2rem;
    }

    .auth-card {
        width: min(520px, 100%);
        margin-inline: auto;
        background: rgba(255, 252, 246, 0.92);
        border: 1px solid var(--bf-line);
        border-radius: 1.5rem;
        box-shadow: 0 24px 60px rgba(25, 40, 29, 0.14);
    }

    .auth-card .card-body {
        padding: 1.6rem;
    }

    .auth-illustration {
        width: 76px;
        max-width: 24vw;
        float: left;
        margin: 0.15rem 0.85rem 0.45rem 0;
    }

    .locale-switcher {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .locale-switcher form {
        margin: 0;
    }

    .locale-switcher .locale-btn {
        width: 34px;
        height: 34px;
        padding: 0;
        border-radius: 999px;
        border: 1px solid var(--bf-line-strong);
        background: rgba(255, 255, 255, 0.82);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 14px rgba(25, 40, 29, 0.08);
    }

    .locale-switcher .locale-btn.active {
        border-color: var(--bf-accent);
        box-shadow: 0 0 0 3px rgba(1, 103, 52, 0.12);
    }

    .locale-switcher .fi {
        width: 18px;
        line-height: 1;
        border-radius: 999px;
        overflow: hidden;
        box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.08);
    }

    .page-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .flash-stack {
        display: grid;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .flash-card {
        border-radius: 1rem;
        border: 1px solid transparent;
        padding: 0.9rem 1rem;
    }

    .flash-card.success {
        background: rgba(125, 185, 40, 0.14);
        border-color: rgba(125, 185, 40, 0.3);
        color: var(--bf-accent-strong);
    }

    .flash-card.error {
        background: rgba(201, 87, 65, 0.10);
        border-color: rgba(201, 87, 65, 0.24);
        color: #8b2d1f;
    }

    @media (max-width: 991.98px) {
        .app-navbar-shell {
            width: min(100%, calc(100% - 16px));
        }

        .app-page {
            width: min(100%, calc(100% - 16px));
        }

        .app-footer-shell {
            width: min(100%, calc(100% - 16px));
        }

        .app-navbar .nav-link {
            border-radius: 0.85rem;
        }
    }
</style>