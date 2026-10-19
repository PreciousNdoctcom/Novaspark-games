"""
Draws the site artwork as SVG: game covers, the hero background, team avatars and the favicon.
The output is committed in assets/img, so XAMPP needs nothing extra. The Vercel build runs it
again to save uploading the images. Usage: python3 tools/generate-art.py assets/img
"""
import random, math, sys, os, re, glob
out = sys.argv[1]
os.makedirs(os.path.join(out, "games"), exist_ok=True)
os.makedirs(os.path.join(out, "team"), exist_ok=True)
W, H = 1600, 900

def stars(r, n, w=W, h=H, maxr=2.2, color="#e2f4e5"):
    groups = {}
    for _ in range(n):
        op = round(r.uniform(.25, .9), 1)
        groups.setdefault(op, []).append(f'<circle cx="{r.uniform(0, w):.0f}" cy="{r.uniform(0, h):.0f}" r="{r.uniform(0.4, maxr):.1f}"/>')
    return "".join(f'<g fill="{color}" opacity="{op}">' + "".join(c) + "</g>" for op, c in groups.items())

def svg(body, defs="", bg1="#0b1e0f", bg2="#071409", w=W, h=H):
    return f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {w} {h}" width="{w}" height="{h}" preserveAspectRatio="xMidYMid slice">
<defs><linearGradient id="bg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="{bg1}"/><stop offset="1" stop-color="{bg2}"/></linearGradient>
<filter id="blur" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="60"/></filter>
<filter id="glow" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="8" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
{defs}</defs>
<rect width="{w}" height="{h}" fill="url(#bg)"/>
{body}
<rect width="{w}" height="{h}" fill="url(#vig)" opacity="0"/>
</svg>'''

def void_protocol(r):
    b = []
    for i in range(0, W, 80):
        b.append(f'<path d="M{i} 0V{H}" stroke="#5dfc8d" stroke-opacity=".06"/>')
    for j in range(0, H, 80):
        b.append(f'<path d="M0 {j}H{W}" stroke="#5dfc8d" stroke-opacity=".06"/>')
    for _ in range(26):
        x, y = r.randrange(0, W, 80), r.randrange(0, H, 80)
        pts = [(x, y)]
        for _ in range(r.randint(2, 5)):
            if r.random() < .5: x += r.choice([-1, 1]) * 80 * r.randint(1, 4)
            else: y += r.choice([-1, 1]) * 80 * r.randint(1, 3)
            pts.append((x, y))
        d = "M" + " L".join(f"{a} {c}" for a, c in pts)
        b.append(f'<path d="{d}" fill="none" stroke="#5dfc8d" stroke-width="3" stroke-opacity=".45"/><circle cx="{pts[-1][0]}" cy="{pts[-1][1]}" r="7" fill="#5dfc8d" opacity=".8"/>')
    b.append('<circle cx="1050" cy="420" r="300" fill="#5dfc8d" opacity=".18" filter="url(#blur)"/>')
    for k, rr in enumerate([260, 200, 140]):
        b.append(f'<circle cx="1050" cy="420" r="{rr}" fill="none" stroke="#5dfc8d" stroke-width="{2+k}" stroke-dasharray="{20+k*10} {14}" opacity=".7"/>')
    b.append('<circle cx="1050" cy="420" r="70" fill="#071409" stroke="#fbbf24" stroke-width="6" filter="url(#glow)"/><circle cx="1050" cy="420" r="26" fill="#fbbf24"/>')
    return svg("".join(b), bg1="#0d2615", bg2="#04100a")

def nebula_drift(r):
    b = [stars(r, 140)]
    for c, x, y, s in [("#7c3aed", 400, 300, 380), ("#5dfc8d", 1100, 520, 320), ("#fbbf24", 800, 200, 240), ("#0ea5e9", 1300, 220, 260)]:
        b.append(f'<ellipse cx="{x}" cy="{y}" rx="{s*1.4:.0f}" ry="{s:.0f}" fill="{c}" opacity=".32" filter="url(#blur)"/>')
    for _ in range(40):
        y = r.uniform(200, 800); x = r.uniform(-200, 1400); L = r.uniform(150, 600)
        b.append(f'<path d="M{x:.0f} {y:.0f} l{L:.0f} {-L*0.25:.0f}" stroke="#e2f4e5" stroke-width="{r.uniform(1,3):.1f}" stroke-linecap="round" opacity="{r.uniform(.15,.6):.2f}"/>')
    b.append('<g transform="translate(980 470) rotate(-14)" filter="url(#glow)"><path d="M0 0 L140 -18 L170 0 L140 18 Z" fill="#e2f4e5"/><path d="M40 -10 L90 -50 L110 -14 Z M40 10 L90 50 L110 14 Z" fill="#5dfc8d"/><path d="M0 -8 L-160 0 L0 8 Z" fill="#fbbf24" opacity=".9"/></g>')
    for i in range(7):
        x = r.uniform(100, 1500); y = r.uniform(600, 880); s = r.uniform(30, 90)
        b.append(f'<path d="M{x:.0f} {y:.0f} l{s:.0f} {-s*0.4:.0f} l{s*0.6:.0f} {s*0.7:.0f} l{-s*0.9:.0f} {s*0.5:.0f} z" fill="#14301c" stroke="#3d6b48" stroke-width="2"/>')
    return svg("".join(b), bg1="#0a0f24", bg2="#071409")

def iron_circuit(r):
    s = 60
    hexpts = lambda x, y: " ".join(f"{x + s*math.cos(math.radians(a)):.0f},{y + s*math.sin(math.radians(a)):.0f}" for a in range(0, 360, 60))
    pat = f'<pattern id="hex" width="180" height="{s*1.732*2:.0f}" patternUnits="userSpaceOnUse"><polygon points="{hexpts(0,0)}" fill="none" stroke="#5dfc8d" stroke-opacity=".1"/><polygon points="{hexpts(90, s*.866)}" fill="none" stroke="#5dfc8d" stroke-opacity=".1"/><polygon points="{hexpts(0, s*1.732)}" fill="none" stroke="#5dfc8d" stroke-opacity=".1"/><polygon points="{hexpts(180,0)}" fill="none" stroke="#5dfc8d" stroke-opacity=".1"/><polygon points="{hexpts(180, s*1.732)}" fill="none" stroke="#5dfc8d" stroke-opacity=".1"/></pattern>'
    b = [f'<defs>{pat}</defs><rect width="{W}" height="{H}" fill="url(#hex)"/>']
    for _ in range(26):
        col, row = r.randrange(0, 18), r.randrange(0, 9)
        x = col * 90; y = row * s * 1.732 + (col % 2) * s * .866
        b.append(f'<polygon points="{hexpts(x,y)}" fill="#5dfc8d" fill-opacity="{r.uniform(.04,.14):.2f}" stroke="#5dfc8d" stroke-opacity="{r.uniform(.2,.5):.2f}"/>')
    b.append('<circle cx="800" cy="520" r="360" fill="#fbbf24" opacity=".16" filter="url(#blur)"/>')
    b.append('''<g transform="translate(800 470)" filter="url(#glow)">
