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

$selected_employee_id = null;
$assigned_trainings = [];
$completed_trainings = [];

// Eğer bir çalışan seçilmişse
if (isset($_GET['employee_id']) && !empty($_GET['employee_id'])) {
    $selected_employee_id = (int)$_GET['employee_id'];
    $assigned_trainings = $training_handler->getAssignedTrainingsByUserId($selected_employee_id);

    foreach ($assigned_trainings as $training) {
        if ($training['status'] === 'Tamamlandı') {
            $completed_trainings[] = $training['title'];
        }
    }
}
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Çalışan Yetkinlik Raporu</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Çalışan Seçimi</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="employee_competency.php">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label for="employee_id" class="form-label">Raporu görmek için bir çalışan seçin:</label>
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
                        <button type="submit" class="btn btn-info">Raporu Göster</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($selected_employee_id): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Eğitim Durum Raporu</h6>
        </div>
        <div class="card-body">
            <?php if (empty($assigned_trainings)): ?>
                <div class="alert alert-warning">Bu çalışana atanmış herhangi bir eğitim bulunamadı.</div>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($assigned_trainings as $training): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($training['title']); ?>

                            <?php
                                $status_badge = 'secondary';
                                if ($training['status'] === 'Tamamlandı') $status_badge = 'success';
                                if ($training['status'] === 'Başladı') $status_badge = 'info';
                                if ($training['status'] === 'Atandı') $status_badge = 'warning';
                            ?>
                            <span class="badge bg-<?php echo $status_badge; ?>"><?php echo htmlspecialchars($training['status']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <hr>
                <div class="alert alert-light mt-4">
                    <h5 class="alert-heading">Yetkinlik Özeti</h5>
                    <?php if (empty($completed_trainings)): ?>
                        <p>Çalışan henüz herhangi bir eğitimi tamamlamamıştır. Hiçbir özel yetkinlik gerektiren işi yapamaz.</p>
                    <?php else: ?>
                        <p>Çalışan aşağıdaki eğitimleri başarıyla tamamlamıştır:</p>
                        <ul>
                            <?php foreach ($completed_trainings as $training_title): ?>
                                <li><strong><?php echo htmlspecialchars($training_title); ?></strong></li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="mb-0">Bu nedenle, bu eğitimlerin gerektirdiği tüm işleri yapmaya yetkilidir.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
include __DIR__ . '/../templates/dashboard_footer.php';
ob_end_flush();
?>
