<?php
// =======================
// DATA FORM
// =======================
$statesStr   = $_POST['states']   ?? '';
$alphabetStr = $_POST['alphabet'] ?? '';
$start       = $_POST['start']    ?? '';
$finalStr    = $_POST['final']    ?? '';
$numStates   = $_POST['num_states'] ?? '';
$inputString = $_POST['input_string'] ?? '';

$states = [];
if ($numStates && is_numeric($numStates) && $numStates > 0) {
    for ($i = 0; $i < $numStates; $i++) {
        $states[] = "q$i";
    }
    $statesStr = implode(',', $states);
} elseif ($statesStr) {
    $states = array_map('trim', explode(',', $statesStr));
}

$alphabet = $alphabetStr ? array_map('trim', explode(',', $alphabetStr)) : ['a','b'];
$final = $finalStr ? array_map('trim', explode(',', $finalStr)) : [];

// =======================
// TRANSISI AMAN
// =======================
$edges = [];
if (!empty($_POST['trans']) && is_array($_POST['trans'])) {
    foreach ($_POST['trans'] as $from => $row) {
        if(!is_array($row)) continue;
        foreach ($row as $sym => $to) {
            if ($to === '') continue;
            foreach (array_map('trim', explode(',', $to)) as $target) {
                if ($target === '') continue;
                if (!isset($edges[$from])) $edges[$from] = [];
                if (!isset($edges[$from][$target])) $edges[$from][$target] = [];
                $edges[$from][$target][] = $sym;
            }
        }
    }
}

// =======================
// POSISI STATE UNTUK DIAGRAM
// =======================
$pos = [];
$R = 30;
$cols = ceil(count($states)/2);
$gapX = 220;
$gapY = 220;
$startX = 200;
$startY = 180;

foreach ($states as $i => $s) {
    $row = intdiv($i, $cols);
    $col = $i % $cols;
    $pos[$s] = [
        $startX + $col*$gapX,
        $startY + $row*$gapY
    ];
}