<rect x="-110" y="-150" width="220" height="170" rx="10" fill="#14301c" stroke="#5dfc8d" stroke-width="5"/>
<rect x="-60" y="-110" width="120" height="34" rx="4" fill="#fbbf24"/>
<path d="M-110 -90 h-90 v120 M110 -90 h90 v120" fill="none" stroke="#5dfc8d" stroke-width="18" stroke-linecap="round"/>
<path d="M-60 20 l-50 220 M60 20 l50 220" stroke="#5dfc8d" stroke-width="26" stroke-linecap="round"/>
<path d="M-150 250 h90 M60 250 h90" stroke="#e2f4e5" stroke-width="14" stroke-linecap="round"/>
<rect x="-30" y="-200" width="60" height="50" rx="6" fill="#1b3d25" stroke="#5dfc8d" stroke-width="4"/></g>''')
    return svg("".join(b), bg1="#10200f", bg2="#060d07")

def stellar_abyss(r):
    b = []
    for i in range(9):
        x = 300 + i * 140 + r.uniform(-40, 40)
        b.append(f'<path d="M{x:.0f} 0 L{x-220:.0f} {H} L{x-60:.0f} {H} Z" fill="#5dfc8d" opacity="{r.uniform(.03,.08):.2f}"/>')
    b.append('<ellipse cx="800" cy="-60" rx="900" ry="260" fill="#5dfc8d" opacity=".25" filter="url(#blur)"/>')
    for _ in range(90):
        x, y, rad = r.uniform(0, W), r.uniform(0, H), r.uniform(2, 14)
        b.append(f'<circle cx="{x:.0f}" cy="{y:.0f}" r="{rad:.1f}" fill="none" stroke="#e2f4e5" stroke-opacity="{r.uniform(.15,.5):.2f}" stroke-width="1.5"/>')
    for k in range(5):
        x = 200 + k * 300
        d = f"M{x} {H} " + " ".join(f"Q{x + r.uniform(-60,60):.0f} {H - 80*(j+.5):.0f} {x + r.uniform(-30,30):.0f} {H - 80*(j+1):.0f}" for j in range(r.randint(3, 6)))
        b.append(f'<path d="{d}" fill="none" stroke="#22c55e" stroke-width="10" stroke-linecap="round" opacity=".5"/>')
    b.append('<g filter="url(#glow)"><ellipse cx="1120" cy="560" rx="60" ry="40" fill="#fbbf24" opacity=".85"/><path d="M1060 570 q-40 60 -10 120 M1090 590 q-10 70 20 110 M1150 590 q20 60 0 120" stroke="#fbbf24" stroke-width="6" fill="none" opacity=".7"/></g>')
    return svg("".join(b), bg1="#0a2a2a", bg2="#020a0b")

def shadow_nova(r):
    b = ['<circle cx="1150" cy="260" r="170" fill="#fbbf24" opacity=".3" filter="url(#blur)"/>', '<circle cx="1150" cy="260" r="110" fill="none" stroke="#fbbf24" stroke-width="3" opacity=".8"/>']
    for layer, (col, base, op) in enumerate([("#14301c", 420, 1), ("#0f2416", 520, 1), ("#071409", 640, 1)]):
        x = -20
        while x < W:
            w = r.uniform(60, 160); h = r.uniform(120, 420) - layer * 40
            y = base - h + layer * 60
            b.append(f'<rect x="{x:.0f}" y="{y:.0f}" width="{w:.0f}" height="{H-y:.0f}" fill="{col}"/>')
            for _ in range(int(w * h / 5000)):
                wx = x + r.uniform(8, w - 14); wy = y + r.uniform(10, h)
                c = r.choice(["#5dfc8d", "#fbbf24", "#e2f4e5", "#f472b6"])
                b.append(f'<rect x="{wx:.0f}" y="{wy:.0f}" width="6" height="10" fill="{c}" opacity="{r.uniform(.2,.9):.2f}"/>')
            x += w + r.uniform(0, 20)
    b.append('<path d="M0 760 H1600" stroke="#5dfc8d" stroke-width="4" opacity=".6" filter="url(#glow)"/>')
    b.append('<g transform="translate(420 520)" filter="url(#glow)"><path d="M0 -120 c22 0 30 18 30 34 c0 18 -10 30 -30 30 c-20 0 -30 -12 -30 -30 c0 -16 8 -34 30 -34 z M-40 -50 h80 l20 140 h-30 l-10 140 h-50 l-10 -140 h-30 z" fill="#020604"/><path d="M-18 -94 h36" stroke="#5dfc8d" stroke-width="5"/></g>')
    return svg("".join(b), bg1="#1a0f24", bg2="#050a06")

def echoes_of_arun(r):
    b = [stars(r, 140, maxr=1.8)]
    b.append('<circle cx="380" cy="230" r="110" fill="#fbbf24" opacity=".85" filter="url(#glow)"/><circle cx="380" cy="230" r="260" fill="#fbbf24" opacity=".18" filter="url(#blur)"/>')
    for cx, cy, s in [(900, 430, 1.0), (1330, 300, .6), (520, 620, .7)]:
        b.append(f'<g transform="translate({cx} {cy}) scale({s})"><path d="M-260 0 H260 L160 90 L60 220 L0 300 L-80 200 L-180 90 Z" fill="#14301c" stroke="#3d6b48" stroke-width="3"/><path d="M-260 0 H260" stroke="#5dfc8d" stroke-width="6"/>'
                 f'<path d="M-150 0 V-150 A60 60 0 0 1 -30 -150 V0 M30 0 V-210 A70 70 0 0 1 170 -210 V0" fill="none" stroke="#89b895" stroke-width="16"/>'
                 f'<circle cx="100" cy="-160" r="18" fill="#5dfc8d" filter="url(#glow)"/></g>')
    for _ in range(30):
        x, y = r.uniform(600, 1400), r.uniform(150, 700)
        b.append(f'<path d="M{x:.0f} {y:.0f} l6 -14 l6 14 l-6 14 z" fill="#5dfc8d" opacity="{r.uniform(.3,.9):.2f}"/>')
    return svg("".join(b), bg1="#1c1730", bg2="#0b1e0f")

def quantum_breach(r):
    b = ['<circle cx="800" cy="450" r="420" fill="#5dfc8d" opacity=".12" filter="url(#blur)"/>']
    for k in range(9):
        rr = 60 + k * 50
        dash = r.choice(["4 10", "30 12", "80 30", "2 6", "120 40"])
        rot = r.uniform(0, 360)
        b.append(f'<circle cx="800" cy="450" r="{rr}" fill="none" stroke="{"#fbbf24" if k%3==0 else "#5dfc8d"}" stroke-width="{r.uniform(2,8):.0f}" stroke-dasharray="{dash}" opacity="{.25+.07*(9-k):.2f}" transform="rotate({rot:.0f} 800 450)"/>')
    for a in range(0, 360, 30):
        x2 = 800 + 520 * math.cos(math.radians(a)); y2 = 450 + 520 * math.sin(math.radians(a))
        b.append(f'<path d="M800 450 L{x2:.0f} {y2:.0f}" stroke="#5dfc8d" stroke-opacity=".08" stroke-width="2"/>')
    b.append('<g filter="url(#glow)"><path d="M800 450 L800 250" stroke="#e2f4e5" stroke-width="8" stroke-linecap="round"/><path d="M800 450 L940 520" stroke="#fbbf24" stroke-width="8" stroke-linecap="round"/><circle cx="800" cy="450" r="20" fill="#e2f4e5"/></g>')
    for _ in range(12):
        x, y, w = r.uniform(0, W), r.uniform(0, H), r.uniform(40, 200)
        b.append(f'<rect x="{x:.0f}" y="{y:.0f}" width="{w:.0f}" height="{w*.6:.0f}" fill="none" stroke="#89b895" stroke-opacity=".25" stroke-width="2" transform="rotate({r.uniform(-30,30):.0f} {x:.0f} {y:.0f})"/>')
    return svg("".join(b), bg1="#0e1d22", bg2="#050b0c")

def glitch_city(r):
    b = []
    x = 0
    while x < W:
        w = r.uniform(50, 140); h = r.uniform(250, 700); y = H - h
        b.append(f'<rect x="{x:.0f}" y="{y:.0f}" width="{w:.0f}" height="{h:.0f}" fill="#14301c" stroke="#1b3d25"/>')
        for wy in range(int(y) + 20, H, 26):
            if r.random() < .55:
                b.append(f'<rect x="{x+8:.0f}" y="{wy}" width="{w-16:.0f}" height="6" fill="{r.choice(["#5dfc8d","#f472b6","#22d3ee","#fbbf24"])}" opacity="{r.uniform(.15,.6):.2f}"/>')
        x += w + 4
    for _ in range(22):
        y = r.uniform(0, H); h = r.uniform(4, 40); off = r.uniform(-80, 80)
        c = r.choice(["#f472b6", "#22d3ee", "#5dfc8d"])
        b.append(f'<rect x="{off:.0f}" y="{y:.0f}" width="{W}" height="{h:.0f}" fill="{c}" opacity="{r.uniform(.06,.22):.2f}"/>')
    b.append('<g filter="url(#glow)"><rect x="560" y="300" width="480" height="140" fill="none" stroke="#f472b6" stroke-width="6"/><rect x="572" y="292" width="480" height="140" fill="none" stroke="#22d3ee" stroke-width="4" opacity=".8"/><path d="M620 370 h120 M780 370 h200" stroke="#fbbf24" stroke-width="14" stroke-linecap="round"/></g>')
    return svg("".join(b), bg1="#1d0b26", bg2="#060a10")

def binary_horizon(r):
    b = [stars(r, 160)]
    b.append('<circle cx="800" cy="1650" r="1050" fill="#5dfc8d" opacity=".35" filter="url(#blur)"/>')
    b.append('<circle cx="800" cy="1650" r="1000" fill="#0b1e0f" stroke="#5dfc8d" stroke-width="5"/>')
    for k in range(1, 7):
        b.append(f'<ellipse cx="800" cy="1650" rx="{1000 - k*20}" ry="{1000 - k*8}" fill="none" stroke="#5dfc8d" stroke-opacity="{.25 - k*.03:.2f}" stroke-width="2"/>')
    b.append('<circle cx="1220" cy="230" r="60" fill="#fbbf24" filter="url(#glow)"/><circle cx="1330" cy="300" r="26" fill="#e2f4e5" filter="url(#glow)"/>')
    for _ in range(40):
        x, y = r.uniform(50, 1550), r.uniform(40, 520)
        b.append(f'<text x="{x:.0f}" y="{y:.0f}" font-family="monospace" font-size="{r.choice([14,18,22])}" fill="#5dfc8d" opacity="{r.uniform(.1,.4):.2f}">{"".join(r.choice("01") for _ in range(r.randint(4,10)))}</text>')
    b.append('<path d="M300 520 Q 800 120 1220 230" fill="none" stroke="#e2f4e5" stroke-width="2" stroke-dasharray="6 10" opacity=".5"/>')
    return svg("".join(b), bg1="#050b1a", bg2="#0b1e0f")

def fallback(r):
    b = [stars(r, 120, maxr=1.5), '<circle cx="800" cy="450" r="300" fill="#5dfc8d" opacity=".12" filter="url(#blur)"/>',
         '<g transform="translate(800 450)" stroke="#5dfc8d" stroke-width="10" stroke-linecap="round" opacity=".7"><path d="M0 -110v70M0 40v70M-110 0h70M40 0h70M-78 -78l45 45M33 33l45 45M78 -78l-45 45M-33 33l-45 45"/></g>']
    return svg("".join(b))

def hero(r):
    b = [stars(r, 160, w=1920, h=1080)]
    b.append('<ellipse cx="600" cy="300" rx="700" ry="220" fill="#5dfc8d" opacity=".22" filter="url(#blur)" transform="rotate(-12 600 300)"/>')
    b.append('<ellipse cx="1400" cy="380" rx="600" ry="180" fill="#fbbf24" opacity=".14" filter="url(#blur)" transform="rotate(10 1400 380)"/>')
    for k, (col, base) in enumerate([("#14301c", 760), ("#0f2416", 860), ("#071409", 960)]):
        pts = [(0, 1080)]
        x = 0
        while x <= 1920:
            pts.append((x, base - r.uniform(40, 200) + k * 10)); x += r.uniform(80, 200)
        pts.append((1920, base - 80)); pts.append((1920, 1080))
        b.append(f'<path d="M{" L".join(f"{a:.0f} {c:.0f}" for a,c in pts)} Z" fill="{col}"/>')
    b.append('<circle cx="1500" cy="220" r="90" fill="none" stroke="#5dfc8d" stroke-width="2" opacity=".5"/><circle cx="1500" cy="220" r="60" fill="#14301c" stroke="#5dfc8d" stroke-width="3"/>')
    return svg("".join(b), bg1="#081a0d", bg2="#0b1e0f", w=1920, h=1080)

games = {"void-protocol": void_protocol, "nebula-drift": nebula_drift, "iron-circuit": iron_circuit, "stellar-abyss": stellar_abyss,
         "shadow-nova": shadow_nova, "echoes-of-arun": echoes_of_arun, "quantum-breach": quantum_breach,
         "glitch-city-hustle": glitch_city, "binary-horizon": binary_horizon, "fallback": fallback}
for i, (slug, fn) in enumerate(games.items()):
    open(os.path.join(out, "games", slug + ".svg"), "w").write(fn(random.Random(i + 7)))
open(os.path.join(out, "hero.svg"), "w").write(hero(random.Random(99)))

team = [("pn", "PN", "#5dfc8d", "#fbbf24"), ("er", "ER", "#fbbf24", "#f472b6"), ("mt", "MT", "#22d3ee", "#5dfc8d")]
for slug, ini, c1, c2 in team:
    open(os.path.join(out, "team", slug + ".svg"), "w").write(f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" width="256" height="256"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="{c1}"/><stop offset="1" stop-color="{c2}"/></linearGradient><radialGradient id="r" cx=".3" cy=".25" r=".9"><stop offset="0" stop-color="#1b3d25"/><stop offset="1" stop-color="#071409"/></radialGradient></defs><rect width="256" height="256" fill="url(#r)"/><circle cx="128" cy="128" r="96" fill="none" stroke="url(#g)" stroke-width="3" opacity=".5"/><text x="128" y="150" text-anchor="middle" font-family="Georgia, 'Times New Roman', serif" font-weight="900" font-size="76" fill="url(#g)">{ini}</text></svg>''')

open(os.path.join(out, "favicon.svg"), "w").write('''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#5dfc8d"/><stop offset="1" stop-color="#fbbf24"/></linearGradient></defs><rect width="64" height="64" rx="8" fill="#071409"/><path d="M18 48V16h4l20 22V16h4v32h-4L22 26v22z" fill="url(#g)"/></svg>''')
# Compact the markup: shorter opacity values, no whitespace between tags.
for f in glob.glob(os.path.join(out, "**", "*.svg"), recursive=True):
    t = open(f).read()
    t = re.sub(r'opacity="(0?\.\d)\d+"', r'opacity="\1"', t)
    t = re.sub(r'>\s+<', '><', t)
    open(f, "w").write(t)
print("Artwork written to", out)
