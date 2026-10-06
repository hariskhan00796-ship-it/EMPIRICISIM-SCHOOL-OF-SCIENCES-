<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(int $status, array $body): void
{
	http_response_code($status);
	echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
	header('Allow: POST');
	respond(405, ['message' => 'Submit the application using the form.']);
}

if (trim((string) ($_POST['website'] ?? '')) !== '') {
	respond(200, ['message' => 'Your application was received. The school will contact you.']);
}

$application = [
	'student_name' => trim((string) ($_POST['student_name'] ?? '')),
	'father_name' => trim((string) ($_POST['father_name'] ?? '')),
	'whatsapp' => trim((string) ($_POST['whatsapp'] ?? '')),
	'parent_email' => trim((string) ($_POST['parent_email'] ?? '')),
	'class_name' => trim((string) ($_POST['class_name'] ?? '')),
	'previous_school' => trim((string) ($_POST['previous_school'] ?? '')),
];

$allowedClasses = [
	'class nursery', 'class prep', 'class 1st', 'class 2nd', 'class 3rd',
	'class 4th', 'class 5th', 'class 6th', 'class 7th', 'class 8th',
	'class 9th', 'class 10th', 'class 1st year', 'class 2nd year',
];

if (
	$application['student_name'] === '' || strlen($application['student_name']) > 120 ||
	$application['father_name'] === '' || strlen($application['father_name']) > 120 ||
	$application['whatsapp'] === '' || strlen($application['whatsapp']) > 30 ||
	!filter_var($application['parent_email'], FILTER_VALIDATE_EMAIL) || strlen($application['parent_email']) > 254 ||
	!in_array($application['class_name'], $allowedClasses, true) ||
	$application['previous_school'] === '' || strlen($application['previous_school']) > 160
) {
	respond(422, ['message' => 'Please check the form fields and try again.']);
}

$dataDirectory = getenv('EMPIRICISIM_DATA_DIR');
if (!is_string($dataDirectory) || $dataDirectory === '') {
	$dataDirectory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'private_admissions';
}

if (!is_dir($dataDirectory) && !mkdir($dataDirectory, 0700, true) && !is_dir($dataDirectory)) {
	respond(500, ['message' => 'The application could not be saved. Please contact the school directly.']);
}

$databasePath = $dataDirectory . DIRECTORY_SEPARATOR . 'admissions.sqlite';
try {
	$database = new PDO('sqlite:' . $databasePath);
	$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$database->exec(
		'CREATE TABLE IF NOT EXISTS admissions (' .
		'id INTEGER PRIMARY KEY AUTOINCREMENT, ' .
		'student_name TEXT NOT NULL, ' .
		'father_name TEXT NOT NULL, ' .
		'whatsapp TEXT NOT NULL, ' .
		'parent_email TEXT NOT NULL, ' .
		'class_name TEXT NOT NULL, ' .
		'previous_school TEXT NOT NULL, ' .
		'received_at TEXT NOT NULL)'
	);
	$statement = $database->prepare(
		'INSERT INTO admissions ' .
		'(student_name, father_name, whatsapp, parent_email, class_name, previous_school, received_at) ' .
		'VALUES (:student_name, :father_name, :whatsapp, :parent_email, :class_name, :previous_school, :received_at)'
	);
	$statement->execute([
		':student_name' => $application['student_name'],
		':father_name' => $application['father_name'],
		':whatsapp' => $application['whatsapp'],
		':parent_email' => $application['parent_email'],
		':class_name' => $application['class_name'],
		':previous_school' => $application['previous_school'],
		':received_at' => gmdate('c'),
	]);
	@chmod($databasePath, 0600);
} catch (PDOException $exception) {
	error_log('Admission database error: ' . $exception->getMessage());
	respond(500, ['message' => 'Application storage is unavailable. Please contact the school directly.']);
}

respond(200, ['message' => 'Your application was received. The school will contact you.']);