// =======================
// CEK STRING DITERIMA ATAU TIDAK
// =======================
$resultString = '';
if($inputString !== '' && $start !== '') {
    $currentStates = [$start];
    $chars = str_split($inputString);
    foreach($chars as $c){
        $nextStates = [];
        foreach($currentStates as $cs){
            foreach($edges[$cs] ?? [] as $to => $syms){
                if(in_array($c, $syms)){
                    $nextStates[] = $to;
                }
            }
        }
        $currentStates = array_unique($nextStates);
    }
    $accepted = false;
    foreach($currentStates as $cs){
        if(in_array($cs, $final)){
            $accepted = true;
            break;
        }
    }
    $resultString = $accepted ? "DITERIMA" : "DITOLAK";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>FSA Visualizer</title>
<style>
:root {
    --primary:#3b82f6;
    --primary-soft:#dbeafe;
    --bg:#f0f4ff;
    --card:#ffffff;
    --border:#cbd5e1;
    --text:#1e3a8a;
    --accept:#16a34a;
    --reject:#dc2626;
}

body {
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    color: var(--text);
    margin:0;
    padding:0;
}

.container {
    max-width:1400px;
    margin:auto;
    padding:30px 20px;
}

h1 {
    text-align:center;
    font-size:2.2em;
    color: var(--primary);
    margin-bottom:30px;
}

.grid {
    display:grid;
    grid-template-columns: 450px 1fr;
    gap:30px;
}

.card {
    background: var(--card);
    border-radius:15px;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
    padding:25px;
}

.card h2 {
    font-size:1.4em;
    color: var(--primary);
    margin-bottom:20px;
    border-bottom:2px solid var(--primary-soft);
    padding-bottom:8px;
}

label {
    display:block;
    font-weight:600;
    margin-bottom:6px;
    margin-top:15px;
}

/* Kotak input seragam dan rapi */
input[type="text"], input[type="number"] {
    width: 100%;
    padding: 10px 12px;
    border-radius:8px;
    border:1px solid var(--border);
    background:#f8faff;
    font-size:14px;
    transition:all 0.2s;
}

input:focus {
    outline:none;
    border-color: var(--primary);
    box-shadow:0 0 0 3px rgba(59,130,246,0.2);
}

/* Kotak jumlah state kecil dan center */
input[name="num_states"] {
    width: 100px;
    padding: 8px 10px;
    text-align: center;
    font-weight:600;
}

/* Button */
button {
    margin-top:20px;
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    background:linear-gradient(135deg,#3b82f6,#60a5fa);
    color:white;
    font-weight:600;
    font-size:15px;
    cursor:pointer;
    transition:0.2s;
}

button:hover {
    transform:translateY(-2px);
    box-shadow:0 6px 16px rgba(59,130,246,0.35);
}

/* ===== TABEL TRANSISI ===== */
.table-container {
    overflow-x: auto;
    margin-top: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    min-width: 400px;
}

th, td {
    padding: 6px 8px;
    text-align: center;
    font-size: 13px;
    min-width: 60px;
}

th {
    background: var(--primary-soft);
    color: var(--primary);
    font-weight: 700;
}

td {
    background:#f8faff;
}

td input {
    padding:5px 6px;
    font-size:13px;
    width:100%;
    box-sizing:border-box;
    border-radius:6px;
    border:1px solid var(--border);
}

.diagram-card {
    overflow:auto;
}

svg {
    background: #ffffff;
    border-radius:15px;
    border:1px solid var(--border);
}

.result {
    margin-top:20px;
    font-size:18px;
    font-weight:700;
}

.accept { color: var(--accept); }
.reject { color: var(--reject); }

@media(max-width:1100px){
    .grid {
        grid-template-columns:1fr;
    }
}
</style>
</head>
<body>

<div class="container">
<h1>Finite State Automata Visualizer</h1>
<div class="grid">

<!-- FORM KONFIGURASI -->
<div class="card">
<h2>Konfigurasi Automata</h2>
<form method="post">
<label>Jumlah State</label>
<input type="number" name="num_states" value="<?= htmlspecialchars($numStates) ?>" min="1">

<label>States (dipisah koma)</label>
<input name="states" value="<?= htmlspecialchars($statesStr) ?>">

<label>Alphabet (dipisah koma)</label>
<input name="alphabet" value="<?= htmlspecialchars($alphabetStr) ?>">

<label>Start State</label>
<input name="start" value="<?= htmlspecialchars($start) ?>">

<label>Final State (dipisah koma)</label>
<input name="final" value="<?= htmlspecialchars($finalStr) ?>">

<?php if(!empty($states)): ?>
<h2>Tabel Transisi</h2>
<div class="table-container">
<table>
<tr>
<th>State</th>
<?php foreach($alphabet as $a): ?>
<th><?= htmlspecialchars($a) ?></th>
<?php endforeach; ?>
</tr>

<?php foreach($states as $s): ?>
<tr>
<td><?= htmlspecialchars($s) ?></td>
<?php foreach($alphabet as $a): ?>
<td>
<input name="trans[<?= htmlspecialchars($s) ?>][<?= htmlspecialchars($a) ?>]" 
       value="<?= $_POST['trans'][$s][$a] ?? '' ?>">
</td>
<?php endforeach; ?>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<label>Input String</label>
<input type="text" name="input_string" value="<?= htmlspecialchars($inputString) ?>" placeholder="Contoh: abba">

<button type="submit">Proses Automata</button>
</form>

<?php if($resultString): ?>
<div class="result <?= $resultString === "DITERIMA" ? 'accept' : 'reject' ?>">
String <strong><?= htmlspecialchars($inputString) ?></strong> : <?= $resultString ?>
</div>
<?php endif; ?>

</div>

<!-- DIAGRAM -->
<?php if(!empty($edges)): ?>
<div class="card diagram-card">
<h2>Diagram Automata</h2>
<svg width="1200" height="850" viewBox="0 0 1200 900">
<defs>
<marker id="arrow" markerWidth="12" markerHeight="12"
refX="10" refY="6" orient="auto">
<polygon points="0 0,12 6,0 12" fill="#1e40af"/>
</marker>
</defs>

<?php
foreach ($edges as $from => $targets) {
    if(!isset($pos[$from])) continue;
    foreach ($targets as $to => $symbols) {
        if(!isset($pos[$to])) continue;
        [$x1,$y1] = $pos[$from];
        [$x2,$y2] = $pos[$to];
        $label = implode(',', $symbols);

        if ($from === $to) {
            echo "<path d='M ".($x1-$R)." ".($y1-$R)." C ".($x1-90)." ".($y1-150)." ".($x1+90)." ".($y1-150)." ".($x1+$R)." ".($y1-$R)."' fill='none' stroke='#1e40af' stroke-width='2.2' marker-end='url(#arrow)'/>
            <text x='$x1' y='".($y1-165)."' text-anchor='middle' fill='#1e40af' font-weight='600'>$label</text>";
            continue;
        }

        $dx=$x2-$x1; $dy=$y2-$y1;
        $len=sqrt($dx*$dx+$dy*$dy);
        $ux=$dx/$len; $uy=$dy/$len;
        $sx=$x1+$ux*$R; $sy=$y1+$uy*$R;
        $ex=$x2-$ux*$R; $ey=$y2-$uy*$R;
        $curve = ($from < $to) ? -70 : 70;
        $cx = ($sx+$ex)/2 - $uy*$curve;
        $cy = ($sy+$ey)/2 + $ux*$curve;

        echo "<path d='M $sx $sy Q $cx $cy $ex $ey' fill='none' stroke='#1e40af' stroke-width='2.2' marker-end='url(#arrow)'/>
        <text x='$cx' y='".($cy-12)."' text-anchor='middle' fill='#1e40af' font-weight='600'>$label</text>";
    }
}

foreach($states as $s){
    if(!isset($pos[$s])) continue;
    [$x,$y] = $pos[$s];
    $strokeColor = ($s === $start) ? '#16a34a' : '#1d4ed8'; // hijau untuk start
    echo "<circle cx='$x' cy='$y' r='$R' fill='#ffffff' stroke='$strokeColor' stroke-width='2.5'/>";
    if(in_array($s,$final)){
        echo "<circle cx='$x' cy='$y' r='".($R-6)."' fill='none' stroke='#dc2626' stroke-width='2.5'/>";
    }
    echo "<text x='$x' y='".($y+5)."' text-anchor='middle' fill='#1e40af' font-weight='600'>$s</text>";
}
?>
</svg>
</div>
<?php endif; ?>

</div>
</div>
</body>
</html>
