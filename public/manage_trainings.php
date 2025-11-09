<?php
ob_start();
require_once __DIR__ . '/../src/User.php';
require_once __DIR__ . '/../src/Training.php';
require_once __DIR__ . '/../templates/dashboard_header.php';

// Yetki kontrolü
if (!User::hasRole(['Süper Admin', 'ISG Uzmanı'])) {
    header("Location: index.php");
    exit();
}

$user_handler = new User();
$training_handler = new Training();

$employees = $user_handler->getAllEmployees();
$all_trainings = $training_handler->getAllTrainings();

$selected_employee_id = null;
$assigned_training_ids = [];
$update_message = '';
$update_success = false;

// Form gönderilmişse (atama güncelleme işlemi)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id_to_update = $_POST['employee_id'] ?? null;
    $posted_training_ids = $_POST['training_ids'] ?? [];

    if ($employee_id_to_update) {
        $success = $training_handler->updateUserTrainingAssignments($employee_id_to_update, $posted_training_ids);
        if ($success) {
            // Başarılı güncelleme sonrası sayfayı yeniden yönlendir (PRG Pattern)
            header("Location: manage_trainings.php?employee_id=" . $employee_id_to_update . "&status=success");
            exit();
        } else {
            $update_message = 'Eğitim atamaları güncellenirken bir hata oluştu.';
            $update_success = false;
        }
    }
}

// URL'den gelen durum mesajını kontrol et
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $update_message = 'Eğitim atamaları başarıyla güncellendi.';
    $update_success = true;
}

// Eğer bir çalışan seçilmişse (GET isteği)
if (isset($_GET['employee_id']) && !empty($_GET['employee_id'])) {
    $selected_employee_id = (int)$_GET['employee_id'];

    // Seçilen çalışanın mevcut atanmış eğitimlerini al
    $assigned_trainings = $training_handler->getAssignedTrainingsByUserId($selected_employee_id);
    foreach ($assigned_trainings as $training) {
        $assigned_training_ids[] = $training['id'];
    }
}

?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Eğitim Atama Yönetimi</h1>

    <?php if ($update_message): ?>
        <div class="alert <?php echo $update_success ? 'alert-success' : 'alert-danger'; ?>">
            <?php echo htmlspecialchars($update_message); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Çalışan Seçimi</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="manage_trainings.php">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label for="employee_id" class="form-label">Lütfen bir çalışan seçin:</label>
                        <select name="employee_id" id="employee_id" class="form-select">
                            <option value="">-- Çalışan Seçiniz --</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?php echo $employee['id']; ?>" <?php echo ($selected_employee_id == $employee['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-info">Eğitimleri Göster</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($selected_employee_id): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Atanacak Eğitimler</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="manage_trainings.php">
                <input type="hidden" name="employee_id" value="<?php echo $selected_employee_id; ?>">
                <div class="row">
                    <?php foreach ($all_trainings as $training): ?>
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="training_ids[]" value="<?php echo $training['id']; ?>" id="training_<?php echo $training['id']; ?>"
                                    <?php echo in_array($training['id'], $assigned_training_ids) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="training_<?php echo $training['id']; ?>">
                                    <?php echo htmlspecialchars($training['title']); ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <hr>
                <div class="text-end">
                    <button type="submit" class="btn btn-success">Atamaları Kaydet</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php
include __DIR__ . '/../templates/dashboard_footer.php';
ob_end_flush();
?>
