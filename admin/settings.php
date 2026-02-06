<?php
$pageTitle = 'Configurações';
include 'includes/header.php';

$db = Database::getInstance();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pairs = $_POST['settings'] ?? [];
    $updated = 0;
    foreach ($pairs as $key => $value) {
        $setting = $db->fetch("SELECT setting_type FROM settings WHERE setting_key = ?", [$key]);
        if ($setting) {
            $type = $setting['setting_type'];
            if ($type === 'boolean') {
                $value = isset($_POST['settings'][$key]) ? '1' : '0';
            }
        }
        $updated += $db->update('settings', ['setting_value' => $value, 'updated_by' => $currentUser['id']], 'setting_key = :key', ['key' => $key]);
    }
    flash('success', "Configurações atualizadas ({$updated})!");
    redirect(url('admin/settings.php'));
}

$categories = $db->fetchAll("SELECT DISTINCT category FROM settings ORDER BY category ASC");
$settingsByCategory = [];
foreach ($categories as $cat) {
    $c = $cat['category'];
    $settingsByCategory[$c] = $db->fetchAll("SELECT * FROM settings WHERE category = ? ORDER BY order_index ASC, setting_key ASC", [$c]);
}
?>

<?= showFlashMessages() ?>

<div class="tabs">
    <?php foreach ($settingsByCategory as $category => $items): ?>
        <a class="tab" href="#<?= escape($category) ?>"><?= ucfirst($category) ?></a>
    <?php endforeach; ?>
</div>

<?php foreach ($settingsByCategory as $category => $items): ?>
<section id="<?= escape($category) ?>" class="card mt-4">
    <div class="card-body">
        <h3 class="card-title"><?= ucfirst($category) ?></h3>
        <form method="POST">
            <div class="settings-grid">
                <?php foreach ($items as $s): ?>
                <div class="setting-item">
                    <label class="setting-label">
                        <?= escape($s['label'] ?? $s['setting_key']) ?>
                        <?php if (!empty($s['description'])): ?>
                            <div class="text-muted"><?= escape($s['description']) ?></div>
                        <?php endif; ?>
                    </label>
                    <div class="setting-control">
                        <?php
                        $name = "settings[{$s['setting_key']}]";
                        $value = $s['setting_value'];
                        switch ($s['setting_type']) {
                            case 'boolean':
                                echo '<label class="d-flex align-center gap-1">';
                                echo '<input type="checkbox" name="' . $name . '" ' . ($value == '1' ? 'checked' : '') . '>';
                                echo ' Ativado';
                                echo '</label>';
                                break;
                            case 'integer':
                                echo '<input type="number" name="' . $name . '" class="form-control" value="' . escape($value) . '">';
                                break;
                            case 'json':
                                echo '<textarea name="' . $name . '" class="form-control" rows="3">' . escape($value) . '</textarea>';
                                break;
                            case 'html':
                                echo '<textarea name="' . $name . '" class="form-control" rows="5">' . escape($value) . '</textarea>';
                                break;
                            default:
                                echo '<input type="text" name="' . $name . '" class="form-control" value="' . escape($value) . '">';
                        }
                        ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-3">
                <button class="btn btn-primary" type="submit">Salvar Alterações</button>
            </div>
        </form>
    </div>
</section>
<?php endforeach; ?>

<!-- Personalizações do Administrador -->
<section class="card mt-4">
    <div class="card-body">
        <h3 class="card-title">Preferências do Administrador</h3>
        <form method="POST" action="<?= url('admin/settings.php') ?>">
            <div class="grid-cols-2 gap-2">
                <label>Tema do Painel
                    <select name="settings[default_theme]" class="form-control">
                        <option value="system">Sistema</option>
                        <option value="light">Claro</option>
                        <option value="dark">Escuro</option>
                    </select>
                </label>
                <label>Idioma Padrão
                    <select name="settings[default_language]" class="form-control">
                        <option value="pt-BR">Português (Brasil)</option>
                        <option value="en-US">English (US)</option>
                        <option value="es-ES">Español</option>
                    </select>
                </label>
                <label>Nome do Site
                    <input type="text" name="settings[site_name]" class="form-control" value="<?= escape(SITE_NAME) ?>">
                </label>
                <label>Email de Contato
                    <input type="email" name="settings[contact_email]" class="form-control" value="<?= escape(SITE_EMAIL) ?>">
                </label>
            </div>
            <div class="mt-3">
                <button class="btn btn-primary" type="submit">Atualizar Preferências</button>
            </div>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
