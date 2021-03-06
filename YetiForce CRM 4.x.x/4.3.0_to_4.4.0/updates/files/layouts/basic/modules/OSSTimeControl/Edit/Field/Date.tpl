{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if $FIELD_MODEL->getName() == 'date_start' && in_array($VIEW, ['Edit', 'QuickCreateAjax']) }
		{assign var=MODULE_MODEL value=$RECORD_STRUCTURE_MODEL->getModule()}
		{assign var=TIME_FIELD value=$MODULE_MODEL->getField('time_start')}
	{else if $FIELD_MODEL->getName() == 'due_date' && in_array($VIEW, ['Edit', 'QuickCreateAjax'])}
		{assign var=MODULE_MODEL value=$RECORD_STRUCTURE_MODEL->getModule()}
		{assign var=TIME_FIELD value=$MODULE_MODEL->getField('time_end')}
	{/if}
	{if $TIME_FIELD}
		<div class="tpl-Edit-Field-Date form-row">
			<div class="col-12 col-sm-6 col-md-6 col-lg-6 mb-3 mb-sm-0"">
				{include file=\App\Layout::getTemplatePath('Edit/Field/Date.tpl', 'Vtiger') BLOCK_FIELDS=$BLOCK_FIELDS FIELD_MODEL=$FIELD_MODEL}
			</div>
			<div class="col-12 col-sm-6 col-md-6 col-lg-6">
				{include file=\App\Layout::getTemplatePath('Edit/Field/Time.tpl', $MODULE) BLOCK_FIELDS=$BLOCK_FIELDS FIELD_MODEL=$TIME_FIELD SKIP=true}
			</div>
		</div>
		{assign var=BLOCK_FIELDS value=false}
	{else}
		{include file=\App\Layout::getTemplatePath('Edit/Field/Date.tpl', 'Vtiger') BLOCK_FIELDS=$BLOCK_FIELDS FIELD_MODEL=$FIELD_MODEL}
	{/if}
{/strip}
