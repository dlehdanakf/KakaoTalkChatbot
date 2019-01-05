<?php
	class Attachment extends BasicModel {
		public $member;
		public $file_srl;
		public $directory;
		public $hashed_name;
		public $original_name;
		public $extension;

		public static function CREATE_BY_MYSQLID($id){
			$attachment = new self;
			$attachment->id = intval($id);
			$attachment->getFileInfoByMysqlID();

			return $attachment;
		}
		public static function CREATE_BY_HASH_NAME($file_srl, $hashed_name){
			$attachment = new self;
			$attachment->file_srl = $file_srl;
			$attachment->hashed_name = $hashed_name;
			$attachment->getFileInfoByHashedName();

			return $attachment;
		}

		public function __construct($filename = null){
			if(!$filename) return;

			$this->original_name = $filename;
			$this->hashed_name = sha1($this->original_name);
			$this->file_srl = $this->generateFileSrl();
			$this->directory = $this->getFileSrlPath($this->file_srl);
			$this->extension = 'unknown';
		}
		public function save(){
			$pdo = B::DB();
			$query = $pdo->prepare("INSERT INTO attachment (file_srl, member, directory, extension, hashed_name, original_name) VALUES (:n, :m, :d, :e, :h, :o)");
			$query->execute([
				":n" => $this->file_srl,
				":m" => $this->member,
				":d" => $this->directory,
				":e" => $this->extension,
				":h" => $this->hashed_name,
				":o" => $this->original_name
			]);

			$this->id = $pdo->lastInsertId();
		}
		public function delete(){
			$pdo = B::DB();
			$query = $pdo->prepare("DELETE FROM attachment WHERE id = :i");
			$query->execute([
				":i" => $this->id
			]);
		}
		public function preventFileAlreadyExist(){
			//	희박한 확률이지만 같은 경로, 같은 이름의 파일이 이미 존재하는경우
			try {
				$a = self::CREATE_BY_HASH_NAME($this->file_srl, $this->hashed_name);
			} catch(Exception $e) {
				return;
			}
			$this->file_srl = $this->generateFileSrl();
			$this->directory = $this->getFileSrlPath($this->file_srl);
			return $this->preventFileAlreadyExist();
		}
		public function getSavePath(){
			return './attachments/' . $this->directory;
		}
		public function getUploadDirectory(){
			return './attachments/' . $this->directory . $this->hashed_name;
		}
		public function getDownloadLinkDirectory(){
			return '/attachments/' . $this->directory . $this->hashed_name;
		}
		public function getDownloadUrl(){
			return "/attachment/download?srl=$this->file_srl&file=$this->hashed_name";
		}

		protected function getFileInfoByMysqlID(){
			$pdo = B::DB();
			$query = $pdo->prepare("SELECT * FROM attachment WHERE id = :i");
			$query->execute([
				":i" => $this->id,
			]);
			$this->storeDBFetchResult($query);
		}
		protected function getFileInfoByHashedName(){
			$pdo = B::DB();
			$query = $pdo->prepare("SELECT * FROM attachment WHERE file_srl = :n AND hashed_name = :h");
			$query->execute([
				":n" => $this->file_srl,
				":h" => $this->hashed_name,
			]);
			$this->storeDBFetchResult($query);
		}
		protected function storeDBFetchResult(PDOStatement $query){
			if($query->rowCount() < 1)
				throw new ModelNotFoundException("Attachment::첨부파일이 존재하지 않습니다");

			$result = $query->fetch(PDO::FETCH_ASSOC);
			$this->id			 = $result['id'];
			$this->member		 = $result['member'];
			$this->file_srl		 = $result['file_srl'];
			$this->directory	 = $result['directory'];
			$this->hashed_name	 = $result['hashed_name'];
			$this->original_name = $result['original_name'];
			$this->extension	 = $result['extension'];
			$this->register_date = $result['register_date'];
		}
		protected function generateFileSrl(){
			return sprintf('%06d', mt_rand(100000000, 999999999));
		}
		protected function getFileSrlPath($no, $size = 3){
			$mod = pow(10, $size);
			$output = sprintf('%0' . $size . 'd/', $no % $mod);
			if($no >= $mod) {
				$output .= $this->getFileSrlPath((int) $no / $mod, $size);
			}
			return $output;
		}
	}