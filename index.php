<?php
session_start();
include "config.php";

$error = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT username, password FROM users WHERE username = ?");
    
    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $success = true;
        } else {
            $error = "Invalid username or password!";
        }

    } else {
        $error = "Invalid username or password!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CENRO LOGIN</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@400;500;600;700&family=Source+Sans+3:wght@300;400;600&display=swap" rel="stylesheet">

<style>
*, *::before, *::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

:root {
    --green-deep:   #1a4d2e;
    --green-mid:    #2d7a4f;
    --green-bright: #3aad6e;
    --green-glow:   #4ede8f;
    --gold:         #c9a84c;
    --gold-light:   #f0cb6e;
    --cream:        #f5f0e8;
    --white:        #ffffff;
    --error:        #e05252;
    --card-bg:      rgba(10, 28, 18, 0.72);
    --border:       rgba(58, 173, 110, 0.3);
    --input-bg:     rgba(255,255,255,0.06);
}

/* ─── BASE ─────────────────────────────────────── */
body {
    font-family: 'Source Sans 3', sans-serif;
    background: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)),
                url('assets/images/cenro.jpeg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
}

/* ─── AMBIENT PARTICLES ─────────────────────────── */
.particles {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}

.particle {
    position: absolute;
    border-radius: 50%;
    background: var(--green-glow);
    opacity: 0;
    animation: drift linear infinite;
}

@keyframes drift {
    0%   { transform: translateY(100vh) scale(0); opacity: 0; }
    10%  { opacity: 0.35; }
    90%  { opacity: 0.2; }
    100% { transform: translateY(-10vh) scale(1.4); opacity: 0; }
}

/* ─── CARD ──────────────────────────────────────── */
.login-container {
    position: relative;
    z-index: 10;
    width: min(420px, 92vw);
    background: var(--card-bg);
    backdrop-filter: blur(22px) saturate(1.4);
    -webkit-backdrop-filter: blur(22px) saturate(1.4);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 42px 40px 36px;
    box-shadow:
        0 0 0 1px rgba(58,173,110,0.08),
        0 32px 64px rgba(0,0,0,0.55),
        0 0 80px rgba(58,173,110,0.07) inset;
    animation: cardIn 0.9s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes cardIn {
    from { opacity: 0; transform: translateY(40px) scale(0.96); }
    to   { opacity: 1; transform: translateY(0)   scale(1); }
}

/* Top accent line */
.login-container::before {
    content: '';
    position: absolute;
    top: 0; left: 10%; right: 10%;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--green-bright), var(--gold), var(--green-bright), transparent);
    border-radius: 0 0 2px 2px;
    animation: shimmer 3s ease infinite;
    background-size: 200% 100%;
}

@keyframes shimmer {
    0%   { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

/* ─── LOGO ──────────────────────────────────────── */
.logo-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 18px;
    animation: logoIn 1s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both;
}

@keyframes logoIn {
    from { opacity: 0; transform: scale(0.7) rotate(-6deg); }
    to   { opacity: 1; transform: scale(1)   rotate(0deg); }
}

.logo-wrap img {
    width: 82px;
    height: 82px;
    object-fit: contain;
    filter: drop-shadow(0 4px 18px rgba(58,173,110,0.5));
    transition: filter 0.4s;
}

.logo-wrap img:hover {
    filter: drop-shadow(0 4px 28px rgba(78,222,143,0.8));
}

/* ─── HEADING ───────────────────────────────────── */
.login-container h2 {
    font-family: 'Rajdhani', sans-serif;
    font-size: 1.75rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    text-align: center;
    color: var(--white);
    margin-bottom: 4px;
    animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.35s both;
}

.subtitle {
    text-align: center;
    font-size: 0.78rem;
    font-weight: 300;
    letter-spacing: 0.06em;
    color: var(--green-bright);
    text-transform: uppercase;
    margin-bottom: 28px;
    animation: fadeUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.45s both;
}

@keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ─── DIVIDER ───────────────────────────────────── */
.divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--border), transparent);
    margin-bottom: 26px;
    animation: fadeUp 0.8s ease 0.5s both;
}

/* ─── ERROR / SUCCESS ───────────────────────────── */
.msg-error {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(224,82,82,0.12);
    border: 1px solid rgba(224,82,82,0.4);
    border-radius: 10px;
    padding: 10px 14px;
    color: #ff8a8a;
    font-size: 0.85rem;
    margin-bottom: 18px;
    animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both, fadeUp 0.4s ease both;
}

.msg-error svg { flex-shrink: 0; }

@keyframes shake {
    10%, 90% { transform: translateX(-3px); }
    20%, 80% { transform: translateX(5px); }
    30%, 50%, 70% { transform: translateX(-4px); }
    40%, 60% { transform: translateX(4px); }
}

