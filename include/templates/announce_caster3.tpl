<table align="center">
	<tr><td><input type="hidden" name="caster_id" value="<?=$casters[$chosen][id]?>"></td></tr>
	<tr>
		<td align="center"><h1><?=$casters[$chosen][name]?></h1><td>
	</tr>
	<tr>
		<td align="center"><h3><?=$factions[$casters[$chosen][faction]]?></h3></td>
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
