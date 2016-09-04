<table align="center">
<tr><td>
<div class="content-wrapper">
	<div class="content-header"><div class="title-plaque"></div></div>
	<div class="content-body">
		<?php if(is_array($casters[$chosen])){?>
		<table align="center" width="500px">
		        <tr><td><input type="hidden" name="caster_id" value="<?=$casters[$chosen][id]?>"></td></tr>
		        <tr>
		                <td align="center"><h1><?=$casters[$chosen][name]?></h1><td>
		        </tr>
		        <tr>
		                <td align="center"><img src="include/images/<?=$faction_logos[$casters[$chosen][faction]]?>.png"/></td>
		        </tr>
		        <tr>
		                <td align="center"><h3><?=$systems[$casters[$chosen][system]]?></h3></td>
		        </tr>
		        <tr><td><hr /></td></tr>
		</table>
		<table align="center">
		        <tr>
		                <td class="right"><?=$page->displayVar("keep")?></td>
                		<td class="left"><?=$page->displayVar("new")?></td>
		        </tr>
		</table>
		<?php } else {?>
		<table align="center" width="500px">
			<tr><td align="center"><h1>No more 'casters!</h1></td></tr>
		</table>
		<?php }?>

	</div>
	<div class="content-footer"></div>
</div>
</tr></td>
</table>
