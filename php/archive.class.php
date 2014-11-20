<?php

class Archive{

	private $filename;
	
	/*************************************************************************
	 * Constructeur
	 ************************************************************************/
	function __construct($filename) {
		$this->filename = $filename;
	}

	/*************************************************************************
	 * retournes les infos sur l'archive
	 ************************************************************************/
	function getInfos(){
		$tab_infos['name'] = substr($this->filename, 0 , -16);
		$tab_infos['filename'] = $this->filename;
		$str_date = substr($this->filename, -16, 12);
		$tab_infos['date'] = mktime(
			intval(substr($str_date, 8, 2)), 	//Heure
			intval(substr($str_date, 10, 2)), 	//Minute
			0,									//seconde	
			intval(substr($str_date, 4, 2)), 	//Mois
			intval(substr($str_date, 6, 2)), 	//Jour
			intval(substr($str_date, 0, 4)) 	//Ann&eacute;e
		);
		$tab_infos['url']  = '../admin/archives/'.$this->filename;

		$tab_infos['size'] = $this->calFilesSize();

		return $tab_infos;
	}

	/*************************************************************************
	 * Restaure une archive si le nom wiki est libre
	 ************************************************************************/
	function restore(){
		$name = substr($this->filename, 0 , -16);
		$path = "../wikis/".$name;
		
		//V&eacute;rifier si le wiki n'est pas d&eacute;j&agrave; existant
		if(file_exists($path)){
			throw new Exception("Le wiki existe d&eacute;j&agrave;.", 1);
			exit();
		}

		//D&eacute;compresser les donn&eacute;es
		$output = shell_exec("mkdir tmp/".$name);
		if(!is_dir("tmp/".$name)) {
			throw new Exception("Impossible de cr&eacute;er le repertoire temporaire"
						  ." (V&eacute;rifiez les droits d'acces sur admin/tmp)", 1);
			exit();
		}

		$output = shell_exec("cd tmp && tar -xvzf ../archives/"
							.$this->filename." && cd -");
		if(!is_dir("tmp/".$name)) {
			throw new Exception("Impossible d'extraire l'archive (V&eacute;rifiez "
								."les droits d'acces sur admin/tmp) ", 1);
			exit();
		}

		//d&eacute;placer les fichiers
		$output = shell_exec("mv tmp/".$name."/".$name." ../wikis/");
		if(!is_dir("../wikis/".$name)) {
			throw new Exception("Impossible de replacer les fichiers du wiki "
							."(V&eacute;rifiez les droits d'acces sur wikis/) ", 1);
			exit();
		}

		//restaurer la base de donn&eacute;e
		include("../wikis/".$name."/wakka.config.php");

		$output = shell_exec("cat tmp/".$name."/".$name.".sql | "
			.$GLOBALS['exec_path']."mysql" 
			." --host=".$wakkaConfig['mysql_host']
			." --user=".$wakkaConfig['mysql_user']
			." --password=".$wakkaConfig['mysql_password']
			." ".$wakkaConfig['mysql_database']);	

		//Effacer les fichiers temporaires
		$output = shell_exec("rm -r tmp/".$name);
		if(is_dir("tmp/".$name)) {
			throw new Exception("Impossible de supprimer les fichiers "
							."temporaires. Pr&eacute;venez l'administrateur.", 1);
			exit();
		}

	}

	/*************************************************************************
	 * Supprime une archive
	 ************************************************************************/
	// TODO : gestion d'une corbeille ?
	function delete(){
		$output = shell_exec("rm ../admin/archives/".$this->filename);
		if(is_file("../admin/archives/".$this->filename)) {
			throw new Exception("Impossible de supprimer l'archive", 1);
			exit();
		}
	}

	private function calFilesSize(){
	
		/*$output = shell_exec("du -s ../admin/archives/".$this->filename);
		$size = explode("\t", $output);
		
		return intval($size[0]);*/

		return filesize("../admin/archives/".$this->filename);
	}
}
?>
