<?php
// characters/add.php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';

$errors = [];

//  Pre-fill campaign_id from GET if provided
$campaignId = (isset($_GET['campaign_id']) && is_numeric($_GET['campaign_id']))
            ? (int)$_GET['campaign_id']
            : null;

//Default values
$name        = '';
$race        = '';
$charClass   = '';
$level       = 1;
$background  = '';
$alignment   = '';
$armorClass  = 10;
$hitPoints   = 10;
$speed       = 30;
$initiative  = 0;
$description = '';
$str = $dex = $con = $int = $wis = $cha = 10;

// Load campaigns owned or shared with this user
$stmt = $pdo->prepare("
    SELECT DISTINCT c.campaign_id, c.name
      FROM campaigns c
 LEFT JOIN campaign_shares s ON c.campaign_id = s.campaign_id
     WHERE c.user_id = :u1
        OR s.user_id = :u2
  ORDER BY c.name
");
$stmt->execute([
    'u1' => $_SESSION['user_id'],
    'u2' => $_SESSION['user_id'],
]);
$campaigns = $stmt->fetchAll();

$races       = ['Human','Elf','Dwarf','Halfling','Dragonborn','Gnome','Half-Elf','Half-Orc','Tiefling'];
$classes     = ['Barbarian','Bard','Cleric','Druid','Fighter','Monk','Paladin','Ranger','Rogue','Sorcerer','Warlock','Wizard'];
$backgrounds = ['Acolyte','Charlatan','Criminal','Entertainer','Folk Hero','Guild Artisan','Hermit','Noble','Outlander','Sage','Sailor','Soldier','Urchin'];
$alignments  = ['Lawful Good','Neutral Good','Chaotic Good','Lawful Neutral','True Neutral','Chaotic Neutral','Lawful Evil','Neutral Evil','Chaotic Evil'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Override with POST values
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
    $str = max(1, min(30, (int)($_POST['strength'] ?? 10)));
    $dex = max(1, min(30, (int)($_POST['dexterity'] ?? 10)));
    $con = max(1, min(30, (int)($_POST['constitution'] ?? 10)));
    $int = max(1, min(30, (int)($_POST['intelligence'] ?? 10)));
    $wis = max(1, min(30, (int)($_POST['wisdom'] ?? 10)));
    $cha = max(1, min(30, (int)($_POST['charisma'] ?? 10)));

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

    // Insert if no errors
    if (empty($errors)) {
        // Insert character
        $ins = $pdo->prepare('
            INSERT INTO characters
              (user_id, campaign_id, name, race, character_class,
               level, background, alignment, armor_class,
               hit_points, speed, initiative, description)
            VALUES
              (:u, :cid, :name, :race, :cls,
               :lvl, :bg, :align, :ac,
               :hp, :spd, :init, :desc)
        ');
        $ins->execute([
            'u'     => $_SESSION['user_id'],
            'cid'   => $campaignId,
            'name'  => $name,
            'race'  => $race,
            'cls'   => $charClass,
            'lvl'   => $level,
            'bg'    => $background,
            'align' => $alignment,
            'ac'    => $armorClass,
            'hp'    => $hitPoints,
            'spd'   => $speed,
            'init'  => $initiative,
            'desc'  => $description,
        ]);

        // Insert attributes
        $newId = $pdo->lastInsertId();
        $pdo->prepare('
            INSERT INTO attributes
              (character_id, strength, dexterity, constitution,
               intelligence, wisdom, charisma)
            VALUES
              (:cid2, :str, :dex, :con, :int, :wis, :cha)
        ')->execute([
            'cid2' => $newId,
            'str'  => $str,
            'dex'  => $dex,
            'con'  => $con,
            'int'  => $int,
            'wis'  => $wis,
            'cha'  => $cha,
        ]);

        header('Location:' . BASE_URL . 'characters/list.php');
        exit;
    }
}
?>

<section class="form-container">
  <h2>Add Character</h2>

  <?php if ($errors): ?>
    <div class="errors"><ul>
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
      <?php endforeach; ?>
    </ul></div>
  <?php endif; ?>

  <form method="post">
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

    <label>Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>

    <label>Race</label>
    <select name="race" required>
      <option value="">— select race —</option>
      <?php foreach ($races as $r): ?>
        <option value="<?= $r ?>" <?= $r === $race ? 'selected' : '' ?>><?= $r ?></option>
      <?php endforeach; ?>
    </select>

    <label>Class</label>
    <select name="character_class" required>
      <option value="">— select class —</option>
      <?php foreach ($classes as $cl): ?>
        <option value="<?= $cl ?>" <?= $cl === $charClass ? 'selected' : '' ?>><?= $cl ?></option>
      <?php endforeach; ?>
    </select>

    <label>Level</label>
    <input type="number" name="level" min="1" value="<?= $level ?>">

    <label>Background</label>
    <select name="background" required>
      <option value="">— select background —</option>
      <?php foreach ($backgrounds as $bg): ?>
        <option value="<?= $bg ?>" <?= $bg === $background ? 'selected' : '' ?>><?= $bg ?></option>
      <?php endforeach; ?>
    </select>

    <label>Alignment</label>
    <select name="alignment" required>
      <option value="">— select alignment —</option>
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

    <fieldset>
      <legend>Ability Scores</legend>
      <label>STR <input type="number" name="strength" min="1" max="30" value="<?= $str ?>"></label>
      <label>DEX <input type="number" name="dexterity" min="1" max="30" value="<?= $dex ?>"></label>
      <label>CON <input type="number" name="constitution" min="1" max="30" value="<?= $con ?>"></label>
      <label>INT <input type="number" name="intelligence" min="1" max="30" value="<?= $int ?>"></label>
      <label>WIS <input type="number" name="wisdom" min="1" max="30" value="<?= $wis ?>"></label>
      <label>CHA <input type="number" name="charisma" min="1" max="30" value="<?= $cha ?>"></label>
    </fieldset>

    <button class="button">Save Character</button>
    <a href="<?= BASE_URL ?>characters/list.php" class="button">Cancel</a>
  </form>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
