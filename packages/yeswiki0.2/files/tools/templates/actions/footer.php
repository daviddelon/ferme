<?php
if (!defined("WIKINI_VERSION"))
{
        die ("acc&egrave;s direct interdit");
}

$chemin_theme = 'themes/'.$this->config['favorite_theme'].'/squelettes/'.$this->config['favorite_squelette'];
if (file_exists($chemin_theme)) {
	$file_content = file_get_contents($chemin_theme);
} else {
	$file_content = file_get_contents('tools/templates/'.$chemin_theme);
}
//On recupere la partie bas du template et on execute les actions wikini
$template_decoupe = explode("{WIKINI_PAGE}", $file_content);
$template_footer = $template_decoupe[1];

if ($act=preg_match_all ("/".'(\\{\\{)'.'(.*?)'.'(\\}\\})'."/is", $template_footer, $matches)) {
	$i = 0; $j = 0;
	foreach($matches as $valeur) {
		foreach($valeur as $val) {
			if (isset($matches[2][$j]) && $matches[2][$j]!='') {
				$action= $matches[2][$j];
				$template_footer=str_replace('{{'.$action.'}}', $this->Format('{{'.$action.'}}'), $template_footer);
			}
			$j++;
		}
		$i++;
	}
}

echo $template_footer;

// on affiche les requetes SQL et le temps de chargement en mode debug

if ($this->GetConfigValue("debug")=="yes")
{
	$debug_log_sql_queries = '';
	$t_SQL=0;
	foreach ($this->queryLog as $query)
	{
		$debug_log_sql_queries .= $query["query"]." (".round($query["time"],4).")<br />\n";
		$t_SQL = $t_SQL + $query["time"];
	}

	list($g2_usec, $g2_sec) = explode(" ",microtime());
	define ("t_end", (float)$g2_usec + (float)$g2_sec);
	$debug_log = "<div class=\"debug\">\n<h4>Query log</h4>\n";
	$debug_log .= "<strong>".round(t_end-t_start, 4)." s total time<br />\n";
	$debug_log .= round($t_SQL, 4)." s total SQL time</strong> (".round((($t_SQL/(t_end-t_start))*100),2)."% of total time)<br />\n";
	$debug_log .= "<strong>".count($this->queryLog)." queries :</strong><br />\n";
	$debug_log .= $debug_log_sql_queries;
	$debug_log .= "</div>\n";
	echo $debug_log;
}


?>