.msg-success {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(58,173,110,0.15);
    border: 1px solid rgba(58,173,110,0.5);
    border-radius: 10px;
    padding: 12px 16px;
    color: var(--green-glow);
    font-size: 0.88rem;
    font-weight: 600;
    margin-bottom: 18px;
    animation: fadeUp 0.5s ease both;
}

.checkmark-circle {
    width: 22px; height: 22px;
    stroke-dasharray: 60;
    stroke-dashoffset: 60;
    animation: drawCheck 0.6s ease 0.2s forwards;
}

@keyframes drawCheck {
    to { stroke-dashoffset: 0; }
}

/* ─── INPUTS ────────────────────────────────────── */
.input-group {
    margin-bottom: 18px;
    position: relative;
    animation: fadeUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.input-group:nth-child(1) { animation-delay: 0.55s; }
.input-group:nth-child(2) { animation-delay: 0.65s; }

.input-group label {
    display: block;
    font-family: 'Rajdhani', sans-serif;
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--green-bright);
    margin-bottom: 7px;
    transition: color 0.3s;
}

.input-group:focus-within label {
    color: var(--green-glow);
}

.input-wrap {
    position: relative;
}

.input-wrap svg.input-icon {
    position: absolute;
    left: 13px; top: 50%;
    transform: translateY(-50%);
    color: rgba(58,173,110,0.5);
    transition: color 0.3s;
    pointer-events: none;
}

.input-group input {
    width: 100%;
    background: var(--input-bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 12px 42px 12px 40px;
    color: var(--white);
    font-family: 'Source Sans 3', sans-serif;
    font-size: 0.95rem;
    outline: none;
    transition: border-color 0.3s, background 0.3s, box-shadow 0.3s;
}

.input-group input::placeholder {
    color: rgba(255,255,255,0.22);
    font-size: 0.88rem;
}

.input-group input:focus {
    border-color: var(--green-bright);
    background: rgba(255,255,255,0.09);
    box-shadow: 0 0 0 3px rgba(58,173,110,0.18), 0 0 20px rgba(58,173,110,0.1);
}

.input-group:focus-within svg.input-icon {
    color: var(--green-bright);
}

/* Password toggle */
.toggle-pw {
    position: absolute;
    right: 13px; top: 50%;
    transform: translateY(-50%);
    background: none; border: none;
    cursor: pointer;
    color: rgba(255,255,255,0.35);
    display: flex; align-items: center;
    transition: color 0.3s;
    padding: 2px;
}

.toggle-pw:hover { color: var(--green-bright); }

/* ─── BUTTON ────────────────────────────────────── */
.login-btn {
    width: 100%;
    padding: 13px;
    margin-top: 6px;
    background: linear-gradient(135deg, var(--green-mid), var(--green-deep));
    border: 1px solid rgba(58,173,110,0.4);
    border-radius: 10px;
    color: var(--white);
    font-family: 'Rajdhani', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.3s;
    animation: fadeUp 0.7s cubic-bezier(0.16,1,0.3,1) 0.75s both;
}

.login-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, var(--green-bright), var(--green-mid));
    opacity: 0;
    transition: opacity 0.3s;
}

.login-btn:hover::before { opacity: 1; }

.login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(45,122,79,0.55), 0 0 20px rgba(58,173,110,0.25);
}

.login-btn:active { transform: translateY(0) scale(0.98); }

.login-btn span {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

/* Ripple */
.ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.25);
    transform: scale(0);
    animation: rippleOut 0.6s linear;
    pointer-events: none;
}

@keyframes rippleOut {
    to { transform: scale(4); opacity: 0; }
}

/* ─── LOADER OVERLAY ────────────────────────────── */
.loader-overlay {
    position: fixed;
    inset: 0;
    background: rgba(5,18,10,0.75);
    backdrop-filter: blur(8px);
    display: none;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 20px;
    z-index: 9999;
}

.loader-ring {
    width: 60px; height: 60px;
    border-radius: 50%;
    border: 3px solid rgba(58,173,110,0.15);
    border-top-color: var(--green-glow);
    border-right-color: var(--gold);
    animation: spin 0.9s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.loader-text {
    font-family: 'Rajdhani', sans-serif;
    font-size: 0.85rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--green-bright);
    animation: pulse 1.2s ease infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 0.6; }
    50%       { opacity: 1; }
}

/* Progress bar */
.loader-progress {
    width: 180px;
    height: 2px;
    background: rgba(255,255,255,0.08);
    border-radius: 2px;
    overflow: hidden;
}

.loader-progress-fill {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, var(--green-mid), var(--green-glow));
    border-radius: 2px;
    animation: progressFill 2s ease forwards;
}

@keyframes progressFill {
    to { width: 100%; }
}

/* ─── FOOTER ────────────────────────────────────── */
.footer-text {
    text-align: center;
    font-size: 0.72rem;
    font-weight: 300;
    letter-spacing: 0.04em;
    color: rgba(255,255,255,0.35);
    margin-top: 24px;
    line-height: 1.5;
    animation: fadeUp 0.7s ease 0.85s both;
}

