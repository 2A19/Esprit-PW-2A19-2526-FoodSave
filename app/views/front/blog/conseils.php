<?php
// Démarrer la session (pour la langue)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger le traducteur
require_once 'C:/xampp/htdocs/FoodSave/app/core/Translator.php';

// Initialiser le traducteur
$translator = Translator::getInstance();
$lang = $translator->getCurrentLang();

// Fonction de traduction
$t = function($key) use ($translator) {
    return $translator->translate($key);
};
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodSave - <?php echo $t('nav_tips'); ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:"DM Sans",sans-serif;background:#0d1f14;color:#e8f5e9;overflow-x:hidden}
        .bg-mesh{position:fixed;inset:0;pointer-events:none;z-index:0}
        .glow-1{position:absolute;width:520px;height:520px;border-radius:50%;background:radial-gradient(circle,rgba(34,197,94,0.18) 0%,transparent 70%);top:-80px;right:-60px;animation:driftA 8s ease-in-out infinite alternate}
        .glow-2{position:absolute;width:400px;height:400px;border-radius:50%;background:radial-gradient(circle,rgba(16,185,129,0.12) 0%,transparent 70%);bottom:0;left:-80px;animation:driftB 10s ease-in-out infinite alternate}
        .glow-3{position:absolute;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle,rgba(234,179,8,0.09) 0%,transparent 70%);top:40%;left:38%;animation:driftA 12s ease-in-out infinite alternate}
        @keyframes driftA{from{transform:translate(0,0)}to{transform:translate(30px,20px)}}
        @keyframes driftB{from{transform:translate(0,0)}to{transform:translate(-20px,-30px)}}
        .grid-lines{position:absolute;inset:0;background-image:linear-gradient(rgba(34,197,94,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(34,197,94,0.04) 1px,transparent 1px);background-size:48px 48px}

        .navbar{position:relative;z-index:10;display:flex;align-items:center;justify-content:space-between;padding:22px 52px;border-bottom:1px solid rgba(34,197,94,0.1);background:rgba(13,31,20,0.6);backdrop-filter:blur(16px)}
        .logo-wrap{display:flex;align-items:center;gap:10px;cursor:pointer}
        .logo-wrap img{height:44px}
        .logo-wrap span{font-weight:700;font-size:1.4rem;background:linear-gradient(135deg,#4caf50,#ff6b35);-webkit-background-clip:text;background-clip:text;color:transparent}
        .nav-links{display:flex;gap:32px;list-style:none}
        .nav-links a{text-decoration:none;font-size:14px;font-weight:500;color:rgba(255,255,255,0.65);transition:color 0.2s}
        .nav-links a.active{color:#4ade80}
        .nav-links a:hover{color:#fff}
        .nav-btns{display:flex;gap:10px}
        .btn-ghost{padding:8px 20px;border:1px solid rgba(74,222,128,0.35);border-radius:50px;background:transparent;color:#4ade80;font-size:13px;font-weight:600;cursor:pointer}
        .btn-fill{padding:8px 22px;border:none;border-radius:50px;background:#16a34a;color:#fff;font-size:13px;font-weight:700;cursor:pointer;box-shadow:0 0 20px rgba(22,163,74,0.45)}
        
        .language-selector{position:fixed;top:90px;right:20px;z-index:9999;background:rgba(13,31,20,0.85);backdrop-filter:blur(10px);padding:8px 15px;border-radius:30px;border:1px solid rgba(74,222,128,0.2)}
        .language-selector a{text-decoration:none;margin:0 5px;color:rgba(255,255,255,0.6)}
        .language-selector a.active{font-weight:bold;color:#4ade80}
        
        .hero{position:relative;z-index:5;padding:3rem 52px 2rem;text-align:center}
        .hero h1{font-size:2.5rem;font-weight:700;color:#fff;margin-bottom:1rem}
        .hero h1 span{color:#4ade80}
        .hero p{color:rgba(255,255,255,0.5)}
        
        .tips-section{position:relative;z-index:5;padding:2rem 52px 4rem}
        .section-header{text-align:center;margin-bottom:2rem}
        .section-header h2{font-size:2rem;color:#fff;margin-bottom:0.5rem}
        .section-header p{color:rgba(255,255,255,0.45)}
        
        .tips-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:2rem;max-width:1400px;margin:0 auto}
        .tip-card{background:rgba(255,255,255,0.04);border:1px solid rgba(74,222,128,0.1);border-radius:20px;padding:1.5rem;transition:all 0.3s ease}
        .tip-card:hover{transform:translateY(-5px);background:rgba(74,222,128,0.05);border-color:rgba(74,222,128,0.25)}
        .tip-icon{width:60px;height:60px;background:rgba(74,222,128,0.08);border-radius:30px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin-bottom:1rem}
        .tip-title{font-size:1.3rem;font-weight:700;color:#fff;margin-bottom:0.5rem}
        .tip-desc{color:rgba(255,255,255,0.6);font-size:0.9rem;line-height:1.5}
        .tip-tag{display:inline-block;margin-top:1rem;padding:4px 12px;background:rgba(74,222,128,0.12);color:#4ade80;border-radius:50px;font-size:0.7rem;font-weight:600}
        
        .footer{background:rgba(8,16,8,0.9);border-top:1px solid rgba(74,222,128,0.08);padding:3rem 52px 1rem;margin-top:3rem}
        .footer-bottom{text-align:center;padding-top:2rem;border-top:1px solid rgba(255,255,255,0.05);font-size:0.8rem;color:rgba(255,255,255,0.3)}
        
        @media (max-width:768px){
            .navbar{padding:22px 20px;flex-direction:column;gap:1rem}
            .nav-links{flex-wrap:wrap;justify-content:center}
            .hero{padding:2rem 20px}
            .tips-section{padding:2rem 20px}
            .tips-grid{grid-template-columns:1fr}
            .language-selector{top:80px;right:15px}
        }
    </style>
</head>
<body>

<div class="bg-mesh">
    <div class="grid-lines"></div>
    <div class="glow-1"></div><div class="glow-2"></div><div class="glow-3"></div>
</div>

<div class="language-selector">
    <a href="?lang=fr&action=conseils" class="<?php echo $lang == 'fr' ? 'active' : ''; ?>">🇫🇷 FR</a>
    <span>|</span>
    <a href="?lang=en&action=conseils" class="<?php echo $lang == 'en' ? 'active' : ''; ?>">🇬🇧 EN</a>
</div>

<nav class="navbar">
    <div class="logo-wrap" onclick="location.href='index.php?action=blog'">
        <img src="/FoodSave/public/assets/images/logo_foodsave.png" alt="FoodSave">
        <span>FoodSave</span>
    </div>
    <ul class="nav-links">
        <li><a href="index.php?action=blog"><?php echo $t('nav_home'); ?></a></li>
        <li><a href="index.php?action=blog"><?php echo $t('nav_blog'); ?></a></li>
        <li><a href="index.php?action=conseils" class="active"><?php echo $t('nav_tips'); ?></a></li>
        <li><a href="index.php?action=recettes"><?php echo $t('nav_recipes'); ?></a></li>
    </ul>
    <div class="nav-btns">
        <button class="btn-ghost" onclick="location.href='index.php?action=login'"><?php echo $t('nav_login'); ?></button>
        <button class="btn-fill" onclick="location.href='index.php?action=register'"><?php echo $t('nav_register'); ?></button>
    </div>
</nav>

<section class="hero">
    <h1>💡 <span><?php echo $t('nav_tips'); ?></span> anti-gaspillage</h1>
    <p><?php echo $t('blog_subtitle'); ?></p>
</section>

<section class="tips-section">
    <div class="section-header">
        <h2>🌱 Astuces zéro déchet</h2>
        <p>Des gestes simples pour une consommation responsable</p>
    </div>
    
    <div class="tips-grid">
        <div class="tip-card">
            <div class="tip-icon">🥕</div>
            <h3 class="tip-title">Rangez votre frigo dans le bon ordre</h3>
            <p class="tip-desc">En haut : produits laitiers. Au milieu : restes. En bas : viande. Bac à légumes : fruits et légumes. Une bonne organisation prolonge la durée de vie de vos aliments.</p>
            <span class="tip-tag">#Organisation</span>
        </div>
        
        <div class="tip-card">
            <div class="tip-icon">📅</div>
            <h3 class="tip-title">La date limite n'est pas une date de mort</h3>
            <p class="tip-desc">La DDM (Date de Durabilité Minimale) est une recommandation. Après cette date, le produit est encore consommable ! Seule la DLC (Date Limite de Consommation) est impérative.</p>
            <span class="tip-tag">#BonÀSavoir</span>
        </div>
        
        <div class="tip-card">
            <div class="tip-icon">🛒</div>
            <h3 class="tip-title">Faites une liste de courses</h3>
            <p class="tip-desc">Avant d'aller au supermarché, listez ce dont vous avez vraiment besoin. Cela évite les achats impulsifs et le gaspillage alimentaire.</p>
            <span class="tip-tag">#Planification</span>
        </div>
        
        <div class="tip-card">
            <div class="tip-icon">❄️</div>
            <h3 class="tip-title">Congelez vos surplus</h3>
            <p class="tip-desc">Vous avez trop cuisiné ? Congelez en portions individuelles. Vos légumes vont faner ? Coupez-les et congelez-les !</p>
            <span class="tip-tag">#Conservation</span>
        </div>
        
        <div class="tip-card">
            <div class="tip-icon">🍞</div>
            <h3 class="tip-title">Ne jetez plus votre pain rassis</h3>
            <p class="tip-desc">Pain perdu, chapelure, croûtons pour soupe... Le pain rassis a plein d'usages délicieux !</p>
            <span class="tip-tag">#Recettes</span>
        </div>
        
        <div class="tip-card">
            <div class="tip-icon">🥬</div>
            <h3 class="tip-title">Utilisez les fanes et épluchures</h3>
            <p class="tip-desc">Les fanes de carottes, radis ou betteraves se mangent en salade, pesto ou soupe. Les épluchures peuvent devenir des chips croustillantes !</p>
            <span class="tip-tag">#ZéroDéchet</span>
        </div>
        
        <?php if(isset($articles) && !empty($articles)): ?>
            <?php foreach($articles as $article): ?>
            <div class="tip-card">
                <div class="tip-icon">💡</div>
                <h3 class="tip-title"><?php echo htmlspecialchars($article['titre']); ?></h3>
                <p class="tip-desc"><?php echo htmlspecialchars(substr($article['resume'], 0, 100)) . '...'; ?></p>
                <a href="index.php?action=detail&id=<?php echo $article['id']; ?>" class="tip-tag" style="text-decoration:none;display:inline-block"><?php echo $t('read_more'); ?> →</a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<footer class="footer">
    <div class="footer-bottom">
        <p>© 2025 FoodSave - <?php echo $t('footer_copyright'); ?></p>
    </div>
</footer>
<!-- Chatbot IA -->
<style>
.chatbot-btn {
    position: fixed;
    bottom: 25px;
    right: 25px;
    background: #16a34a;
    border: none;
    border-radius: 50px;
    padding: 12px 20px;
    color: white;
    cursor: pointer;
    font-weight: bold;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    font-family: inherit;
}
.chatbot-window {
    position: fixed;
    bottom: 80px;
    right: 25px;
    width: 350px;
    height: 450px;
    background: #0d1f14;
    border: 1px solid #4ade80;
    border-radius: 16px;
    display: none;
    flex-direction: column;
    z-index: 1000;
    overflow: hidden;
}
.chatbot-window.open {
    display: flex;
}
.chatbot-header {
    background: #16a34a;
    padding: 12px;
    color: white;
    font-weight: bold;
    display: flex;
    justify-content: space-between;
}
.chatbot-header button {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
}
.chatbot-messages {
    flex: 1;
    overflow-y: auto;
    padding: 12px;
}
.chatbot-messages .user {
    text-align: right;
    color: #4ade80;
    margin: 8px 0;
}
.chatbot-messages .bot {
    text-align: left;
    color: white;
    margin: 8px 0;
}
.chatbot-input {
    display: flex;
    padding: 12px;
    border-top: 1px solid #333;
    gap: 8px;
}
.chatbot-input input {
    flex: 1;
    padding: 8px 12px;
    border-radius: 20px;
    border: 1px solid #4ade80;
    background: #1a2a1a;
    color: white;
    font-family: inherit;
}
.chatbot-input button {
    background: #16a34a;
    border: none;
    border-radius: 20px;
    padding: 8px 16px;
    color: white;
    cursor: pointer;
}
</style>

<button class="chatbot-btn" id="chatbotToggle">💬 Assistant IA</button>
<div class="chatbot-window" id="chatbotWindow">
    <div class="chatbot-header">
        <span>🤖 FoodSave IA</span>
        <button id="chatbotClose">✕</button>
    </div>
    <div class="chatbot-messages" id="chatMessages">
        <div class="bot">👋 Bonjour ! Posez-moi vos questions sur le gaspillage alimentaire, la conservation, les recettes anti-gaspi...</div>
    </div>
    <div class="chatbot-input">
        <input type="text" id="chatInput" placeholder="Votre question...">
        <button id="chatSend">Envoyer</button>
    </div>
</div>

<script>
const toggle = document.getElementById('chatbotToggle');
const close = document.getElementById('chatbotClose');
const windowBot = document.getElementById('chatbotWindow');
const messagesDiv = document.getElementById('chatMessages');
const input = document.getElementById('chatInput');
const send = document.getElementById('chatSend');

toggle.onclick = () => windowBot.classList.add('open');
close.onclick = () => windowBot.classList.remove('open');

function addMessage(text, isUser = false) {
    const div = document.createElement('div');
    div.className = isUser ? 'user' : 'bot';
    div.innerHTML = isUser ? '👤 ' + text : '🤖 ' + text;
    messagesDiv.appendChild(div);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

async function sendMessage() {
    const question = input.value.trim();
    if (!question) return;
    
    addMessage(question, true);
    input.value = '';
    
    try {
        const response = await fetch('index.php?action=chatbot', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'question=' + encodeURIComponent(question)
        });
        const data = await response.json();
        addMessage(data.response);
    } catch (error) {
        addMessage('❌ Erreur, veuillez réessayer.');
    }
}

send.onclick = sendMessage;
input.onkeypress = (e) => { if (e.key === 'Enter') sendMessage(); };
</script>

</body>
</html>