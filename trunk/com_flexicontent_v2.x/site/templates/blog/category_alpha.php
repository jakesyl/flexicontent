<?php
/**
 * @version 1.5 stable $Id: category_alpha.php 171 2010-03-20 00:44:02Z emmanuel.danan $
 * @package Joomla
 * @subpackage FLEXIcontent
 * @copyright (C) 2009 Emmanuel Danan - www.vistamedia.fr
 * @license GNU/GPL v2
 * 
 * FLEXIcontent is a derivative work of the excellent QuickFAQ component
 * @copyright (C) 2008 Christoph Lukes
 * see www.schlu.net for more information
 *
 * FLEXIcontent is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
$alphacharacters = $this->params->get('alphacharacters', "a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,y,z|0,1,2,3,4,5,6,7,8,9");
$groups = explode("|", $alphacharacters);
?>

<div id="fc_alpha">
	<?php
	$flag = true;
	foreach($groups as $group) {
		$letters = explode(",", $group);
	?>
	<div class="letters">
	<?php if($flag) {?>
	<a class="fc_alpha_index" href="#" onclick="document.getElementById('alpha_index').value='';document.getElementById('adminForm').submit();"><?php echo JText::_('FLEXI_ALL'); ?></a>
	<?php $flag = false;}?>
	<?php
		foreach ($letters as $letter) :
			$letter = trim($letter);
			if (in_array($letter, $this->alpha)) :
				echo "<a class=\"fc_alpha_index\" href=\"#\" onclick=\"document.getElementById('alpha_index').value='".$letter."';document.getElementById('adminForm').submit();\">".strtoupper($letter)."</a>";
			else :
				echo "<span class=\"fc_alpha_index\">".strtoupper($letter)."</span>";
			endif;
		endforeach;
	?>
	</div>
	<?php
	}?>
</div>