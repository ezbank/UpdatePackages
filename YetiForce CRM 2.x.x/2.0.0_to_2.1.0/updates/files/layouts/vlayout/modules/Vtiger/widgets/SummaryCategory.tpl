<div class="summaryWidgetContainer recordDetails">
	{if $WIDGET['label'] neq ' ' && $WIDGET['label'] neq ''}
		<div class="widget_header marginBottom10px">
			<span class="margin0px"><h4>{vtranslate($WIDGET['label'],$MODULE_NAME)}</h4></span>
		</div>
	{/if}
	{foreach item=SUMMARY_CATEGORY from=$SUMMARY_INFORMATION}
		<div class="row textAlignCenter roundedCorners">
			{foreach item=FIELD_VALUE from=$SUMMARY_CATEGORY}
				<span class="well squeezedWell col-md-3" data-reference="{$FIELD_VALUE.reference}">
					<div>
						<label class="font-x-small">
							{vtranslate($FIELD_VALUE.name,$MODULE_NAME)}
						</label>
					</div>
					<div>
						<label class="font-x-x-large">
							{if !empty($FIELD_VALUE.data)}{$FIELD_VALUE.data}{else}0{/if}
						</label>
					</div>
				</span>
			{/foreach}
		</div>
	{/foreach}
</div>