/* ─── CORNER ACCENTS ────────────────────────────── */
.corner {
    position: absolute;
    width: 16px; height: 16px;
    border-color: var(--gold);
    border-style: solid;
    opacity: 0.5;
}

.corner-tl { top: 12px; left: 12px; border-width: 2px 0 0 2px; }
.corner-tr { top: 12px; right: 12px; border-width: 2px 2px 0 0; }
.corner-bl { bottom: 12px; left: 12px; border-width: 0 0 2px 2px; }
.corner-br { bottom: 12px; right: 12px; border-width: 0 2px 2px 0; }
</style>
</head>
<body>

<!-- Ambient particles -->
<div class="particles" id="particles"></div>

<!-- ─── LOGIN CARD ─── -->
<div class="login-container">
    <!-- Corner accents -->
    <div class="corner corner-tl"></div>
    <div class="corner corner-tr"></div>
    <div class="corner corner-bl"></div>
    <div class="corner corner-br"></div>

    <!-- Logo -->
    <div class="logo-wrap">
        <img src="assets/images/denr remv bg.png" alt="DENR Logo">
    </div>

    <h2>Admin Login</h2>
    <p class="subtitle">CENRO &mdash; Manolo Fortich</p>

    <div class="divider"></div>

    <!-- Error message -->
    <?php if (!empty($error)): ?>
        <div class="msg-error">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Success message -->
    <?php if ($success): ?>
        <div class="msg-success">
            <svg class="checkmark-circle" viewBox="0 0 24 24" fill="none" stroke="var(--green-glow)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M7 13l3 3 7-7"/>
            </svg>
            Login successful! Redirecting&hellip;
        </div>

        <!-- Loader overlay -->
        <div class="loader-overlay" id="loader">
            <div class="loader-ring"></div>
            <div class="loader-progress">
                <div class="loader-progress-fill"></div>
            </div>
            <div class="loader-text">Loading dashboard</div>
        </div>

        <script>
            document.getElementById("loader").style.display = "flex";
            setTimeout(function () {
                window.location.href = "dashboard.php";
            }, 2200);
        </script>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" id="loginForm">
        <div class="input-group">
            <label for="username">Username</label>
            <div class="input-wrap">
                <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                <input type="text" id="username" name="username" placeholder="Enter username" autocomplete="username" required>
            </div>
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <div class="input-wrap">
                <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input type="password" id="password" name="password" placeholder="Enter password" autocomplete="current-password" required>
                <button type="button" class="toggle-pw" id="togglePw" aria-label="Show password">
                    <svg id="eyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit" class="login-btn" id="loginBtn">
            <span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                Sign In
            </span>
        </button>
    </form>

    <div class="footer-text">
        Department of Environment and Natural Resources<br>Manolo Fortich, Bukidnon
    </div>
</div>

<script>
/* ─── PARTICLES ─── */
(function () {
    const container = document.getElementById('particles');
    const count = 22;
    for (let i = 0; i < count; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        const size = Math.random() * 5 + 2;
        p.style.cssText = `
            width:${size}px; height:${size}px;
            left:${Math.random() * 100}%;
            animation-duration:${6 + Math.random() * 10}s;
            animation-delay:${Math.random() * 12}s;
        `;
        container.appendChild(p);
    }
})();

/* ─── RIPPLE EFFECT ─── */
document.getElementById('loginBtn').addEventListener('click', function (e) {
    const btn = this;
    const rect = btn.getBoundingClientRect();
    const ripple = document.createElement('span');
    ripple.className = 'ripple';
    const size = Math.max(rect.width, rect.height);
    ripple.style.cssText = `
        width:${size}px; height:${size}px;
        left:${e.clientX - rect.left - size/2}px;
        top:${e.clientY - rect.top - size/2}px;
    `;
    btn.appendChild(ripple);
    ripple.addEventListener('animationend', () => ripple.remove());
});

/* ─── PASSWORD TOGGLE ─── */
const togglePw = document.getElementById('togglePw');
const pwInput  = document.getElementById('password');
const eyeIcon  = document.getElementById('eyeIcon');

const eyeOpen   = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
const eyeClosed = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>`;

togglePw.addEventListener('click', function () {
    const isHidden = pwInput.type === 'password';
    pwInput.type = isHidden ? 'text' : 'password';
    eyeIcon.innerHTML = isHidden ? eyeClosed : eyeOpen;
});

/* ─── BUTTON LOADING STATE ─── */
document.getElementById('loginForm').addEventListener('submit', function () {
    const btn = document.getElementById('loginBtn');
    btn.disabled = true;
    btn.querySelector('span').innerHTML = `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin .8s linear infinite">
            <circle cx="12" cy="12" r="10" stroke-opacity=".25"/><path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
        </svg>
        Verifying…
    `;
    const style = document.createElement('style');
    style.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
    document.head.appendChild(style);
});
</script>

</body>
</html>