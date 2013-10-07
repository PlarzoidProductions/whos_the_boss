<table align="center">
	<tr><td><input type="hidden" name="caster_id" value="<?=$casters[$chosen][id]?>"></td></tr>
	<tr>
		<td align="center"><h1><?=$casters[$chosen][name]?></h1></td>
	</tr>
	<tr>
			<td align="center"><img src="include/images/<?=$faction_logos[$casters[$chosen][faction]]?>"?></h1></td>
	</tr>
	<tr>
		<td align="center"><h3><?=$systems[$casters[$chosen][system]]?></h1></td>
	</tr>
	<tr><td><hr /></td></tr>
</table>
<table align="center">
	<tr>
		<td class="right"><?=$page->displayVar("keep")?></td>
		<td class="left"><?=$page->displayVar("new")?></td>
	</tr>
</table>
