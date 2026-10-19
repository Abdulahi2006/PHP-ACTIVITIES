<?php
session_start();

if (empty($_SESSION['student_data']) || !is_array($_SESSION['student_data'])) {
    header('Location: index.php');
    exit;
}

$data = $_SESSION['student_data'];

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$sections = [
    'Personal Information' => [
        'student_name' => 'Full Name',
        'pob' => 'Place of Birth',
        'dob' => 'Date of Birth',
        'address' => 'Address',
        'gender' => 'Gender',
        'telephone' => 'Telephone',
        'email' => 'Email'
    ],
    'Parent Information' => [
        'parent_name' => 'Parent/Guardian Name',
        'parent_telephone' => 'Parent/Guardian Telephone',
        'relationship' => 'Relationship'
    ],
    'Academic Information' => [
        'student_id' => 'Student ID',
        'faculty' => 'Faculty',
        'department' => 'Department',
        'class' => 'Class',
        'registration_date' => 'Registration Date'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration Result</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container result-container">
    <header class="page-header">
        <p class="eyebrow">Jamhuriya University of Science &amp; Technology</p>
        <h1>Registration Successful</h1>
        <p>The submitted student information is displayed below.</p>
    </header>

    <div class="success" role="status">Student information was submitted successfully.</div>

    <?php if (!empty($data['image'])): ?>
        <div class="student-photo">
            <img src="<?= h($data['image']) ?>" alt="Uploaded student image">
        </div>
    <?php endif; ?>

    <?php foreach ($sections as $sectionTitle => $fields): ?>
        <section class="table-section">
            <h2><?= h($sectionTitle) ?></h2>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr><th>Field</th><th>Submitted Information</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($fields as $key => $label): ?>
                        <tr>
                            <th scope="row"><?= h($label) ?></th>
                            <td><?= h($data[$key] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endforeach; ?>

    <div class="actions">
        <a class="button" href="index.php">Register Another Student</a>
        <button class="button button-secondary" type="button" onclick="window.print()">Print Result</button>
    </div>
    <footer>PHP &amp; MySQL — Chapter 4 Assignment (Forms)</footer>
</main>
</body>
</html>
