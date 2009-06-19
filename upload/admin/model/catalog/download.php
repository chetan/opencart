<?php
class ModelCatalogDownload extends Model {
	public function addDownload($data) {
		$filename = basename($data['download']['name']) . '.' . md5(rand());
		
		if (@move_uploaded_file($data['download']['tmp_name'], DIR_DOWNLOAD . $filename)) {
			@unlink($data['download']['tmp_name']);
	  	}
				
      	$this->db->query("INSERT INTO " . DB_PREFIX . "download SET filename = '" . $this->db->escape($filename) . "', mask = '" . $this->db->escape(basename($data['download']['name'])) . "', remaining = '" . (int)$data['remaining'] . "', date_added = NOW()");

      	$download_id = $this->db->getLastId(); 

      	foreach ($data['download_description'] as $language_id => $value) {
        	$this->db->query("INSERT INTO " . DB_PREFIX . "download_description SET download_id = '" . (int)$download_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
      	}	
	}
	
	public function editDownload($download_id, $data) {
      	if ($data['download']['name']) {
			$filename = basename($data['download']['name']) . '.' . md5(rand());
		
			if (@move_uploaded_file($data['download']['tmp_name'], DIR_DOWNLOAD . $filename)) {
				@unlink($data['download']['tmp_name']);
	  		}			
			
        	$this->db->query("UPDATE " . DB_PREFIX . "download SET filename = '" . $this->db->escape($filename) . "', mask = '" . $this->db->escape(basename($data['download']['name'])) . "' WHERE download_id = '" . (int)$download_id . "'");
      	}
 
        $this->db->query("UPDATE " . DB_PREFIX . "download SET remaining = '" . (int)$data['remaining'] . "' WHERE download_id = '" . (int)$download_id . "'");
		
      	$this->db->query("DELETE FROM " . DB_PREFIX . "download_description WHERE download_id = '" . (int)$download_id . "'");

      	foreach ($data['download_description'] as $language_id => $value) {
        	$this->db->query("INSERT INTO " . DB_PREFIX . "download_description SET download_id = '" . (int)$download_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
      	}	
	}
	
	public function deleteDownload($download_id) {
      	$this->db->query("DELETE FROM " . DB_PREFIX . "download WHERE download_id = '" . (int)$download_id . "'");
	  	$this->db->query("DELETE FROM " . DB_PREFIX . "download_description WHERE download_id = '" . (int)$download_id . "'");	
	}	

	public function getDownload($download_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "download WHERE download_id = '" . (int)$download_id . "'");
		
		return $query->row;
	}

	public function getDownloads($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "download d LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id) WHERE dd.language_id = '" . (int)$this->language->getId() . "'";
	
		$sort_data = array(
			'dd.name',
			'd.remaining'
		);
	
		if (in_array(@$data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY dd.name";	
		}
			
		if (@$data['order'] == 'DESC') {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
			
		if (isset($data['start']) || isset($data['limit'])) {
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);
	
		return $query->rows;
	}
	
	public function getDownloadDescriptions($download_id) {
		$download_description_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "download_description WHERE download_id = '" . (int)$download_id . "'");
		
		foreach ($query->rows as $result) {
			$download_description_data[$result['language_id']] = array('name' => $result['name']);
		}
		
		return $download_description_data;
	}
	
	public function getTotalDownloads() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "download");
		
		return $query->row['total'];
	}	
}
?>