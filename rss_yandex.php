<?php
/*
Plugin Name: Yandex RSS2 Export Feed
Plugin URI: http://code.google.com/p/yandex-feed/
Description: Export RSS2 for Yandex
Author: Trinity Solution LLC (coded by Madness), changed for Wordpress 2.6+ by Sherif aka sharof2000 (sharof2000@gmail.com), tweaked for wordpress 3.5+ by Ivan Matveev
Version: 1.4.2
Author URI: http://code.google.com/p/yandex-feed/
*/

class plugin_class 
{

	var $page_title;
	var $menu_title;
	var $access_level;
	var $add_page_to; 		// 1=add_menu_page 2=add_options_page 3=add_theme_page 4=add_management_page 5=plugin
	var $short_description;

	var $url_rss;			//URL RSS
	var $image_url;			//URL картинки блока image
	var $image_title;		//Заголовок картинки блока image
	var $image_link;		//URL блока image
	var $list_categories;	//Список категорий для экспорта
	var $num_posts;			//Число постов в rss
	var $description;		//Показывать ли description
	var $days;				//За сколько дней показывать статьи

	function plugin_class() 
	{
		$this->get_options();
	}
	
	function get_options()
	{
		$options = get_option('rss_yandex_options');

		$this->url_rss = $options['url_rss'];
		$this->image_url = $options['image_url'];
		$this->image_title = $options['image_title'];
		$this->image_link = $options['image_link'];
		$this->list_categories = $options['list_categories'];
		$this->num_posts = $options['num_posts'];
		$this->description = $options['description'];
		$this->cut_description = $options['cut_description'];
		$this->days = $options['days'];
	}
	
	function add_admin_menu()
	{
		if ( $this->add_page_to == 1 )
			add_menu_page($this->page_title, $this->menu_title, $this->access_level, __FILE__, array($this, 'admin_page'));
		elseif ( $this->add_page_to == 2 )
			add_options_page($this->page_title, $this->menu_title, $this->access_level, __FILE__, array($this, 'admin_page'));
		elseif ( $this->add_page_to == 3 )
			add_management_page($this->page_title, $this->menu_title, $this->access_level, __FILE__, array($this, 'admin_page'));			
		elseif ( $this->add_page_to == 4 )
			add_theme_page($this->page_title, $this->menu_title, $this->access_level, __FILE__, array($this, 'admin_page'));
		elseif ( $this->add_page_to == 5 )
			add_submenu_page('plugins.php', $this->page_title, $this->menu_title, $this->access_level, __FILE__, array($this, 'admin_page'));
	}

	function activate()
	{
		$options = array (
			'url_rss' => 'yandex-feed',
			'image_url' => get_bloginfo('url').'/image/rss_logo.gif',
			'image_title' => get_bloginfo('name'),
			'image_link' => get_bloginfo('url'),
			'list_categories' => array(),
			'num_posts' => 10,
			'description' => 0,
			'cut_description' => 0,
			'days' => 7
		);
		add_option('rss_yandex_options', $options, 'Настройки плагина RSS2 4 Yandex', 'yes');
		$this->get_options();
		$this->modify_htaccess('add');
	}

	function deactivate()
	{
		delete_option('rss_yandex_options');
		$this->modify_htaccess('delete');
	}

	function admin_page()
	{
		echo <<<EOF
		<div class="wrap"> 
		<h2>{$this->page_title}</h2>
		<p>{$this->short_description}</p>
EOF;

		if (isset($_POST['UPDATE'])) ### обновление
		{
			echo '<div id="message" class="updated fade"><p><strong>Обновлено!</strong></p></div>';

			$this->url_rss = (trim($_POST['url_rss']))?trim($_POST['url_rss']):$this->url_rss;
			$this->image_url = trim($_POST['image_url']);
			$this->image_title = trim($_POST['image_title']);
			$this->image_link = trim($_POST['image_link']);
			$this->list_categories = (array) $_POST['list_categories'];
			$this->num_posts = intval(trim($_POST['num_posts']));
			$this->description = intval(trim($_POST['description']));
			$this->cut_description = intval(trim($_POST['cut_description']));
			$this->days = intval(trim($_POST['days']));

			$options = array (
				'url_rss' => $this->url_rss,
				'image_url' => $this->image_url,
				'image_title' => $this->image_title,
				'image_link' => $this->image_link,
				'list_categories' => $this->list_categories,
				'num_posts' => $this->num_posts,
				'description' => $this->description,
				'cut_description' => $this->cut_description,
				'days' => $this->days
			);
			update_option('rss_yandex_options', $options, 'Настройки плагина RSS2 4 Yandex', 'yes');

			$this->modify_htaccess();
		}

		$this->view_options_page();
		echo '</div>';
	}

