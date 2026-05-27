<?php
	
	ob_start();
	require_once __DIR__ . '/../../config/db.php';
	ob_end_clean();

	require_once __DIR__ . '/../../helpers/response.php';
	require_once __DIR__ . '/../../helpers/logger.php';

	header('Content-Type: application/json');

	try{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			logError($conn, "Upload-profile-picture route accessed with invalid method", __FILE__, __LINE__, null);
			jsonResponse(404, false, 'Route not found', null, 'The requested endpoint does not exist.');
		}

		$userId = $_POST['user_id'] ?? null;
		if (!$userId || !ctype_digit((string)$userId)) {
			logError($conn, "Profile picture upload failed: missing or invalid user_id", __FILE__, __LINE__, null);
			jsonResponse(400, false, 'Valid user_id is required.', null, 'Missing or invalid user_id.');
		}

		if (!isset($_FILES['profile_picture'])) {
			logError($conn, "Profile picture upload failed: no file uploaded for user_id=$userId", __FILE__, __LINE__, $userId);
			jsonResponse(400, false, 'profile_picture file is required.', null, 'No file was uploaded.');
		}

		$file = $_FILES['profile_picture'];

		if (!isset($file['error']) || is_array($file['error'])) {
			logError($conn, "Profile picture upload failed: malformed upload payload for user_id=$userId", __FILE__, __LINE__, $userId);
			jsonResponse(400, false, 'Invalid file upload payload.', null, 'Malformed upload request.');
		}

		if ($file['error'] !== UPLOAD_ERR_OK) {
			$uploadErrors = [
				UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the server upload_max_filesize limit.',
				UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the form MAX_FILE_SIZE limit.',
				UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded.',
				UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
				UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder on the server.',
				UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
				UPLOAD_ERR_EXTENSION => 'File upload stopped by a PHP extension.',
			];

			$errorMessage = $uploadErrors[$file['error']] ?? 'Unknown upload error.';
			logError($conn, "Profile picture upload failed: PHP upload error for user_id=$userId — $errorMessage", __FILE__, __LINE__, $userId);
			jsonResponse(400, false, 'Profile picture upload failed.', null, $errorMessage);
		}

		$maxSize = 2 * 1024 * 1024; 
		if (($file['size'] ?? 0) <= 0 || $file['size'] > $maxSize) {
			logError($conn, "Profile picture upload failed: invalid file size ({$file['size']} bytes) for user_id=$userId", __FILE__, __LINE__, $userId);
			jsonResponse(400, false, 'File size must be between 1 byte and 2MB.', null, 'Invalid file size.');
		}

		$allowedMimeToExt = [
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'image/webp' => 'webp',
			'image/gif' => 'gif',
		];

		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mimeType = $finfo->file($file['tmp_name']);
		if (!$mimeType || !isset($allowedMimeToExt[$mimeType])) {
			logError($conn, "Profile picture upload failed: unsupported MIME type ($mimeType) for user_id=$userId", __FILE__, __LINE__, $userId);
			jsonResponse(400, false, 'Only image files are allowed (jpg, png, webp, gif).', null, 'Unsupported file type.');
		}

		
		$stmt = $conn->prepare('SELECT id, profile_picture FROM users WHERE id = ?');
		$stmt->execute([(int)$userId]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$user) {
			logError($conn, "Profile picture upload failed: no user found for user_id=$userId", __FILE__, __LINE__, $userId);
			jsonResponse(404, false, 'User not found.', null, 'No user exists with the provided user_id.');
		}

		$uploadDir = __DIR__ . '/../../../uploads/profile_pictures';
		if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
			logError($conn, "Profile picture upload failed: could not create upload directory for user_id=$userId", __FILE__, __LINE__, $userId);
			jsonResponse(500, false, 'Could not create upload directory.', null, 'Server storage initialization failed.');
		}

		$fileName = 'user_' . (int)$userId . '_' . bin2hex(random_bytes(8)) . '.' . $allowedMimeToExt[$mimeType];
		$absolutePath = $uploadDir . '/' . $fileName;
		$relativePath = 'uploads/profile_pictures/' . $fileName;

		if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
			logError($conn, "Profile picture upload failed: could not move file to destination for user_id=$userId", __FILE__, __LINE__, $userId);
			jsonResponse(500, false, 'Failed to save uploaded file.', null, 'Could not move file to destination folder.');
		}

		$conn->beginTransaction();

		$updateStmt = $conn->prepare('UPDATE users SET profile_picture = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
		$updateStmt->execute([$relativePath, (int)$userId]);

		$conn->commit();
		
		if (!empty($user['profile_picture'])) {
			$oldPath = (string)$user['profile_picture'];
			if (strpos($oldPath, 'uploads/profile_pictures/') === 0) {
				$oldAbsolutePath = __DIR__ . '/../../../' . $oldPath;
				if (file_exists($oldAbsolutePath) && is_file($oldAbsolutePath)) {
					@unlink($oldAbsolutePath);
				}
			}
		}

		jsonResponse(200, true, 'Profile picture uploaded successfully.', [
			'user_id' => (int)$userId,
			'profile_picture' => $relativePath,
		]);
	}catch (Throwable $e) {
		if (isset($conn) && $conn->inTransaction()) {
			$conn->rollBack();
		}

		if (isset($absolutePath) && file_exists($absolutePath)) {
			@unlink($absolutePath);
		}

		logError($conn, $e->getMessage(), $e->getFile(), $e->getLine(), $userId ?? null);

		jsonResponse(500, false, 'Internal Server Error');
	}
?>
