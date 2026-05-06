<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FoodSave — Zéro Gaspillage, Maximum Impact</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,700;0,900;1,400;1,700&display=swap" rel="stylesheet">
  <style>
    :root {
      --g1: #0D2B1F;
      --g2: #1B4332;
      --g3: #2D6A4F;
      --g4: #40916C;
      --g5: #52B788;
      --g6: #74C69D;
      --cream: #FAF8F3;
      --cream2: #F2EFE6;
      --orange: #E07B39;
      --orange2: #F5A263;
      --gold: #D4A843;
      --white: #fff;
      --text: #0F1A14;
      --text2: #2C3E30;
      --muted: #6B8070;
      --border: #DDD8CC;
      --font: 'Outfit', sans-serif;
      --serif: 'Playfair Display', serif;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }

    body {
      font-family: var(--font);
      background: var(--cream);
      color: var(--text);
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
    }

    /* ══ NAVBAR ══ */
    nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      display: flex; align-items: center; justify-content: space-between;
      padding: 18px 60px;
      background: rgba(250,248,243,0.82);
      backdrop-filter: blur(18px);
      border-bottom: 1px solid rgba(0,0,0,0.06);
      transition: padding 0.3s;
    }
    .nav-logo {
      display: flex; align-items: center; gap: 12px;
    }
    .nav-logo-icon {
      width: 40px; height: 40px;
      background: linear-gradient(135deg, var(--g2), var(--g4));
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
      box-shadow: 0 4px 14px rgba(45,106,79,0.3);
    }
    .nav-logo-text {
      font-size: 1.25rem; font-weight: 800; letter-spacing: -0.02em; color: var(--g1);
    }
    .nav-links {
      display: flex; align-items: center; gap: 36px; list-style: none;
    }
    .nav-links a {
      text-decoration: none; color: var(--text2); font-size: 0.9rem; font-weight: 500;
      transition: color 0.2s; position: relative;
    }
    .nav-links a::after {
      content: ''; position: absolute; bottom: -3px; left: 0; width: 0; height: 2px;
      background: var(--g4); transition: width 0.25s;
    }
    .nav-links a:hover { color: var(--g3); }
    .nav-links a:hover::after { width: 100%; }
    .nav-cta {
      display: flex; gap: 10px;
    }
    .btn-ghost {
      padding: 9px 20px; border: 1.5px solid var(--border); border-radius: 100px;
      background: transparent; font-family: var(--font); font-size: 0.85rem; font-weight: 600;
      color: var(--text2); cursor: pointer; transition: all 0.2s;
    }
    .btn-ghost:hover { border-color: var(--g4); color: var(--g3); background: rgba(64,145,108,0.05); }
    .btn-solid {
      padding: 9px 22px; border: none; border-radius: 100px;
      background: linear-gradient(135deg, var(--g2), var(--g4));
      font-family: var(--font); font-size: 0.85rem; font-weight: 700;
      color: white; cursor: pointer;
      box-shadow: 0 4px 14px rgba(45,106,79,0.3);
      transition: transform 0.15s, box-shadow 0.15s;
    }
    .btn-solid:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(45,106,79,0.4); }

    /* ══ HERO ══ */
    .hero {
      min-height: 100vh;
      display: flex; flex-direction: column; justify-content: center;
      padding: 120px 60px 80px;
      position: relative; overflow: hidden;
    }
    .hero-bg {
      position: absolute; inset: 0; z-index: 0;
      background: linear-gradient(145deg, #0D2B1F 0%, #1B4332 40%, #2D6A4F 70%, #40916C 100%);
    }
    .hero-bg::before {
      content: '';
      position: absolute; inset: 0;
      background-image:
        radial-gradient(ellipse 50% 60% at 80% 50%, rgba(116,198,157,0.15) 0%, transparent 60%),
        radial-gradient(ellipse 30% 40% at 15% 80%, rgba(224,123,57,0.12) 0%, transparent 50%),
        radial-gradient(ellipse 40% 30% at 50% 10%, rgba(82,183,136,0.1) 0%, transparent 50%);
    }
    /* Grid lines */
    .hero-bg::after {
      content: '';
      position: absolute; inset: 0;
      background-image: linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
      background-size: 60px 60px;
    }

    /* Floating food shapes */
    .food-float {
      position: absolute; z-index: 1; pointer-events: none;
      font-size: 3rem; opacity: 0.12;
      animation: float-anim linear infinite;
    }
    @keyframes float-anim {
      0%   { transform: translateY(0) rotate(0deg); }
      50%  { transform: translateY(-20px) rotate(8deg); }
      100% { transform: translateY(0) rotate(0deg); }
    }

    .hero-content { position: relative; z-index: 2; max-width: 800px; }

    .hero-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);
      border-radius: 100px; padding: 7px 18px;
      font-size: 0.78rem; color: rgba(255,255,255,0.75);
      font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase;
      margin-bottom: 28px;
      animation: fade-up 0.8s ease both;
    }
    .badge-dot {
      width: 7px; height: 7px; background: #52d68a; border-radius: 50%;
      animation: blink 2s ease-in-out infinite;
    }
    @keyframes blink { 0%,100%{opacity:1;} 50%{opacity:0.4;} }

    .hero-title {
      font-family: var(--serif);
      font-size: clamp(3.2rem, 6vw, 5.5rem);
      font-weight: 900; line-height: 1.04; letter-spacing: -0.02em;
      color: white; margin-bottom: 24px;
      animation: fade-up 0.8s 0.1s ease both;
    }
    .hero-title em {
      font-style: italic; color: var(--g6);
    }
    .hero-title span {
      position: relative;
    }
    .hero-title span::after {
      content: '';
      position: absolute; bottom: 4px; left: 0; right: 0;
      height: 3px; background: var(--orange2);
      border-radius: 2px;
    }

    .hero-desc {
      font-size: 1.15rem; line-height: 1.7; color: rgba(255,255,255,0.6);
      max-width: 560px; font-weight: 400; margin-bottom: 42px;
      animation: fade-up 0.8s 0.2s ease both;
    }

    .hero-actions {
      display: flex; gap: 14px; flex-wrap: wrap;
      animation: fade-up 0.8s 0.3s ease both;
    }
    .btn-hero-primary {
      padding: 16px 36px; border: none; border-radius: 100px;
      background: linear-gradient(135deg, var(--orange), var(--orange2));
      font-family: var(--font); font-size: 1rem; font-weight: 700; color: white;
      cursor: pointer; letter-spacing: 0.01em;
      box-shadow: 0 6px 28px rgba(224,123,57,0.5);
      transition: transform 0.15s, box-shadow 0.15s;
      display: flex; align-items: center; gap: 10px;
    }
    .btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 36px rgba(224,123,57,0.55); }
    .btn-hero-secondary {
      padding: 16px 36px; border: 2px solid rgba(255,255,255,0.2);
      border-radius: 100px; background: transparent;
      font-family: var(--font); font-size: 1rem; font-weight: 600; color: rgba(255,255,255,0.85);
      cursor: pointer; transition: all 0.2s;
      display: flex; align-items: center; gap: 10px;
    }
    .btn-hero-secondary:hover { background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.35); }

    /* Scroll indicator */
    .scroll-hint {
      position: absolute; bottom: 40px; left: 60px; z-index: 2;
      display: flex; align-items: center; gap: 10px;
      color: rgba(255,255,255,0.35); font-size: 0.75rem; font-weight: 500;
      text-transform: uppercase; letter-spacing: 0.1em;
      animation: fade-up 1s 0.6s ease both;
    }
    .scroll-line {
      width: 40px; height: 1px; background: rgba(255,255,255,0.25);
      animation: pulse-line 2s ease-in-out infinite;
    }
    @keyframes pulse-line { 0%,100%{width:40px;} 50%{width:20px;} }

    /* Hero stats strip */
    .hero-stats {
      position: absolute; bottom: 0; right: 0; z-index: 2;
      display: flex;
      background: rgba(0,0,0,0.25);
      backdrop-filter: blur(16px);
      border-top-left-radius: 20px;
      border-top: 1px solid rgba(255,255,255,0.08);
      border-left: 1px solid rgba(255,255,255,0.08);
      overflow: hidden;
    }
    .hero-stat-item {
      padding: 20px 32px;
      border-right: 1px solid rgba(255,255,255,0.07);
      text-align: center;
    }
    .hero-stat-item:last-child { border-right: none; }
    .hsi-num {
      font-family: var(--serif); font-size: 2rem; font-weight: 700;
      color: white; letter-spacing: -0.02em; line-height: 1;
    }
    .hsi-label { font-size: 0.7rem; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.08em; margin-top: 4px; }

    @keyframes fade-up {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ══ SECTIONS COMMON ══ */
    section { padding: 100px 60px; }
    .section-label {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--green-light, #D8F3DC);
      background: rgba(82,183,136,0.12);
      border: 1px solid rgba(82,183,136,0.25);
      border-radius: 100px; padding: 6px 16px;
      font-size: 0.72rem; font-weight: 700; color: var(--g3);
      text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 16px;
    }
    .section-title {
      font-family: var(--serif); font-size: clamp(2rem, 3.5vw, 3rem);
      font-weight: 700; letter-spacing: -0.02em; line-height: 1.15;
      color: var(--text); margin-bottom: 16px;
    }
    .section-title em { font-style: italic; color: var(--g4); }
    .section-sub {
      color: var(--muted); font-size: 1rem; line-height: 1.65; max-width: 520px;
    }

    /* ══ IMPACT SECTION ══ */
    .impact {
      background: var(--cream);
    }
    .impact-grid {
      display: grid; grid-template-columns: 1fr 2fr; gap: 80px; align-items: center; margin-top: 60px;
    }
    .impact-kpis {
      display: flex; flex-direction: column; gap: 20px;
    }
    .kpi-card {
      background: var(--white); border: 1px solid var(--border);
      border-radius: 20px; padding: 24px 28px;
      display: flex; align-items: center; gap: 18px;
      transition: transform 0.2s, box-shadow 0.2s;
      animation: fade-up 0.6s ease both;
    }
    .kpi-card:hover { transform: translateX(6px); box-shadow: 0 8px 28px rgba(45,106,79,0.1); }
    .kpi-icon {
      width: 52px; height: 52px; border-radius: 14px;
      display: flex; align-items: center; justify-content: center; font-size: 22px;
      flex-shrink: 0;
    }
    .kpi-icon.green { background: linear-gradient(135deg, #D8F3DC, #B7E4C7); }
    .kpi-icon.orange { background: linear-gradient(135deg, #FDE8D8, #FBBF99); }
    .kpi-icon.blue { background: linear-gradient(135deg, #DBEAFE, #BFDBFE); }
    .kpi-num {
      font-family: var(--serif); font-size: 2rem; font-weight: 700;
      letter-spacing: -0.03em; color: var(--text); line-height: 1;
    }
    .kpi-label { font-size: 0.8rem; color: var(--muted); margin-top: 3px; }

    /* Impact visual */
    .impact-visual {
      position: relative;
    }
    .impact-map {
      background: linear-gradient(145deg, var(--g1) 0%, var(--g2) 50%, var(--g3) 100%);
      border-radius: 28px; padding: 48px 40px;
      overflow: hidden; position: relative;
    }
    .impact-map::before {
      content: '';
      position: absolute; inset: 0;
      background-image: radial-gradient(circle at 30% 60%, rgba(82,183,136,0.2) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(224,123,57,0.1) 0%, transparent 40%);
    }
    .impact-map-grid {
      display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; position: relative; z-index: 1;
    }
    .map-cell {
      background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08);
      border-radius: 14px; padding: 16px; text-align: center;
      transition: background 0.25s;
    }
    .map-cell:hover { background: rgba(255,255,255,0.12); }
    .map-cell-icon { font-size: 1.8rem; margin-bottom: 6px; }
    .map-cell-name { font-size: 0.7rem; color: rgba(255,255,255,0.5); font-weight: 500; }
    .map-cell-val { font-size: 1.1rem; font-weight: 800; color: white; margin: 3px 0; }
    .map-title {
      font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em;
      color: rgba(255,255,255,0.4); margin-bottom: 20px; position: relative; z-index: 1;
    }
    .map-footer {
      margin-top: 20px; padding-top: 20px;
      border-top: 1px solid rgba(255,255,255,0.08);
      display: flex; justify-content: space-between; align-items: center;
      position: relative; z-index: 1;
    }
    .map-footer-stat { text-align: center; }
    .mfs-num { font-size: 1.5rem; font-weight: 800; color: white; font-family: var(--serif); }
    .mfs-label { font-size: 0.68rem; color: rgba(255,255,255,0.38); text-transform: uppercase; letter-spacing: 0.06em; }

    /* ══ CONSEILS ══ */
    .conseils { background: var(--cream2); }
    .conseils-header {
      display: flex; justify-content: space-between; align-items: flex-end;
      margin-bottom: 48px; flex-wrap: wrap; gap: 20px;
    }
    .conseils-grid {
      display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;
    }
    .conseil-card {
      background: var(--white); border: 1px solid var(--border);
      border-radius: 22px; overflow: hidden;
      transition: transform 0.25s, box-shadow 0.25s;
      cursor: pointer;
    }
    .conseil-card:hover { transform: translateY(-6px); box-shadow: 0 16px 48px rgba(45,106,79,0.12); }
    .conseil-card-top {
      height: 140px; display: flex; align-items: center; justify-content: center;
      font-size: 4rem; position: relative; overflow: hidden;
    }
    .conseil-card-top.green { background: linear-gradient(135deg, #1B4332, #40916C); }
    .conseil-card-top.orange { background: linear-gradient(135deg, #7C2D12, #E07B39); }
    .conseil-card-top.blue { background: linear-gradient(135deg, #1E3A5F, #3B82F6); }
    .conseil-card-top.purple { background: linear-gradient(135deg, #3B0764, #9333EA); }
    .conseil-card-top.red { background: linear-gradient(135deg, #7F1D1D, #EF4444); }
    .conseil-card-top.teal { background: linear-gradient(135deg, #134E4A, #14B8A6); }
    .conseil-card-top::after {
      content: '';
      position: absolute; bottom: 0; left: 0; right: 0; height: 40px;
      background: linear-gradient(to top, var(--white), transparent);
    }
    .conseil-tag {
      position: absolute; top: 12px; left: 12px;
      background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 100px; padding: 4px 12px;
      font-size: 0.65rem; font-weight: 700; color: white;
      text-transform: uppercase; letter-spacing: 0.07em;
    }
    .conseil-body { padding: 20px 22px 24px; }
    .conseil-title { font-size: 1rem; font-weight: 700; color: var(--text); margin-bottom: 8px; line-height: 1.3; }
    .conseil-desc { font-size: 0.82rem; color: var(--muted); line-height: 1.6; }
    .conseil-footer {
      display: flex; justify-content: space-between; align-items: center; margin-top: 16px;
      padding-top: 14px; border-top: 1px solid var(--border);
    }
    .conseil-author { font-size: 0.72rem; color: var(--muted); }
    .conseil-read {
      font-size: 0.75rem; font-weight: 700; color: var(--g4);
      display: flex; align-items: center; gap: 4px; text-decoration: none;
      transition: gap 0.2s;
    }
    .conseil-card:hover .conseil-read { gap: 8px; }

    /* ══ TEMOIGNAGES ══ */
    .temoignages { background: var(--cream); overflow: hidden; }
    .temoignages-header { margin-bottom: 52px; }
    .temoignages-track-wrap {
      position: relative; overflow: hidden; margin: 0 -60px; padding: 0 60px;
    }
    .temoignages-track-wrap::before,
    .temoignages-track-wrap::after {
      content: ''; position: absolute; top: 0; bottom: 0; width: 80px; z-index: 2; pointer-events: none;
    }
    .temoignages-track-wrap::before { left: 0; background: linear-gradient(to right, var(--cream), transparent); }
    .temoignages-track-wrap::after  { right: 0; background: linear-gradient(to left, var(--cream), transparent); }

    .temoignages-track {
      display: flex; gap: 20px;
      animation: scroll-left 35s linear infinite;
      width: max-content;
    }
    .temoignages-track:hover { animation-play-state: paused; }
    @keyframes scroll-left {
      from { transform: translateX(0); }
      to   { transform: translateX(-50%); }
    }
    .temoignage-card {
      width: 320px; flex-shrink: 0;
      background: var(--white); border: 1px solid var(--border);
      border-radius: 20px; padding: 26px 24px;
    }
    .stars { display: flex; gap: 3px; margin-bottom: 14px; }
    .star { color: var(--gold); font-size: 14px; }
    .temoignage-text {
      font-size: 0.88rem; line-height: 1.65; color: var(--text2);
      margin-bottom: 18px;
      font-style: italic;
    }
    .temoignage-text::before { content: '"'; font-size: 1.4rem; color: var(--g5); line-height: 0; vertical-align: -0.3em; margin-right: 2px; }
    .temoignage-text::after  { content: '"'; font-size: 1.4rem; color: var(--g5); line-height: 0; vertical-align: -0.3em; margin-left: 2px; }
    .temoignage-author { display: flex; align-items: center; gap: 12px; }
    .temoignage-avatar {
      width: 38px; height: 38px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 1rem; font-weight: 800; color: white; flex-shrink: 0;
    }
    .temoignage-name { font-size: 0.85rem; font-weight: 700; color: var(--text); }
    .temoignage-role { font-size: 0.72rem; color: var(--muted); }

    /* ══ EVENEMENTS ══ */
    .evenements { background: var(--g1); position: relative; overflow: hidden; }
    .evenements::before {
      content: '';
      position: absolute; inset: 0;
      background: radial-gradient(ellipse 60% 70% at 80% 50%, rgba(64,145,108,0.12) 0%, transparent 60%),
                  radial-gradient(ellipse 30% 40% at 10% 20%, rgba(224,123,57,0.08) 0%, transparent 50%);
    }
    .evenements .section-label { background: rgba(82,183,136,0.15); border-color: rgba(82,183,136,0.25); color: var(--g6); }
    .evenements .section-title { color: white; }
    .evenements .section-sub { color: rgba(255,255,255,0.45); }
    .evenements-header {
      display: flex; justify-content: space-between; align-items: flex-end;
      margin-bottom: 48px; flex-wrap: wrap; gap: 20px;
      position: relative; z-index: 1;
    }
    .evenements-list { display: flex; flex-direction: column; gap: 16px; position: relative; z-index: 1; }
    .event-card {
      background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
      border-radius: 18px; padding: 24px 28px;
      display: flex; align-items: center; gap: 24px;
      cursor: pointer; transition: background 0.25s, transform 0.2s;
    }
    .event-card:hover { background: rgba(255,255,255,0.09); transform: translateX(6px); }
    .event-date {
      width: 60px; flex-shrink: 0; text-align: center;
      background: rgba(255,255,255,0.07); border-radius: 14px; padding: 10px 8px;
    }
    .event-day { font-family: var(--serif); font-size: 1.9rem; font-weight: 700; color: white; line-height: 1; }
    .event-month { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.4); }
    .event-info { flex: 1; min-width: 0; }
    .event-title { font-size: 1rem; font-weight: 700; color: white; margin-bottom: 5px; }
    .event-meta { display: flex; gap: 16px; flex-wrap: wrap; }
    .event-meta-item {
      display: flex; align-items: center; gap: 5px;
      font-size: 0.78rem; color: rgba(255,255,255,0.45);
    }
    .event-badge {
      flex-shrink: 0; padding: 6px 14px; border-radius: 100px;
      font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
    }
    .event-badge.green  { background: rgba(82,183,136,0.18); color: var(--g6); }
    .event-badge.orange { background: rgba(224,123,57,0.18); color: var(--orange2); }
    .event-badge.blue   { background: rgba(59,130,246,0.18); color: #93C5FD; }
    .event-arrow { color: rgba(255,255,255,0.25); font-size: 18px; flex-shrink: 0; transition: color 0.2s, transform 0.2s; }
    .event-card:hover .event-arrow { color: rgba(255,255,255,0.6); transform: translateX(4px); }
    .event-counter {
      display: flex; gap: 32px; margin-top: 40px;
      padding: 28px 32px;
      background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);
      border-radius: 18px;
    }
    .ec-item { text-align: center; }
    .ec-num { font-family: var(--serif); font-size: 2.2rem; font-weight: 700; color: white; letter-spacing: -0.02em; }
    .ec-label { font-size: 0.7rem; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.08em; }

    /* ══ PARTENAIRES ══ */
    .partenaires { background: var(--cream2); text-align: center; padding: 70px 60px; }
    .partenaires-title { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--muted); margin-bottom: 36px; }
    .partenaires-logos {
      display: flex; align-items: center; justify-content: center; gap: 48px; flex-wrap: wrap;
    }
    .partner-logo {
      display: flex; align-items: center; gap: 10px;
      opacity: 0.4; transition: opacity 0.2s; cursor: pointer;
    }
    .partner-logo:hover { opacity: 0.75; }
    .partner-icon {
      width: 36px; height: 36px; border-radius: 10px;
      display: flex; align-items: center; justify-content: center; font-size: 18px;
    }
    .partner-name { font-size: 1rem; font-weight: 800; color: var(--text); letter-spacing: -0.01em; }



    /* ══ FOOTER ══ */
    footer {
      background: var(--g1); padding: 60px 60px 36px;
      position: relative; overflow: hidden;
    }
    footer::before {
      content: '';
      position: absolute; inset: 0;
      background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
      background-size: 60px 60px;
    }
    .footer-grid {
      display: grid; grid-template-columns: 2fr 1fr 1fr 1fr;
      gap: 48px; margin-bottom: 48px; position: relative; z-index: 1;
    }
    .footer-logo {
      display: flex; align-items: center; gap: 12px; margin-bottom: 16px;
    }
    .footer-logo-icon {
      width: 38px; height: 38px; background: linear-gradient(135deg, var(--g3), var(--g5));
      border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: 17px;
    }
    .footer-logo-text { font-size: 1.1rem; font-weight: 800; color: white; letter-spacing: -0.01em; }
    .footer-desc { font-size: 0.83rem; color: rgba(255,255,255,0.35); line-height: 1.65; max-width: 240px; }
    .footer-col-title { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.35); font-weight: 600; margin-bottom: 16px; }
    .footer-links { list-style: none; display: flex; flex-direction: column; gap: 10px; }
    .footer-links a { font-size: 0.84rem; color: rgba(255,255,255,0.5); text-decoration: none; transition: color 0.2s; }
    .footer-links a:hover { color: var(--g5); }
    .footer-bottom {
      border-top: 1px solid rgba(255,255,255,0.06);
      padding-top: 24px;
      display: flex; justify-content: space-between; align-items: center;
      position: relative; z-index: 1;
    }
    .footer-copy { font-size: 0.78rem; color: rgba(255,255,255,0.25); }
    .footer-legal { display: flex; gap: 24px; }
    .footer-legal a { font-size: 0.75rem; color: rgba(255,255,255,0.25); text-decoration: none; transition: color 0.2s; }
    .footer-legal a:hover { color: rgba(255,255,255,0.5); }

    /* ══ RESPONSIVE ══ */
    @media (max-width: 1024px) {
      nav { padding: 16px 28px; }
      .nav-links { display: none; }
      .hero { padding: 100px 28px 60px; }
      section { padding: 70px 28px; }
      .impact-grid { grid-template-columns: 1fr; gap: 40px; }
      .conseils-grid { grid-template-columns: repeat(2, 1fr); }
      .footer-grid { grid-template-columns: 1fr 1fr; }
      .hero-stats { display: none; }
      footer { padding: 48px 28px 28px; }
    }
    @media (max-width: 640px) {
      .hero-title { font-size: 2.5rem; }
      .conseils-grid { grid-template-columns: 1fr; }
      .hero-actions { flex-direction: column; }
      .btn-hero-primary, .btn-hero-secondary { width: 100%; justify-content: center; }
      .event-counter { flex-wrap: wrap; gap: 20px; }
      .footer-grid { grid-template-columns: 1fr; }
      .partenaires-logos { gap: 28px; }
      footer { padding: 40px 20px 24px; }
      section { padding: 60px 20px; }
    }
  </style>
</head>
<body>

<!-- ══ NAVBAR ══ -->
<nav>
  <div class="nav-logo">
    <div class="nav-logo-icon">🍃</div>
    <span class="nav-logo-text">FoodSave</span>
  </div>
  <ul class="nav-links">
    <li><a href="#impact">Impact</a></li>
    <li><a href="#conseils">Conseils</a></li>
    <li><a href="#evenements">Événements</a></li>
    <li><a href="#avis">Avis</a></li>

  </ul>
  <div class="nav-cta">
    <button class="btn-ghost" onclick="window.location.href='index.php'">Se connecter</button>
    <button class="btn-solid" onclick="window.location.href='index.php'">Espace client →</button>
  </div>
</nav>

<!-- ══ HERO ══ -->
<section class="hero" id="home">
  <div class="hero-bg"></div>
  <!-- Floating food emojis -->
  <div class="food-float" style="top:15%;left:72%;animation-duration:8s;">🥦</div>
  <div class="food-float" style="top:65%;left:85%;animation-duration:11s;animation-delay:-3s;font-size:2.2rem;">🍋</div>
  <div class="food-float" style="top:30%;left:60%;animation-duration:9s;animation-delay:-5s;font-size:2rem;">🫐</div>
  <div class="food-float" style="top:75%;left:68%;animation-duration:13s;animation-delay:-2s;font-size:2.5rem;">🥕</div>
  <div class="food-float" style="top:50%;left:78%;animation-duration:10s;animation-delay:-7s;font-size:1.8rem;">🌿</div>

  <div class="hero-content">
    <div class="hero-badge">
      <div class="badge-dot"></div>
      Plateforme active · Tunis, Tunisie
    </div>

    <h1 class="hero-title">
      Ensemble,<br>
      réduisons le<br>
      <em>gaspillage</em><br>
      <span>alimentaire</span>
    </h1>

    <p class="hero-desc">
      FoodSave connecte les producteurs, collecteurs et associations pour transformer les invendus en ressources. Suivez, gérez et agissez en temps réel.
    </p>

    <div class="hero-actions">
      <button class="btn-hero-primary" onclick="window.location.href='index.php'">
        Accéder
        <span>→</span>
      </button>
      <button class="btn-hero-secondary" onclick="document.getElementById('impact').scrollIntoView({behavior:'smooth'})">
        Découvrir l'impact
        <span>↓</span>
      </button>
    </div>
  </div>

  <div class="scroll-hint">
    <div class="scroll-line"></div>
    Défiler pour explorer
  </div>

  <div class="hero-stats">
    <div class="hero-stat-item">
      <div class="hsi-num" id="h-kg">0</div>
      <div class="hsi-label">kg récupérés</div>
    </div>
    <div class="hero-stat-item">
      <div class="hsi-num" id="h-col">0</div>
      <div class="hsi-label">collectes</div>
    </div>
    <div class="hero-stat-item">
      <div class="hsi-num" id="h-ben">0</div>
      <div class="hsi-label">bénéficiaires</div>
    </div>
    <div class="hero-stat-item">
      <div class="hsi-num" id="h-co2">0<small>t</small></div>
      <div class="hsi-label">CO₂ évité</div>
    </div>
  </div>
</section>

<!-- ══ PARTENAIRES ══ -->
<div class="partenaires">
  <p class="partenaires-title">Ils nous font confiance</p>
  <div class="partenaires-logos">
    <div class="partner-logo"><div class="partner-icon">🏪</div><span class="partner-name">Carrefour</span></div>
    <div class="partner-logo"><div class="partner-icon">🌾</div><span class="partner-name">AgriTunisie</span></div>
    <div class="partner-logo"><div class="partner-icon">🏥</div><span class="partner-name">Croissant Rouge</span></div>
    <div class="partner-logo"><div class="partner-icon">🎓</div><span class="partner-name">Esprit School</span></div>
    <div class="partner-logo"><div class="partner-icon">🏙️</div><span class="partner-name">Mairie Tunis</span></div>
    <div class="partner-logo"><div class="partner-icon">🌍</div><span class="partner-name">FAO Tunisia</span></div>
  </div>
</div>

<!-- ══ IMPACT ══ -->
<section class="impact" id="impact">
  <div class="section-label">📊 Notre Impact</div>
  <h2 class="section-title">Des chiffres qui<br><em>parlent d'eux-mêmes</em></h2>
  <p class="section-sub">Chaque kilogramme récupéré, c'est une famille nourrie et une tonne de CO₂ de moins dans l'atmosphère.</p>

  <div class="impact-grid">
    <div class="impact-kpis">
      <div class="kpi-card" style="animation-delay:0.1s">
        <div class="kpi-icon green">🗑️</div>
        <div><div class="kpi-num" id="kpi-1">4 280</div><div class="kpi-label">kg de nourriture récupérés ce mois</div></div>
      </div>
      <div class="kpi-card" style="animation-delay:0.2s">
        <div class="kpi-icon orange">🚛</div>
        <div><div class="kpi-num" id="kpi-2">137</div><div class="kpi-label">collectes organisées</div></div>
      </div>
      <div class="kpi-card" style="animation-delay:0.3s">
        <div class="kpi-icon blue">♻️</div>
        <div><div class="kpi-num" id="kpi-3">12 t</div><div class="kpi-label">de CO₂ évitées depuis le lancement</div></div>
      </div>
    </div>

    <div class="impact-visual">
      <div class="impact-map">
        <p class="map-title">Répartition par catégorie alimentaire</p>
        <div class="impact-map-grid">
          <div class="map-cell"><div class="map-cell-icon">🥖</div><div class="map-cell-val">28%</div><div class="map-cell-name">Boulangerie</div></div>
          <div class="map-cell"><div class="map-cell-icon">🥦</div><div class="map-cell-val">22%</div><div class="map-cell-name">Légumes</div></div>
          <div class="map-cell"><div class="map-cell-icon">🍎</div><div class="map-cell-val">18%</div><div class="map-cell-name">Fruits</div></div>
          <div class="map-cell"><div class="map-cell-icon">🥛</div><div class="map-cell-val">14%</div><div class="map-cell-name">Laitiers</div></div>
          <div class="map-cell"><div class="map-cell-icon">🍗</div><div class="map-cell-val">11%</div><div class="map-cell-name">Viandes</div></div>
          <div class="map-cell"><div class="map-cell-icon">🍝</div><div class="map-cell-val">7%</div><div class="map-cell-name">Céréales</div></div>
          <div class="map-cell"><div class="map-cell-icon">🫙</div><div class="map-cell-val">0%</div><div class="map-cell-name">Conserves</div></div>
          <div class="map-cell"><div class="map-cell-icon">🍬</div><div class="map-cell-val">0%</div><div class="map-cell-name">Sucreries</div></div>
        </div>
        <div class="map-footer">
          <div class="map-footer-stat"><div class="mfs-num">2 340</div><div class="mfs-label">Bénéficiaires</div></div>
          <div class="map-footer-stat"><div class="mfs-num">98%</div><div class="mfs-label">Taux redistrib.</div></div>
          <div class="map-footer-stat"><div class="mfs-num">47</div><div class="mfs-label">Partenaires actifs</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ CONSEILS ══ -->
<section class="conseils" id="conseils">
  <div class="conseils-header">
    <div>
      <div class="section-label">💡 Conseils & Bonnes Pratiques</div>
      <h2 class="section-title">Agir au quotidien<br><em>contre le gaspillage</em></h2>
    </div>
    <button class="btn-ghost">Voir tous les conseils →</button>
  </div>

  <div class="conseils-grid">
    <div class="conseil-card">
      <div class="conseil-card-top green">
        🥬
        <span class="conseil-tag">Conservation</span>
      </div>
      <div class="conseil-body">
        <div class="conseil-title">Conservez vos légumes jusqu'à 5 jours de plus</div>
        <div class="conseil-desc">Enveloppez les légumes à feuilles dans un linge humide et évitez de les laver avant stockage. La temperature idéale est entre 1° et 4°C.</div>
        <div class="conseil-footer">
          <span class="conseil-author">par Dr. Sonia Mrabet</span>
          <a href="#" class="conseil-read">Lire →</a>
        </div>
      </div>
    </div>

    <div class="conseil-card">
      <div class="conseil-card-top orange">
        📅
        <span class="conseil-tag">Planification</span>
      </div>
      <div class="conseil-body">
        <div class="conseil-title">Planifiez vos achats avec le menu de la semaine</div>
        <div class="conseil-desc">Préparer un menu hebdomadaire réduit les achats impulsifs de 40% et diminue drastiquement les restes en fin de semaine.</div>
        <div class="conseil-footer">
          <span class="conseil-author">par Équipe FoodSave</span>
          <a href="#" class="conseil-read">Lire →</a>
        </div>
      </div>
    </div>

    <div class="conseil-card">
      <div class="conseil-card-top blue">
        ♻️
        <span class="conseil-tag">Recyclage</span>
      </div>
      <div class="conseil-body">
        <div class="conseil-title">Transformez vos restes en compost actif</div>
        <div class="conseil-desc">Les épluchures et restes organiques peuvent devenir un engrais naturel en 6 semaines. Un composteur urbain suffit pour commencer.</div>
        <div class="conseil-footer">
          <span class="conseil-author">par Amine Laouiti</span>
          <a href="#" class="conseil-read">Lire →</a>
        </div>
      </div>
    </div>

    <div class="conseil-card">
      <div class="conseil-card-top purple">
        🛒
        <span class="conseil-tag">Achat malin</span>
      </div>
      <div class="conseil-body">
        <div class="conseil-title">Comprendre les dates de péremption</div>
        <div class="conseil-desc">«À consommer avant» ≠ «À consommer de préférence avant». Le premier est une question de sécurité, le second de qualité.</div>
        <div class="conseil-footer">
          <span class="conseil-author">par NextWave Team</span>
          <a href="#" class="conseil-read">Lire →</a>
        </div>
      </div>
    </div>

    <div class="conseil-card">
      <div class="conseil-card-top red">
        🍽️
        <span class="conseil-tag">Cuisine</span>
      </div>
      <div class="conseil-body">
        <div class="conseil-title">10 recettes avec ce que vous avez déjà</div>
        <div class="conseil-desc">Transformez vos invendus et restes en plats savoureux. Le pain rassis devient pain perdu, les fruits trop mûrs deviennent smoothies.</div>
        <div class="conseil-footer">
          <span class="conseil-author">par Chef Karim Ben Ali</span>
          <a href="#" class="conseil-read">Lire →</a>
        </div>
      </div>
    </div>

    <div class="conseil-card">
      <div class="conseil-card-top teal">
        🌍
        <span class="conseil-tag">Environnement</span>
      </div>
      <div class="conseil-body">
        <div class="conseil-title">L'impact climatique du gaspillage alimentaire</div>
        <div class="conseil-desc">1/3 de la nourriture mondiale est gaspillée, représentant 8% des émissions mondiales de gaz à effet de serre. Chaque geste compte.</div>
        <div class="conseil-footer">
          <span class="conseil-author">par Lina Gharbi</span>
          <a href="#" class="conseil-read">Lire →</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ TEMOIGNAGES ══ -->
<section class="temoignages" id="avis">
  <div class="temoignages-header">
    <div class="section-label">⭐ Avis & Témoignages</div>
    <h2 class="section-title">Ce qu'ils disent<br><em>de FoodSave</em></h2>
    <p class="section-sub">Plus de 200 utilisateurs actifs font confiance à notre plateforme pour gérer leurs opérations quotidiennes.</p>
  </div>

  <div class="temoignages-track-wrap">
    <div class="temoignages-track" id="temoignages-track">
      <!-- Set 1 -->
      <div class="temoignage-card">
        <div class="stars">⭐⭐⭐⭐⭐</div>
        <p class="temoignage-text">FoodSave a transformé notre gestion des invendus. On a réduit notre gaspillage de 60% en seulement deux mois. L'interface est intuitive et le tableau de bord est très complet.</p>
        <div class="temoignage-author">
          <div class="temoignage-avatar" style="background:linear-gradient(135deg,#1B4332,#40916C)">SR</div>
          <div><div class="temoignage-name">Sarra Rhimi</div><div class="temoignage-role">Responsable, Carrefour Tunis</div></div>
        </div>
      </div>
      <div class="temoignage-card">
        <div class="stars">⭐⭐⭐⭐⭐</div>
        <p class="temoignage-text">Grâce aux alertes de collecte, nos bénévoles sont toujours informés en temps réel. On a pu distribuer plus de 800 kg de nourriture ce trimestre.</p>
        <div class="temoignage-author">
          <div class="temoignage-avatar" style="background:linear-gradient(135deg,#7C2D12,#E07B39)">MB</div>
          <div><div class="temoignage-name">Mohamed Ben Salah</div><div class="temoignage-role">Coordinateur, Croissant Rouge</div></div>
        </div>
      </div>
      <div class="temoignage-card">
        <div class="stars">⭐⭐⭐⭐⭐</div>
        <p class="temoignage-text">L'assistant IA est impressionnant. Il m'a aidé à optimiser nos routes de collecte et à prévoir les surplus. Un vrai gain de temps pour toute notre équipe.</p>
        <div class="temoignage-author">
          <div class="temoignage-avatar" style="background:linear-gradient(135deg,#1E3A5F,#3B82F6)">FT</div>
          <div><div class="temoignage-name">Fatma Trabelsi</div><div class="temoignage-role">Directrice, AgriTunisie Nord</div></div>
        </div>
      </div>
      <div class="temoignage-card">
        <div class="stars">⭐⭐⭐⭐⭐</div>
        <p class="temoignage-text">Nous utilisons FoodSave depuis le lancement. Le système de catégories est parfait pour trier nos déchets alimentaires et le rapport CO₂ motivie toute l'équipe.</p>
        <div class="temoignage-author">
          <div class="temoignage-avatar" style="background:linear-gradient(135deg,#3B0764,#9333EA)">KL</div>
          <div><div class="temoignage-name">Kamel Laouiti</div><div class="temoignage-role">Chef, Restaurant El Mida</div></div>
        </div>
      </div>
      <div class="temoignage-card">
        <div class="stars">⭐⭐⭐⭐⭐</div>
        <p class="temoignage-text">Simple, rapide, efficace. On a intégré FoodSave en une journée et nos opérateurs l'utilisent sans aucune formation particulière. Bravo à l'équipe NextWave !</p>
        <div class="temoignage-card-author">
          <div class="temoignage-avatar" style="background:linear-gradient(135deg,#134E4A,#14B8A6)">NB</div>
          <div><div class="temoignage-name">Nadia Belhaj</div><div class="temoignage-role">Manager, Marché Central</div></div>
        </div>
      </div>
      <div class="temoignage-card">
        <div class="stars">⭐⭐⭐⭐⭐</div>
        <p class="temoignage-text">La fonctionnalité d'export PDF pour les rapports mensuels est un vrai atout pour nos comptes-rendus aux autorités locales. Nous la recommandons vivement.</p>
        <div class="temoignage-author">
          <div class="temoignage-avatar" style="background:linear-gradient(135deg,#78350F,#D97706)">AG</div>
          <div><div class="temoignage-name">Amine Gharbi</div><div class="temoignage-role">Responsable RSE, Ville de Tunis</div></div>
        </div>
      </div>
      <!-- Set 2 (duplicate for infinite scroll) -->
      <div class="temoignage-card">
        <div class="stars">⭐⭐⭐⭐⭐</div>
        <p class="temoignage-text">FoodSave a transformé notre gestion des invendus. On a réduit notre gaspillage de 60% en seulement deux mois. L'interface est intuitive et le tableau de bord est très complet.</p>
        <div class="temoignage-author">
          <div class="temoignage-avatar" style="background:linear-gradient(135deg,#1B4332,#40916C)">SR</div>
          <div><div class="temoignage-name">Sarra Rhimi</div><div class="temoignage-role">Responsable, Carrefour Tunis</div></div>
        </div>
      </div>
      <div class="temoignage-card">
        <div class="stars">⭐⭐⭐⭐⭐</div>
        <p class="temoignage-text">Grâce aux alertes de collecte, nos bénévoles sont toujours informés en temps réel. On a pu distribuer plus de 800 kg de nourriture ce trimestre.</p>
        <div class="temoignage-author">
          <div class="temoignage-avatar" style="background:linear-gradient(135deg,#7C2D12,#E07B39)">MB</div>
          <div><div class="temoignage-name">Mohamed Ben Salah</div><div class="temoignage-role">Coordinateur, Croissant Rouge</div></div>
        </div>
      </div>
      <div class="temoignage-card">
        <div class="stars">⭐⭐⭐⭐⭐</div>
        <p class="temoignage-text">L'assistant IA est impressionnant. Il m'a aidé à optimiser nos routes de collecte et à prévoir les surplus. Un vrai gain de temps pour toute notre équipe.</p>
        <div class="temoignage-author">
          <div class="temoignage-avatar" style="background:linear-gradient(135deg,#1E3A5F,#3B82F6)">FT</div>
          <div><div class="temoignage-name">Fatma Trabelsi</div><div class="temoignage-role">Directrice, AgriTunisie Nord</div></div>
        </div>
      </div>
      <div class="temoignage-card">
        <div class="stars">⭐⭐⭐⭐⭐</div>
        <p class="temoignage-text">Nous utilisons FoodSave depuis le lancement. Le système de catégories est parfait pour trier nos déchets alimentaires et le rapport CO₂ motivie toute l'équipe.</p>
        <div class="temoignage-author">
          <div class="temoignage-avatar" style="background:linear-gradient(135deg,#3B0764,#9333EA)">KL</div>
          <div><div class="temoignage-name">Kamel Laouiti</div><div class="temoignage-role">Chef, Restaurant El Mida</div></div>
        </div>
      </div>
      <div class="temoignage-card">
        <div class="stars">⭐⭐⭐⭐⭐</div>
        <p class="temoignage-text">Simple, rapide, efficace. On a intégré FoodSave en une journée et nos opérateurs l'utilisent sans aucune formation particulière. Bravo à l'équipe NextWave !</p>
        <div class="temoignage-author">
          <div class="temoignage-avatar" style="background:linear-gradient(135deg,#134E4A,#14B8A6)">NB</div>
          <div><div class="temoignage-name">Nadia Belhaj</div><div class="temoignage-role">Manager, Marché Central</div></div>
        </div>
      </div>
      <div class="temoignage-card">
        <div class="stars">⭐⭐⭐⭐⭐</div>
        <p class="temoignage-text">La fonctionnalité d'export PDF pour les rapports mensuels est un vrai atout pour nos comptes-rendus aux autorités locales. Nous la recommandons vivement.</p>
        <div class="temoignage-author">
          <div class="temoignage-avatar" style="background:linear-gradient(135deg,#78350F,#D97706)">AG</div>
          <div><div class="temoignage-name">Amine Gharbi</div><div class="temoignage-role">Responsable RSE, Ville de Tunis</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ EVENEMENTS ══ -->
<section class="evenements" id="evenements">
  <div class="evenements-header">
    <div>
      <div class="section-label">📅 Événements</div>
      <h2 class="section-title" style="color:white;">Prochain rendez-vous<br><em style="color:var(--g6)">à ne pas manquer</em></h2>
      <p class="section-sub">Collectes, ateliers, formations — rejoignez la communauté FoodSave.</p>
    </div>
    <button class="btn-ghost" style="border-color:rgba(255,255,255,0.15);color:rgba(255,255,255,0.6);" onmouseover="this.style.background='rgba(255,255,255,0.08)';this.style.color='white'" onmouseout="this.style.background='transparent';this.style.color='rgba(255,255,255,0.6)'">Voir le calendrier →</button>
  </div>

  <div class="evenements-list">
    <div class="event-card">
      <div class="event-date"><div class="event-day">14</div><div class="event-month">Mai</div></div>
      <div class="event-info">
        <div class="event-title">Grande Collecte du Marché Central de Tunis</div>
        <div class="event-meta">
          <span class="event-meta-item">📍 Marché Bab El Bhar, Tunis</span>
          <span class="event-meta-item">🕘 09h00 – 13h00</span>
          <span class="event-meta-item">👥 24 participants</span>
        </div>
      </div>
      <span class="event-badge green">Collecte</span>
      <span class="event-arrow">→</span>
    </div>

    <div class="event-card">
      <div class="event-date"><div class="event-day">19</div><div class="event-month">Mai</div></div>
      <div class="event-info">
        <div class="event-title">Atelier Anti-Gaspillage : Cuisiner avec les restes</div>
        <div class="event-meta">
          <span class="event-meta-item">📍 Esprit School, Ariana</span>
          <span class="event-meta-item">🕑 14h00 – 17h00</span>
          <span class="event-meta-item">👥 40 places</span>
        </div>
      </div>
      <span class="event-badge orange">Atelier</span>
      <span class="event-arrow">→</span>
    </div>

    <div class="event-card">
      <div class="event-date"><div class="event-day">26</div><div class="event-month">Mai</div></div>
      <div class="event-info">
        <div class="event-title">Formation Opérateurs FoodSave — Module 3</div>
        <div class="event-meta">
          <span class="event-meta-item">📍 En ligne · Zoom</span>
          <span class="event-meta-item">🕙 10h00 – 12h00</span>
          <span class="event-meta-item">👥 15 participants</span>
        </div>
      </div>
      <span class="event-badge blue">Formation</span>
      <span class="event-arrow">→</span>
    </div>

    <div class="event-card">
      <div class="event-date"><div class="event-day">03</div><div class="event-month">Jun</div></div>
      <div class="event-info">
        <div class="event-title">Journée Mondiale de l'Environnement — Collecte Nationale</div>
        <div class="event-meta">
          <span class="event-meta-item">📍 Plusieurs villes</span>
          <span class="event-meta-item">🕗 07h00 – 18h00</span>
          <span class="event-meta-item">👥 +200 bénévoles</span>
        </div>
      </div>
      <span class="event-badge green">Événement national</span>
      <span class="event-arrow">→</span>
    </div>
  </div>

  <div class="event-counter">
    <div class="ec-item"><div class="ec-num">12</div><div class="ec-label">Événements ce mois</div></div>
    <div class="ec-item"><div class="ec-num">340</div><div class="ec-label">Bénévoles mobilisés</div></div>
    <div class="ec-item"><div class="ec-num">8</div><div class="ec-label">Villes couvertes</div></div>
    <div class="ec-item"><div class="ec-num">1.2t</div><div class="ec-label">Objectif kg ce mois</div></div>
  </div>
</section>



<!-- ══ FOOTER ══ -->
<footer>
  <div class="footer-grid">
    <div>
      <div class="footer-logo">
        <div class="footer-logo-icon">🍃</div>
        <span class="footer-logo-text">FoodSave</span>
      </div>
      <p class="footer-desc">Plateforme de gestion du gaspillage alimentaire pour les professionnels et associations en Tunisie.</p>
    </div>
    <div>
      <div class="footer-col-title">Plateforme</div>
      <ul class="footer-links">
        <li><a href="#">Tableau de bord</a></li>
        <li><a href="#">Gestion des déchets</a></li>
        <li><a href="#">Planification collectes</a></li>
        <li><a href="#">Rapports & exports</a></li>
        <li><a href="#">Assistant IA</a></li>
      </ul>
    </div>
    <div>
      <div class="footer-col-title">Ressources</div>
      <ul class="footer-links">
        <li><a href="#">Documentation API</a></li>
        <li><a href="#">Guide utilisateur</a></li>
        <li><a href="#">Conseils & blog</a></li>
        <li><a href="#">Événements</a></li>
        <li><a href="#">Formation</a></li>
      </ul>
    </div>
    <div>
      <div class="footer-col-title">Organisation</div>
      <ul class="footer-links">
        <li><a href="#">À propos</a></li>
        <li><a href="#">NextWave Team</a></li>
        <li><a href="#">Partenaires</a></li>
        <li><a href="#">Contact</a></li>
        <li><a href="#">Esprit 2526-2A19</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="footer-copy">© 2026 FoodSave · NextWave Team · Esprit School</div>
    <div class="footer-legal">
      <a href="#">Confidentialité</a>
      <a href="#">Conditions</a>
      <a href="#">Mentions légales</a>
    </div>
  </div>
</footer>

<script>
  // ── Hero counter animation
  function countUp(el, end, suffix='', dur=2000) {
    let s=0; const step=end/(dur/16);
    const t=setInterval(()=>{ s=Math.min(s+step,end); el.textContent=Math.round(s)+suffix; if(s>=end)clearInterval(t); },16);
  }
  setTimeout(()=>{
    const h1=document.getElementById('h-kg'),
          h2=document.getElementById('h-col'),
          h3=document.getElementById('h-ben'),
          h4=document.getElementById('h-co2');
    if(h1){countUp(h1,4280,' kg');countUp(h2,137,'');countUp(h3,2340,'');h4.innerHTML='<span style="font-family:var(--serif);font-size:2rem;font-weight:700;color:white;">12</span><small style="color:rgba(255,255,255,0.5)">t</small>';}
  },400);

  // ── Navbar scroll shrink
  window.addEventListener('scroll',()=>{
    const n=document.querySelector('nav');
    if(window.scrollY>50){n.style.padding='12px 60px';}
    else{n.style.padding='18px 60px';}
  });
</script>

</body>
</html>