	function view_options_page() 
	{
		echo <<<EOF
<h3>Опции</h3>
<form action="" method="POST">
<table class="form-table">
	<tr>
		<th><label for="url_rss">URL RSS:</label></th>
		<td><input type="text" id="url_rss" name="url_rss" class="regular-text code" size="40" value="{$this->url_rss}"></td>
	</tr>
	<tr>
		<th><label for="image_url">URL картинки:</label></th>
		<td><input type="text" id="image_url" name="image_url" size="40" class="regular-text code" value="{$this->image_url}"></td>
	</tr>
	<tr>
		<th><label for="image_title">Титл картинки:</label></th>
		<td><input type="text" id="image_title" name="image_title" size="40" class="regular-text code" value="{$this->image_title}"></td>
	</tr>
	<tr>
		<th><label for="image_link">Ссылка картинки:</label></th>
		<td><input type="text" id="image_link" name="image_link" size="40" class="regular-text code" value="{$this->image_link}"></td>
	</tr>
	<tr>
		<th><label for="days">За сколько дней показывать статьи:</label></th>
		<td><input type="text" id="days" name="days" class="regular-text code" size="10" value="{$this->days}"></td>
	</tr>
EOF;
echo '<tr>
		<th>Показывать ли description:</th>
		<td><label for="description_n"><input type="radio" id="description_n" name="description" value="0"'.((!$this->description)?' checked':'').'> Нет</label> <label for="description_y"><input type="radio" id="description_y" name="description" value="1"'.(($this->description)?' checked':'').'> Да</label></td>
	</tr>';
echo '<tr>
		<th>Обрезать ли description (оставить только &lt;p&gt; и замена " на \'):</th>
		<td><label for="cut_description_n"><input type="radio" id="cut_description_n" name="cut_description" value="0"'.((!$this->cut_description)?' checked':'').'> Нет</label> <label for="cut_description_y"><input type="radio" id="cut_description_y" name="cut_description" value="1"'.(($this->cut_description)?' checked':'').'> Да</label></td>
	</tr>';
echo '<tr>
		<th>Количество постов в RSS:</th>
		<td><label for="num_posts_999"><input type="radio" id="num_posts_999" name="num_posts" value="999"'.(($this->num_posts == 999)?' checked':'').'> Все</label> <label for="num_posts_10"><input type="radio" id="num_posts_10" name="num_posts" value="10"'.(($this->num_posts == 10)?' checked':'').'> 10</label> <label for="num_posts_20"><input type="radio"  id="num_posts_20" name="num_posts" value="20"'.(($this->num_posts == 20)?' checked':'').'> 20</label> <label for="num_posts_50"><input type="radio" id="num_posts_50" name="num_posts" value="50"'.(($this->num_posts == 50)?' checked':'').'> 50</label>';
echo '</table>';
		echo "<h3>Список категорий:</h3>";
		echo '<table class="form-table">';
		
		$cat_ids = get_all_category_ids();
		foreach ($cat_ids as $cat)
		{
			echo '<tr>
		<td><label for="list_categories_'.$cat.'"><input type="checkbox" id="list_categories_'.$cat.'" name="list_categories[]" value="'.$cat.'"'.
				((in_array($cat, $this->list_categories))?' checked':'').'> '.get_catname($cat).'</label></td>
		</tr>';
		}
		echo '</table>';
		echo '
			<p class="submit">
				<input type="submit" name="UPDATE" class="button-primary" value="Готово" />
			</p>
		</form>';
	}

	function modify_htaccess($type = 'update')
	{
		if ($type == 'delete')
		{
			if (is_writable(ABSPATH . '.htaccess'))
			{
				$htaccess = file(ABSPATH . '.htaccess');
				foreach ($htaccess as $num_line => $htaccess_line)
				{
					if (strstr($htaccess_line, 'wp-rss_yandex.php'))
					{
						$found = true;
						$htaccess[$num_line] = '';
					}
				}
				if ($found)
				{
					$file = fopen(ABSPATH . '.htaccess', 'wb');
					foreach ($htaccess as $htaccess_line)
					{
						fwrite($file, $htaccess_line);
					}
					fclose($file);
				}
			}
			return;
		}

		if (is_writable(ABSPATH . '.htaccess'))
		{
			$htaccess = file(ABSPATH . '.htaccess');
			$htaccess_w = array();
			$found = false;
			foreach ($htaccess as $num_line => $htaccess_line)
			{
				if (strstr($htaccess_line, 'wp-rss_yandex.php'))
				{
					$found = true;
					$htaccess_line = "RewriteRule ^{$this->url_rss}(.*)? wp-rss_yandex.php [L]\n";
				}
				$htaccess_w[] = $htaccess_line;
			}
			if (!$found)
			{
				$htaccess_w = array();
				foreach ($htaccess as $num_line => $htaccess_line)
				{
					$htaccess_w[] = $htaccess_line;
					if (strstr($htaccess_line, 'RewriteBase'))
					{
						$found = true;
						$htaccess_w[] = "RewriteRule ^{$this->url_rss}(.*)? wp-rss_yandex.php [L]\n";
					}
				}
			}
			if (!$found)
			{
				array_unshift($htaccess_w, "RewriteEngine On\n", "RewriteBase /\n", "RewriteRule ^{$this->url_rss}(.*)? wp-rss_yandex.php [L]\n");
			}
			$file = fopen(ABSPATH . '.htaccess', 'wb');
			foreach ($htaccess_w as $htaccess_line)
			{
				fwrite($file, $htaccess_line);
			}
			fclose($file);
			if ($type == 'update')
			{
				echo "<div class='updated'>Файл .htaccess обновлен!</div>";
			}
		}else{
			if (!file_exists(ABSPATH . '.htaccess'))
			{
				$file = fopen(ABSPATH . '.htaccess', 'wb');
				fwrite($file, "RewriteEngine On\nRewriteBase /\nRewriteRule ^{$this->url_rss}(.*)? wp-rss_yandex.php [L]\n");
				fclose($file);
				if ($type == 'update')
				{
					echo "<div class='updated'>Файл .htaccess создан!</div>";
				}
			}else{
				if ($type == 'update')
				{
					echo "<div class='updated'>Файл .htaccess не доступен для записи :(</div>";
				}
			}
		}
	}

} // class plugin_class

##################################################################

$my_plugin = new plugin_class();

$path_to_php_file_plugin = basename(__FILE__); //'rss_yandex.php'

$my_plugin->page_title = 'Yandex RSS Export Feed'; // название плагина (заголовок)
$my_plugin->menu_title = 'Яндекс Лента'; // название в меню
$my_plugin->access_level = 5; // уровень доступа
$my_plugin->add_page_to = 2; // куда добавлять страницу: 1=главное меню 2=настройки 3=управление 4=шаблоны 5=плагины

// короткое описание плагина
$my_plugin->short_description = 'Плагин экспортирует статьи согласно требованиям гребанного яндекса.';


##################################################################

add_action('admin_menu', array($my_plugin, 'add_admin_menu'));
add_action('deactivate_' . $path_to_php_file_plugin, array($my_plugin, 'deactivate')); 
add_action('activate_' . $path_to_php_file_plugin, array($my_plugin, 'activate')); 
?>
