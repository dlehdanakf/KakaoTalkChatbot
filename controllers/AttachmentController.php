<?php
	class AttachmentController {
		public function uploadAttachment(){
			header('Content-Type: application/json');
			try {
				//	업로드된 파일의 에러 형태 검사
				//	is_array를 검사하는 이유는 에러 형태가 배열 형태일 경우 여러 에러가 곂치는 경우이기 때문
				if (
					!isset($_FILES['attachment']['error']) ||
					is_array($_FILES['attachment']['error'])
				) {
					throw new RuntimeException('서버에 전달된 첨부파일이 유효하지 않습니다');
				}
				// $_FILES['attachment']['error'] 값 확인
				switch ($_FILES['attachment']['error']) {
					case UPLOAD_ERR_OK:
						break;
					case UPLOAD_ERR_NO_FILE:
						throw new RuntimeException('첨부파일이 전달되지 않았습니다');
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						throw new RuntimeException('첨부파일이 크기 제한을 초과했습니다');
					default:
						throw new RuntimeException('알 수 없는 오류가 발생했습니다');
				}
				//	확장자 검사
				if (false === $ext = array_search(
						(new finfo(FILEINFO_MIME_TYPE))->file($_FILES['attachment']['tmp_name']), [
						'jpg' => 'image/jpeg',
						'png' => 'image/png',
						'gif' => 'image/gif',
						'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
						'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
						'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
						'pdf' => 'application/pdf',
						'hwp' => 'application/x-hwp',
						'ai' => 'application/postscript',
						'psd' => 'image/vnd.adobe.photoshop',
						'zip' => 'application/zip',
						'rar' => 'application/x-rar-compressed',
						'7z' => 'application/x-7z-compressed',
						'txt' => 'text/plain',
					],
						true
					)) {
					throw new RuntimeException('해당 확장자는 업로드할 수 없습니다');
				}

				$attachment = new Attachment($_FILES['attachment']['name']);
				$attachment->extension = $ext;
				$attachment->hashed_name = $attachment->hashed_name . '.' . $ext;
				//	같은 경로에 같은 파일명으로 된 첨부파일이 저장되는 것을 방지
				$attachment->preventFileAlreadyExist();
				//	해당 첨부파일 DB에 기록
				$attachment->save();
				if (!is_dir($attachment->getSavePath())){
					mkdir($attachment->getSavePath(), 0777, true);
				}
				if (!move_uploaded_file(
					$_FILES['attachment']['tmp_name'],
					$attachment->getUploadDirectory()
				)) {
					throw new RuntimeException('서버에 첨부파일을 저장하는데 오류가 발생했습니다');
				}
				B::DIE_MESSAGE(200, '성공', [
					'id' => $attachment->id,
					'name' => $attachment->original_name,
					'link' => $attachment->getDownloadUrl(),
					'dir' => $attachment->getDownloadLinkDirectory(),
					'ext' => $attachment->extension
				]);
			} catch(RuntimeException $e) {
				B::DIE_MESSAGE(500, $e->getMessage());
			}
		}
		public function downloadAttachment(){
			header('Content-Type: application/json');
			if(!isset($_REQUEST['srl']) || !isset($_REQUEST['file'])){
				return B::VIEW()->render('error.html', [
					'page_desc' => '다운로드할 첨부파일을 입력해주세요'
				]);
			}
			try {
				$attachment = Attachment::CREATE_BY_HASH_NAME($_REQUEST['srl'], $_REQUEST['file']);
			} catch(Exception $e) {
				return B::VIEW()->render('error.html', [
					'page_desc' => '첨부파일을 찾을 수 없습니다'
				]);
			}
			$file_path = $attachment->getUploadDirectory();
			$file_size = filesize($file_path);
			header("Pragma: public");
			header("Expires: 0");
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"$attachment->original_name\"");
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: $file_size");
			ob_clean();
			flush();
			readfile($file_path);
		}
	}