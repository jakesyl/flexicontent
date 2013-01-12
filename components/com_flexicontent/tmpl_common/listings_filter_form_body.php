<?php
// **************************************************************************************************************
// Form for (a) Text search, Field Filters, Alpha-Index, Items Total Statistics, Selectors(e.g. per page, orderby)
// **************************************************************************************************************

$infoimage = JHTML::image ( 'components/com_flexicontent/assets/images/icon-16-hint.png', '' );
$limit_selector = flexicontent_html::limit_selector( $this->params );
$orderby_selector = flexicontent_html::ordery_selector( $this->params );

$search_tip_class = $this->params->get('show_search_tip') ? ' hasTip ' : '';
$search_tip_title = $this->params->get('show_search_tip') ? ' title="'.JText::_('FLEXI_SEARCH').'::'.JText::_('FLEXI_TEXT_SEARCH_INFO').'" ' : '';
$filters_list_tip_class = $this->params->get('show_filters_list_tip') ? ' hasTip ' : '';
$filters_list_tip_title = $this->params->get('show_filters_list_tip') ? ' title="'.JText::_('FLEXI_FIELD_FILTERS').'::'.JText::_('FLEXI_FIELD_FILTERS_INFO').'" ' : '';
?>

	<?php if ((($this->params->get('use_filters', 0)) && $this->filters) || ($this->params->get('use_search'))) : /* BOF filter ans search block */ ?>
	
	<div id="fc_filter" class="floattext">
		
		<?php if ($this->params->get('use_search', 0)) : /* BOF search */ ?>
		<div class="fc_text_filter_box">
			
			<?php if ($this->params->get('show_search_label', 1)) : ?>
				<span class="fc_text_filter_label <?php echo $search_tip_class;?>" <?php echo $search_tip_title;?> ><?php echo JText::_('FLEXI_SEARCH'); ?>:</span>
				<?php echo $this->params->get('compact_search_with_filters', 1) ? '<br/>' : ''; ?>
			<?php elseif ( $this->params->get('show_search_tip') ): ?>
				<span class="hasTip" <?php echo $search_tip_title;?> ><?php echo $infoimage; ?></span>
			<?php endif; ?>
			
			<input class="fc_text_filter rc5" type="text" name="filter" id="filter" value="<?php echo $this->lists['filter'];?>" />
			<?php echo $this->params->get('compact_search_with_filters', 1) ? '<br/>' : ''; ?>
			
			<?php if ($this->params->get('show_search_go', 1) || $this->params->get('show_search_reset', 1)) : ?>
				<span class="fc_text_filter_buttons">
					
					<?php if ($this->params->get('show_search_go', 1)) : ?>
						<button class="fc_button button_go" onclick="var form=document.getElementById('adminForm');                                     adminFormPrepare(form);"><span class="fcbutton_go"><?php echo JText::_( 'FLEXI_GO' ); ?></span></button>
					<?php endif; ?>
					
					<?php if ($this->params->get('show_search_reset', 1)) : ?>
						<button class="fc_button button_reset" onclick="var form=document.getElementById('adminForm'); adminFormClearFilters(form);  adminFormPrepare(form);"><span class="fcbutton_reset"><?php echo JText::_( 'FLEXI_RESET' ); ?></span></button>
					<?php endif; ?>
					
				</span>
			<?php endif; ?>	
			
		</div>
		<?php endif; /* EOF search */ ?>

		<?php if ( !$this->params->get('compact_search_with_filters', 1) && $this->params->get('use_search') && ($this->params->get('use_filters', 0) && $this->filters) ) : ?>
			<div class="fc_text_filter_splitter"></div>
		<?php endif; ?>

		<?php if ($this->params->get('use_filters', 0) && $this->filters) : /* BOF filter */ ?>
		
			<?php if ($this->params->get('show_filters_list_label', 1)) : ?>
				<span class="fc_field_filters_list_label hasTip" title="<?php echo JText::_('FLEXI_FIELD_FILTERS'); ?>::<?php echo JText::_('FLEXI_FIELD_FILTERS_INFO'); ?>"><?php echo JText::_('FLEXI_FIELD_FILTERS'); ?>:</span>
				<?php echo $this->params->get('compact_search_with_filters', 1) ? '<br/>' : ''; ?>
			<?php elseif ( $this->params->get('show_filters_list_tip') ): ?>
				<span class="fc_field_filters_list_tipicon hasTip" <?php echo $filters_list_tip_title;?> ><?php echo $infoimage; ?></span>
			<?php endif; ?>
			
			<?php
			foreach ($this->filters as $filt) :
				if (empty($filt->html)) continue;
				// Form field that have form auto submit, need to be have their onChange Event prepended with the FORM PREPARATION function call
				if ( preg_match('/onchange[ ]*=[ ]*([\'"])/i', $filt->html, $matches) ) {
					if ( preg_match('/\.submit\(\)/', $filt->html, $matches) ) {
						// Autosubmit detected inside onChange event, prepend the event with form preparation function call
						$filt->html = preg_replace('/onchange[ ]*=[ ]*([\'"])/i', 'onchange=${1}adminFormPrepare(document.getElementById(\'adminForm\')); ', $filt->html);
					} else {
						// The onChange Event, has no autosubmit, force GO button (in case GO button was not already inside search box)
						$force_go = true;
					}
				} else {
					// Filter has no onChange event and thus no autosubmit, force GO button  (in case GO button was not already inside search box)
					$force_go = true;
				}
				?>
				<span class="filter" >
				
					<?php if ( $this->params->get('show_filter_labels', 1)==1 ) : ?>
						<span class="filter_label">
							<?php echo $filt->label; ?>
						</span>
					<?php endif; ?>
				
					<span class="filter_html">
						<?php echo $filt->html; ?>
					</span>
				
				</span>
			<?php endforeach; ?>
			
			<?php 
			$go_added = $this->params->get('use_search') && $this->params->get('show_search_go', 1);
			$reset_added = $this->params->get('use_search') && $this->params->get('show_search_reset', 1);
			?>
			
			<?php if (!empty($force_go) && !$go_added) : ?>
			<span class="fc_text_filter_buttons">
				<button class="fc_button button_go" onclick="var form=document.getElementById('adminForm');                               adminFormPrepare(form);"><span class="fcbutton_go"><?php echo JText::_( 'FLEXI_GO' ); ?></span></button>
				<?php if (!$reset_added) : ?>
				<button class="fc_button button_reset" onclick="var form=document.getElementById('adminForm'); adminFormClearFilters(form);  adminFormPrepare(form);"><span class="fcbutton_reset"><?php echo JText::_( 'FLEXI_RESET' ); ?></span></button>
				<?php endif; ?>
			</span>
			<?php endif; ?>
		
		<?php endif; /* EOF filter */ ?>
		
	</div>
	<?php endif; /* EOF filter and search block */ ?>
	<?php
	if ($this->params->get('show_alpha', 1)) :
		echo $this->loadTemplate('alpha');
	endif;
	?>

	<?php if (count($this->items)) : ?>

	<!-- BOF items total-->
	<div id="item_total" class="item_total group">
	
		<?php if ($this->params->get('show_item_total', 1)) : ?>
			<span class="fc_item_total_data">
				<?php echo @$this->resultsCounter ? $this->resultsCounter : $this->pageNav->getResultsCounter(); // custom Results Counter ?>
			</span>
		<?php endif; ?>
		
		<?php if ($limit_selector) : ?>
			<span class="fc_limit_label hasTip" title="<?php echo JText::_('FLEXI_PAGINATION'); ?>::<?php echo JText::_('FLEXI_PAGINATION_INFO'); ?>">
				<span class="fc_limit_selector"><?php echo $limit_selector;?></span>
			</span>
		<?php endif; ?>
		
		<?php if ($orderby_selector) : ?>
			<span class="fc_orderby_label hasTip" title="<?php echo JText::_('FLEXI_ORDERBY'); ?>::<?php echo JText::_('FLEXI_ORDERBY_INFO'); ?>">
				<span class="fc_orderby_selector"><?php echo $orderby_selector;?></span>
			</span>
		<?php endif; ?>
	
	</div>
	<!-- BOF items total-->

	<?php endif; ?>