<?php
// characters/edit.php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$cid    = (int)($_GET['id'] ?? 0);
$errors = [];

// Fetch character & enforce access
$params = ['cid' => $cid];
$sql = 'SELECT * FROM characters WHERE character_id = :cid';
if ($_SESSION['role'] !== 'admin') {
    $sql           .= ' AND user_id = :u';
    $params['u']    = $_SESSION['user_id'];
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ch = $stmt->fetch();
if (! $ch) {
    echo '<p>Not found or access denied.</p>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Prefill fields
$campaignId  = $ch['campaign_id'];
$name        = $ch['name'];
$race        = $ch['race'];
$charClass   = $ch['character_class'];
$level       = $ch['level'];
$background  = $ch['background'];
$alignment   = $ch['alignment'];
$armorClass  = $ch['armor_class'];
$hitPoints   = $ch['hit_points'];
$speed       = $ch['speed'];
$initiative  = $ch['initiative'];
$description = $ch['description'];

// Load attributes
$stmt = $pdo->prepare('SELECT * FROM attributes WHERE character_id = :cid');
$stmt->execute(['cid' => $cid]);
$at = $stmt->fetch();
$str = $at['strength'];
$dex = $at['dexterity'];
$con = $at['constitution'];
$int = $at['intelligence'];
$wis = $at['wisdom'];
$cha = $at['charisma'];

// Load campaigns owned or shared with me (distinct placeholders)
$stmt = $pdo->prepare("
  SELECT c.campaign_id, c.name
    FROM campaigns c
    LEFT JOIN campaign_shares s
      ON c.campaign_id = s.campaign_id
   WHERE c.user_id = :u1
      OR s.user_id = :u2
   GROUP BY c.campaign_id
   ORDER BY c.name
");
$stmt->execute([
    'u1' => $_SESSION['user_id'],
    'u2' => $_SESSION['user_id'],
]);
$campaigns = $stmt->fetchAll();

// Static lists
$races       = ['Human','Elf','Dwarf','Halfling','Dragonborn','Gnome','Half-Elf','Half-Orc','Tiefling'];
$classes     = ['Barbarian','Bard','Cleric','Druid','Fighter','Monk','Paladin','Ranger','Rogue','Sorcerer','Warlock','Wizard'];
$backgrounds = ['Acolyte','Charlatan','Criminal','Entertainer','Folk Hero','Guild Artisan','Hermit','Noble','Outlander','Sage','Sailor','Soldier','Urchin'];
$alignments  = ['Lawful Good','Neutral Good','Chaotic Good','Lawful Neutral','True Neutral','Chaotic Neutral','Lawful Evil','Neutral Evil','Chaotic Evil'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect fields
    $campaignId  = ($_POST['campaign_id'] ?? '') === '' ? null : (int)$_POST['campaign_id'];
    $name        = trim($_POST['name'] ?? '');
    $race        = $_POST['race'] ?? '';
    $charClass   = $_POST['character_class'] ?? '';
    $level       = max(1, (int)($_POST['level'] ?? 1));
    $background  = $_POST['background'] ?? '';
    $alignment   = $_POST['alignment'] ?? '';
    $armorClass  = max(0, (int)($_POST['armor_class'] ?? 10));
    $hitPoints   = max(0, (int)($_POST['hit_points'] ?? 10));
    $speed       = max(0, (int)($_POST['speed'] ?? 30));
    $initiative  = (int)($_POST['initiative'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    // Ability scores
    $str = max(1, min(30, (int)($_POST['strength'] ?? $str)));
    $dex = max(1, min(30, (int)($_POST['dexterity'] ?? $dex)));
    $con = max(1, min(30, (int)($_POST['constitution'] ?? $con)));
    $int = max(1, min(30, (int)($_POST['intelligence'] ?? $int)));
    $wis = max(1, min(30, (int)($_POST['wisdom'] ?? $wis)));
    $cha = max(1, min(30, (int)($_POST['charisma'] ?? $cha)));

    // Validation
    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if (! in_array($race, $races, true)) {
        $errors[] = 'Please select a valid race.';
    }
    if (! in_array($charClass, $classes, true)) {
        $errors[] = 'Please select a valid class.';
    }
    if (! in_array($background, $backgrounds, true)) {
        $errors[] = 'Please select a valid background.';
    }
    if (! in_array($alignment, $alignments, true)) {
        $errors[] = 'Please select a valid alignment.';
    }

    if (empty($errors)) {
        // Update character
        $upd = $pdo->prepare('
            UPDATE characters SET
              campaign_id     = :cid2,
              name            = :n,
              race            = :race,
              character_class = :cls,
              level           = :lvl,
              background      = :bg,
              alignment       = :align,
              armor_class     = :ac,
              hit_points      = :hp,
              speed           = :spd,
              initiative      = :init,
              description     = :desc,
              updated_at      = CURRENT_TIMESTAMP
            WHERE character_id = :cid
        ');
        $upd->execute([
            'cid2'=> $campaignId,
            'n'   => $name,
            'race'=> $race,
            'cls' => $charClass,
            'lvl' => $level,
            'bg'  => $background,
            'align'=> $alignment,
            'ac'  => $armorClass,
            'hp'  => $hitPoints,
            'spd' => $speed,
            'init'=> $initiative,
            'desc'=> $description,
            'cid' => $cid
        ]);

        // Update attributes
        $pdo->prepare('
            UPDATE attributes SET
              strength     = :str,
              dexterity    = :dex,
              constitution = :con,
              intelligence = :int,
              wisdom       = :wis,
              charisma     = :cha
            WHERE character_id = :cid
        ')->execute([
            'str' => $str,
            'dex' => $dex,
            'con' => $con,
            'int' => $int,
            'wis' => $wis,
            'cha' => $cha,
            'cid' => $cid
        ]);

        header('Location:' . BASE_URL . 'characters/list.php');
        exit;
    }
}
?>

<section class="form-container">
  <h2>Edit Character</h2>
  <?php if ($errors): ?>
    <div class="errors"><ul>
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
      <?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <form method="post">
    <!-- Campaign dropdown -->
    <label>Campaign (optional)</label>
    <select name="campaign_id">
      <option value="">— none —</option>
      <?php foreach ($campaigns as $c): ?>
        <option value="<?= $c['campaign_id'] ?>"
          <?= $c['campaign_id'] === $campaignId ? 'selected' : '' ?>>
          <?= htmlspecialchars($c['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <!-- Character fields... -->
    <label>Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>

    <label>Race</label>
    <select name="race" required>
      <?php foreach ($races as $r): ?>
        <option value="<?= $r ?>" <?= $r === $race ? 'selected' : '' ?>><?= $r ?></option>
      <?php endforeach; ?>
    </select>

    <label>Class</label>
    <select name="character_class" required>
      <?php foreach ($classes as $cl): ?>
        <option value="<?= $cl ?>" <?= $cl === $charClass ? 'selected' : '' ?>><?= $cl ?></option>
      <?php endforeach; ?>
    </select>

    <label>Level</label>
    <input type="number" name="level" min="1" value="<?= $level ?>">

    <label>Background</label>
    <select name="background" required>
      <?php foreach ($backgrounds as $bg): ?>
        <option value="<?= $bg ?>" <?= $bg === $background ? 'selected' : '' ?>><?= $bg ?></option>
      <?php endforeach; ?>
    </select>

    <label>Alignment</label>
    <select name="alignment" required>
      <?php foreach ($alignments as $al): ?>
        <option value="<?= $al ?>" <?= $al === $alignment ? 'selected' : '' ?>><?= $al ?></option>
      <?php endforeach; ?>
    </select>

    <label>Armor Class</label>
    <input type="number" name="armor_class" min="0" value="<?= $armorClass ?>">

    <label>Hit Points</label>
    <input type="number" name="hit_points" min="0" value="<?= $hitPoints ?>">

    <label>Speed</label>
    <input type="number" name="speed" min="0" value="<?= $speed ?>">

    <label>Initiative</label>
    <input type="number" name="initiative" min="0" value="<?= $initiative ?>">

    <label>Description</label>
    <textarea name="description"><?= htmlspecialchars($description) ?></textarea>

    <!-- Ability scores -->
    <fieldset>
      <legend>Ability Scores</legend>
      <label>STR <input type="number" name="strength" min="1" max="30" value="<?= $str ?>"></label>
      <label>DEX <input type="number" name="dexterity" min="1" max="30" value="<?= $dex ?>"></label>
      <label>CON <input type="number" name="constitution" min="1" max="30" value="<?= $con ?>"></label>
      <label>INT <input type="number" name="intelligence" min="1" max="30" value="<?= $int ?>"></label>
      <label>WIS <input type="number" name="wisdom" min="1" max="30" value="<?= $wis ?>"></label>
      <label>CHA <input type="number" name="charisma" min="1" max="30" value="<?= $cha ?>"></label>
    </fieldset>

    <button class="button">Save Changes</button>
    <a href="<?= BASE_URL ?>characters/list.php" class="button">Cancel</a>
  </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
